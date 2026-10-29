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

    public static function maxExpired(int $api_id = 0, int $sec = 10, int $amount = 1): bool
    {
        $startCheck = new DateTime();
        $now = clone $startCheck;
        $startCheck->modify("-{$sec} seconds");
        $data_where[] = [self::$table . ".api_id" => $api_id];
        $data_where[] = [self::$table . ".datetime >" => $startCheck->format('Y-m-d H:i:s')];
        $data_where[] = [self::$table . ".datetime <" => $now->format('Y-m-d H:i:s')];
        self::$sqlWhere = setSqlWhere($data_where);
        $count =  self::getTotal();
        return $count >= $amount;
    }

    public static function insert(string $msg = "", int $api_id = 0): int
    {
        if ($api_id > 0) {
            $webhook = fopen('php://input', 'rb');
            $string = "";
            while (!feof($webhook)) {
                $string .= fread($webhook, 4096);
            }
            $post_value = empty($_POST) ? $string : json_encode($_POST);
            $data['api_id'] = $api_id;
            $data['msg'] = $msg;
            $data["ip_address"] = CIInput()->ip_address();
            $data["browser"] = CIAgent()->browser();
            $data["platform"] = CIAgent()->platform();
            $data["path"] = uri_string();
            $data['post_value'] = $post_value;
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
