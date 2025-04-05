<?php

class SpamwordModel
{
    use BasicModel;

    public static function __constructStatic()
    {
        self::$table = "spam_word";
        self::$primaryKey = "word_id";
        self::$usingSoftDel = true;
        self::$selectOrderBy = [
            'word_id#desc' => 'ID (aflopend)',
            'word#desc' => 'Woord (aflopend)',
            'word#asc' => 'Woord (oplopend)',
        ];
    }

    private static function makeString(): string
    {
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = $where;
        $arr_result = self::getAll();
        if (!empty($arr_result)) {
            $new_array = [];
            foreach ($arr_result as $value) {
                $new_array[] = $value["word"];
            }
            return implode("|", $new_array);
        }
        return '';
    }

    public static function check(array $data = [], int $limit = 1): bool
    {
        if (empty($data)) {
            return true;
        }
        $total_find = 0;
        $matches = [];
        $string = self::makeString();
        if (empty($string)) {
            return true;
        }
        foreach ($data as $value) {
            preg_match_all('/' . $string . '/i', trim($value ?? ""), $matches);
            $total_find += count($matches[0]);
        }
        if ($total_find >= $limit) {
            return false;
        }
        return true;
    }

    public static function checkOne(string $content = "", int $limit = 1): bool
    {
        if (empty($content)) {
            return true;
        }
        $matches = [];
        $string = self::makeString();
        if (empty($string)) {
            return true;
        }
        preg_match_all('/' . $string . '/i', $content, $matches);
        if (count($matches[0]) >= $limit) {
            return false;
        }
        return true;
    }

    public static function getPostdata(): array
    {
        $data["word"] = CIInput()->post("word");
        AjaxckModel::value('word', $data["word"]);
        return $data;
    }
}
