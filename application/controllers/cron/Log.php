<?php

class Log extends Cron_Controller
{

    public function delAll()
    {
        $this->delAppLog();
        $this->delSendmail();
        TelegramModel::log(__METHOD__);
    }

    public function error(string $chatId = "")
    {
        $date = date("Y-m-d", strtotime("-1 day"));
        $log_file_extension = config_item('log_file_extension');
        $log_path = config_item('log_path');
        $filepath = $log_path . 'error-' . $date . '.' . $log_file_extension;
        if (file_exists($filepath)) {
            $contentd = file_get_contents($filepath);
            $array  = array_filter(explode(PHP_EOL, $contentd));
            $total = count($array);
            if ($total > 0) {
                TelegramModel::log(__METHOD__ . ':' . $total, $chatId);
            }
        }
    }

    private function delAppLog()
    {
        $date = date("Y-m-d H:i:s", strtotime("-3 month"));
        $data_where[ApplogModel::$table . ".date <="] = $date;
        ApplogModel::$sqlWhere = $data_where;
        $total = ApplogModel::getTotal();
        if ($total > 0) {
            $this->db->from(ApplogModel::$table);
            foreach ($data_where as $field => $value) {
                $this->db->where($field, $value);
            }
            $this->db->delete();
        }
    }

    private function delSendmail()
    {
        $date = date("Y-m-d H:i:s", strtotime("-3 month"));
        $data_where[SendMailModel::$table . ".created_at <="] = $date;
        SendMailModel::$sqlWhere = $data_where;
        $total = SendMailModel::getTotal();
        if ($total > 0) {
            $this->db->from(SendMailModel::$table);
            foreach ($data_where as $field => $value) {
                $this->db->where($field, $value);
            }
            $this->db->delete();
        }
    }
}
