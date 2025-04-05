<?php

class HistoryUrlModel
{
    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "history_url";
        self::$primaryKey = "url_id";
        self::$selectOrderBy = [
            'url_id#desc' => 'ID (aflopend)',
            'date#desc' => 'Datum (aflopend)',
            'date#asc' => 'Datum (oplopend)',
            'path#desc' => 'Path (aflopend)',
            'path#asc' => 'Path (oplopend)',
        ];
    }

    public static function update(string $title = "", int $uid = 0)
    {
        if (empty($title) === true) {
            return;
        }
        $data["title"] = $title;
        if ($uid > 0) {
            $data["user_id"] = $uid;
        } else {
            $data["user_id"] = LoginModel::userId();
        }
        $data["path"] = uri_string();
        $datack["date >="] = date('Y-m-d 00:00:00');
        $datack["date <="] = date('Y-m-d 23:59:59');
        self::$sqlWhere = array_merge($data, $datack);
        $exist = self::getOne();
        if (empty($exist)) {
            return self::add($data);
        }
        return self::edit($exist[self::$primaryKey],  ["date" => date("Y-m-d H:i:s")]);
    }

    public static function getLast(int $limit = 10)
    {
        $data_where["user_id"] = LoginModel::userId();
        $data_where["path !="] = uri_string();
        self::$sqlWhere = $data_where;
        return self::getList($limit);
    }
}
