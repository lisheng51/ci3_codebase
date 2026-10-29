<?php

/**
 * Brings existing tables in line with the CREATE TABLE in the generated sql/*.sql files.
 * Missing columns and indexes are added and changed columns are modified.
 * An ENGINE change (rebuilds the table) and dropping columns or indexes that only exist in the database (loses data)
 * are returned in "confirm" and only executed when the sha1 of that exact statement is confirmed.
 */
class SchemaModel
{
    public static function syncTable(string $file = "", bool $execute = true, array $confirmed = []): array
    {
        $result = ["sql" => [], "confirm" => [], "warning" => []];
        $target = self::parseCreateTable(self::createTableFromFile($file));
        if (empty($target["table"]) || CIDb()->query('SHOW TABLES LIKE ' . CIDb()->escape($target["table"]))->num_rows() === 0) {
            return $result;
        }

        $table = $target["table"];
        $row = CIDb()->query('SHOW CREATE TABLE `' . $table . '`')->row_array();
        $current = self::parseCreateTable($row["Create Table"] ?? "");

        $engine = [];
        if (!empty($target["engine"]) && strcasecmp($target["engine"], $current["engine"]) !== 0) {
            $engine[] = 'ALTER TABLE `' . $table . '` ENGINE=' . $target["engine"];
        }

        $previous = null;
        foreach ($target["columns"] as $name => $definition) {
            $position = $previous === null ? ' FIRST' : ' AFTER `' . $previous . '`';
            if (!isset($current["columns"][$name])) {
                $result["sql"][] = 'ALTER TABLE `' . $table . '` ADD COLUMN ' . $definition . $position;
            } elseif (self::normalize($current["columns"][$name]) !== self::normalize($definition)) {
                $result["sql"][] = 'ALTER TABLE `' . $table . '` MODIFY COLUMN ' . $definition;
            }
            $previous = $name;
        }

        foreach ($target["keys"] as $name => $definition) {
            if (!isset($current["keys"][$name])) {
                $result["sql"][] = 'ALTER TABLE `' . $table . '` ADD ' . $definition;
            } elseif (self::normalize($current["keys"][$name]) !== self::normalize($definition)) {
                if ($name === 'PRIMARY') {
                    $result["warning"][] = "$table: primary key is anders, handmatig aanpassen";
                    continue;
                }
                $result["sql"][] = 'ALTER TABLE `' . $table . '` DROP INDEX `' . $name . '`, ADD ' . $definition;
            }
        }

        // Indexes first, dropping a column already removes it from its indexes
        $drop = [];
        foreach (array_diff_key($current["keys"], $target["keys"]) as $name => $definition) {
            $drop[] = $name === 'PRIMARY' ? 'ALTER TABLE `' . $table . '` DROP PRIMARY KEY' : 'ALTER TABLE `' . $table . '` DROP INDEX `' . $name . '`';
        }
        foreach (array_diff_key($current["columns"], $target["columns"]) as $name => $definition) {
            $drop[] = 'ALTER TABLE `' . $table . '` DROP COLUMN `' . $name . '`';
        }

        $result["confirm"] = array_merge($engine, $drop);
        if ($execute === true) {
            $isConfirmed = fn($sql) => in_array(sha1($sql), $confirmed, true);
            // The engine goes first so the other changes are made on the converted table
            $run = array_merge(array_filter($engine, $isConfirmed), $result["sql"], array_filter($drop, $isConfirmed));
            foreach ($run as $sql) {
                if (CIDb()->query($sql) === false) {
                    $result["warning"][] = "$table: mislukt: $sql";
                    break;
                }
            }
            $result["sql"] = array_values($run);
            $result["confirm"] = array_values(array_filter($result["confirm"], fn($sql) => !$isConfirmed($sql)));
        }
        return $result;
    }

    private static function createTableFromFile(string $file = ""): string
    {
        if (!is_file($file)) {
            return "";
        }
        $content = file_get_contents($file);
        if (preg_match('/CREATE TABLE.*?\n\)[^;]*;/s', $content, $match)) {
            return $match[0];
        }
        return "";
    }

    private static function parseCreateTable(string $sql = ""): array
    {
        $data = ["table" => "", "engine" => "", "columns" => [], "keys" => []];
        if (preg_match('/CREATE TABLE `?([^`\s(]+)`?/', $sql, $match)) {
            $data["table"] = $match[1];
        }
        if (preg_match('/\n\)[^\n]*\bENGINE=(\w+)/', $sql, $match)) {
            $data["engine"] = $match[1];
        }

        foreach (preg_split('/\r?\n/', $sql) as $line) {
            $line = rtrim(trim($line), ',');
            if (preg_match('/^`([^`]+)`/', $line, $match)) {
                $data["columns"][$match[1]] = $line;
            } elseif (strpos($line, 'PRIMARY KEY') === 0) {
                $data["keys"]["PRIMARY"] = $line;
            } elseif (preg_match('/^(?:UNIQUE |FULLTEXT |SPATIAL )?KEY `([^`]+)`/', $line, $match)) {
                $data["keys"][$match[1]] = $line;
            }
        }
        return $data;
    }

    /**
     * MySQL and MariaDB versions print the same definition differently, compare only what matters
     */
    private static function normalize(string $definition = ""): string
    {
        $definition = strtolower(preg_replace('/\s+/', ' ', trim($definition)));
        $definition = preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', $definition);
        $definition = preg_replace('/ (character set|collate) \w+/', '', $definition);
        $definition = preg_replace("/ default '(-?\d+(\.\d+)?)'/", ' default $1', $definition);
        $definition = preg_replace('/current_timestamp\(\)/', 'current_timestamp', $definition);
        $definition = preg_replace('/ using btree/', '', $definition);
        // MySQL leaves out DEFAULT NULL on nullable text columns, MariaDB prints it
        $definition = preg_replace('/ default null/', '', $definition);
        // MariaDB stores a JSON column as longtext with this check, MySQL does not
        $definition = preg_replace('/ check \(json_valid\(`[^`]+`\)\)/', '', $definition);
        return $definition;
    }
}
