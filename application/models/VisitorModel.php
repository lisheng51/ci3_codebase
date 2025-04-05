<?php

class VisitorModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "visitor";
        self::$primaryKey = "visitor_id";
        self::$selectOrderBy = [
            'visitor_id#desc' => 'ID (aflopend)',
            'datetime#desc' => 'Datum (aflopend)',
            'datetime#asc' => 'Datum (oplopend)',
            'path#desc' => 'Path (aflopend)',
            'path#asc' => 'Path (oplopend)',
        ];
    }

    public static function insert(): int
    {
        if (ENVIRONMENT_VISITOR_LOG) {
            $data["ip_address"] = CIInput()->ip_address();
            $data["browser"] = CIAgent()->browser() . ' ' . CIAgent()->version();
            $data["platform"] = CIAgent()->platform();
            $data["path"] = uri_string();
            $data["user_id"] = LoginModel::userId();
            $insertId = self::add($data);
            return $insertId;
        }

        return 0;
    }
}
