<?php

class ConfigModel
{
    use BasicModel;
    public static $configNow = [];
    public static $uploadFolder = 'config';
    public static $sessionKey = "config";

    public static function __constructStatic()
    {
        self::$table = 'config';
        self::$primaryKey = 'c_key';
    }

    public static function input(string $key = "", string $label = "", string $type = 'text', array $config = [])
    {
        if (empty($label)) {
            $label = $key;
        }
        if (empty($config)) {
            $config = self::$configNow;
        }

        $value = null;
        if (isset($config[$key])) {
            $value = $config[$key];
        }
        return '<div class="form-group"><label>' . $label . '</label><input type="' . $type . '" class="form-control" name="' . $key . '" value="' . $value . '"></div>';
    }

    public static function textarea(string $key = "", string $label = "", int $row = 8, array $config = [])
    {
        if (empty($label)) {
            $label = $key;
        }
        if (empty($config)) {
            $config = self::$configNow;
        }

        $value = null;
        if (isset($config[$key])) {
            $value = $config[$key];
        }
        return '<div class="form-group"><label>' . $label . '</label><textarea class="form-control" rows="' . $row . '" name="' . $key . '"  >' . $value . '</textarea></div>';
    }

    public static function getAll(): array
    {
        $limit = self::getTotal();
        $listdb = self::getList($limit);
        $result = array_column($listdb, 'c_value', self::$primaryKey);
        return $result;
    }

    public static function updateCache(): array
    {
        $jsonFile = self::$table . '.json';
        $listdb = self::getAll();
        $response = json_encode($listdb);
        update_cache_file($jsonFile, $response);
        return $listdb;
    }

    public static function fetchCache(): array
    {
        $jsonFile = self::$table . '.json';
        $response = get_cache_file($jsonFile);
        if (isJSON($response)) {
            return json_decode($response, true);
        }
        return self::updateCache();
    }

    public static function fetch(string $key = ""): string
    {
        if (empty($key)) {
            return '';
        }
        $config = self::fetchCache();
        return $config[$key] ?? '';
    }

    public static function key(string $key = ''): string
    {
        CIDb()->from(self::$table);
        CIDb()->where(self::$primaryKey, $key);
        $query = CIDb()->get();
        $rsdb = $query->row_array();
        return $rsdb["c_value"] ?? "";
    }

    public static function update(array $webdbs = []): bool
    {
        $result = false;
        if (is_array($webdbs)) {
            $insertResult = $ckIn = $data = [];
            foreach ($webdbs as $key => $value) {
                $ckIn[] = $key;
                $insertResult[self::$primaryKey] = $key;
                $insertResult['c_value'] = $value;
                $data[] = $insertResult;
            }

            if (!empty($ckIn)) {
                CIDb()->from(self::$table)->where_in(self::$primaryKey, $ckIn)->delete();
                if (!empty($data)) {
                    CIDb()->insert_batch(self::$table, $data);
                }
                CIDbUtility()->repair_table(self::$table);
                CIDbUtility()->optimize_table(self::$table);
                $result = true;
            }
        }
        self::updateCache();
        return $result;
    }

    public static function updateOne(string $value = '', string $key = 'webapp_smtp_user_refresh_token'): array
    {
        CIDb()->set('c_value', $value);
        CIDb()->where(self::$primaryKey, $key);
        CIDb()->update(self::$table);
        return self::updateCache();
    }
}
