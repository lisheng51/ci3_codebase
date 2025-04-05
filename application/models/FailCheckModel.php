<?php

class FailCheckModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "fail_check";
        self::$primaryKey = "fail_id";
    }

    private static function getExistByTypeId(int $type_id = 0)
    {
        CIDb()->from(self::$table)
            ->where("ip_address", CIInput()->ip_address())
            ->where("platform", CIAgent()->platform())
            ->where("date", date('Y-m-d'))
            ->where("type_id", $type_id)
            ->where("browser", CIAgent()->browser());
        CIDb()->limit(1);
        $query = CIDb()->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return [];
    }

    public static function getTryNum(int $type_id = 0): int
    {
        $arr_data = self::getExistByTypeId($type_id);
        if (!empty($arr_data)) {
            return (int) $arr_data["num"];
        }
        return 0;
    }

    private static function get(string $key = ''): string
    {
        $value = CISession()->tempdata($key);
        if (empty($value) === true) {
            return "";
        }
        return $value;
    }

    public static function check(int $type_id = 0): bool
    {
        $arr_type = FailCheckTypeModel::fetchField($type_id);
        if (empty($arr_type)) {
            return true;
        }
        $check_ip = self::get($arr_type["key"]);
        if (empty($check_ip)) {
            return true;
        }
        if (CIInput()->ip_address() == $check_ip) {
            return false;
        }

        return true;
    }

    public static function update(int $type_id = 0)
    {
        $arr_type = FailCheckTypeModel::fetchField($type_id);
        if (empty($arr_type)) {
            return;
        }

        $arr_data = self::getExistByTypeId($type_id);
        if (empty($arr_data) && $type_id > 0) {
            $dataAdd["ip_address"] = CIInput()->ip_address();
            $dataAdd["browser"] = CIAgent()->browser();
            $dataAdd["platform"] = CIAgent()->platform();
            $dataAdd["date"] = date('Y-m-d');
            $dataAdd["type_id"] = $type_id;
            return self::add($dataAdd);
        }

        $webapp_fail_limit_num = c_key("webapp_fail_limit_num") > 0 ? c_key("webapp_fail_limit_num") : 5;
        $mod = $arr_data["num"] % $webapp_fail_limit_num;
        if ($mod === 0) {
            CISession()->set_tempdata($arr_type["key"], $arr_data["ip_address"], $arr_type["lock_time"]);
        } else {
            $data["num"] = $arr_data["num"] + 1;
            self::edit($arr_data[self::$primaryKey], $data);
        }
    }

    public static function reset(int $type_id = 0)
    {
        $arr_type = FailCheckTypeModel::fetchField($type_id);
        if (empty($arr_type)) {
            return;
        }

        $arr_data = self::getExistByTypeId($type_id);
        if (empty($arr_data)) {
            return;
        }

        $data["num"] = 0;
        self::edit($arr_data[self::$primaryKey], $data);
    }
}
