<?php

class LoginHistoryModel
{

    use BasicModel;

    public static function __constructStatic()
    {
        self::$table = "login_history";
        self::$primaryKey = "login_history_id";
        self::$selectOrderBy = [
            'login_history_id#desc' => 'ID (aflopend)',
            'created_at#desc' => 'Datum (aflopend)',
            'created_at#asc' => 'Datum (oplopend)',
        ];
    }

    public static function insert(int $userId = 0): int
    {
        $data["ip_address"] = CIInput()->ip_address();
        $data["browser"] = CIAgent()->browser() . ' ' . CIAgent()->version();
        $data["platform"] = CIAgent()->platform();
        $data[UserModel::$primaryKey] = $userId;
        $insertId = self::add($data);
        return $insertId;
    }

    public static function last(int $setUserId = 0): array
    {
        $userId = LoginModel::userId();
        if ($setUserId > 0) {
            $userId = $setUserId;
        }
        $data[UserModel::$primaryKey] = $userId;
        self::$sqlWhere  = $data;;
        $listdb = self::getList(2);
        $last = end($listdb);
        return $last;
    }
}
