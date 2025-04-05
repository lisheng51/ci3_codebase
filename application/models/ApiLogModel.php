<?php

class ApiLogModel
{
    use BasicModel;

    public static function __constructStatic()
    {
        self::$table = "api_log";
        self::$primaryKey = "log_id";
        self::$selectOrderBy = [
            'log_id#desc' => 'ID (aflopend)',
            'datetime#desc' => 'Datum (aflopend)',
            'datetime#asc' => 'Datum (oplopend)',
        ];
    }

    public static function joinApi()
    {
        self::$sqlSelect =
            [
                [ApiModel::$table => 'name'],
                [ApiModel::$table => 'permission_group_ids']
            ];

        self::$sqlJoin =
            [
                [ApiModel::$table => ApiModel::$primaryKey]
            ];
    }

    public static function insert(string $msg = "", int $api_id = 0): int
    {
        if (ENVIRONMENT !== 'development' && $api_id > 0) {
            $data['api_id'] = $api_id;
            $data['msg'] = $msg;
            $data["ip_address"] = CIInput()->ip_address();
            $data["browser"] = CIAgent()->browser();
            $data["platform"] = CIAgent()->platform();
            $data["path"] = uri_string();
            $data['post_value'] = json_encode($_POST);
            $data['get_value'] = json_encode($_GET);
            $data['header_value'] = json_encode(apache_request_headers());
            $insertId = self::add($data);
            return $insertId;
        }

        return 0;
    }

    public static function updateOut(int $id = 0, $out = null): bool
    {
        if ($id > 0) {
            $data['out_value'] = json_encode($out);
            return self::edit($id, $data);
        }

        return false;
    }
}
