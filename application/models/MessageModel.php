<?php

class MessageModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        
        self::$table = "message";
        self::$primaryKey = "message_id";
        self::$selectOrderBy = [
            'message_id#desc' => 'ID (aflopend)',
            'title#desc' => 'Onderwerp (aflopend)',
            'title#asc' => 'Onderwerp (oplopend)',
            'date#desc' => 'Datum (aflopend)',
            'date#asc' => 'Datum (oplopend)',
        ];
    }


    public static function getPostdata(): array
    {
        $data["content"] = CIInput()->post("content");
        $data["title"] = CIInput()->post("title");
        $data["to_user_id"] = CIInput()->post("to_user_id");
        $data["from_user_id"] = LoginModel::userId();
        if ($data["from_user_id"] == $data["to_user_id"]) {
            $json["msg"] = "Het heeft geen zin om naar uwzelf te sturen!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        AjaxckModel::value('Content', $data["content"]);
        AjaxckModel::spamword($data);
        return $data;
    }

    public static function selectOpenStatus(string $is_open = "")
    {
        $arr_type = ["no" => "Ongelezen", "yes" => "Gelezen"];
        return select("is_open", $arr_type, $is_open, true);
    }

    public static function getNoOpenMessage(int $uid = 0, int $limit = 10)
    {
        if ($uid > 0) {
            $data_where["to_user_id"] = $uid;
        } else {
            $data_where["to_user_id"] = LoginModel::userId();
        }
        $data_where["is_open"] = 0;
        self::$sqlWhere = $data_where;
        $arr_result = [];
        $total = self::getTotal();
        $listdb = self::getList($limit);
        foreach ($listdb as $rs) {
            $rs["from_user_name"] = UserModel::display($rs["from_user_id"]);
            $rs["date"] = F_datetime::convert_datetime($rs["date"]);
            $arr_result[] = $rs;
        }

        $result["listdb"] = $arr_result;
        $result["total"] = $total;
        return $result;
    }
}
