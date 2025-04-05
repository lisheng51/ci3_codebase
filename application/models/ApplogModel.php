<?php

class ApplogModel
{
    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "app_log";
        self::$primaryKey = "log_id";
        self::$selectOrderBy = [
            'log_id#desc' => 'ID (aflopend)',
            'date#desc' => 'Datum (aflopend)',
            'date#asc' => 'Datum (oplopend)',
            'path#desc' => 'Path (aflopend)',
            'path#asc' => 'Path (oplopend)',
        ];
    }

    public static function insert(string $description = "", int $uid = 0): int
    {
        if (empty($description)) {
            return 0;
        }
        $data["description"] = $description;
        if ($uid > 0) {
            $data["user_id"] = $uid;
        } else {
            $data["user_id"] = LoginModel::userId();
        }
        $data["path"] = uri_string();
        return self::add($data);
    }
}
