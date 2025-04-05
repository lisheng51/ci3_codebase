<?php

class Message extends API_Controller
{

    public function index()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        $start = CIInput()->get("start");
        $end = CIInput()->get("end");
        if (empty($start) === false) {
            $data_where["date >="] = date_format(date_create(trim($start)), 'Y-m-d H:i:s');
        }

        if (empty($end) === false) {
            $data_where["date <="] = date_format(date_create(trim($end)), 'Y-m-d 23:59:59');
        }

        $isopen = CIInput()->get("is_open");
        $data_where["to_user_id"] = $dataUser[UserModel::$primaryKey];
        $data_where["is_del"] = 0;
        if (empty($isopen) === false) {
            if ($isopen === 'yes') {
                $data_where["is_open"] = 1;
            }

            if ($isopen === 'no') {
                $data_where["is_open"] = 0;
            }
        }

        MessageModel::$sqlWhere = $data_where;
        $listdb = MessageModel::getAll();
        $arr_result = [];
        if (empty($listdb) === false) {
            foreach ($listdb as $rs) {
                $rs["from"] = UserModel::display($rs["from_user_id"]);
                unset($rs['from_user_id'], $rs['to_user_id'], $rs['is_del']);
                $arr_result[] = $rs;
            }
        }
        ApiModel::outOK($arr_result, $apiLogId);
    }

    public function open()
    {
        $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        $messageId = CIInput()->post("message_id") ?? 0;
        $rsdb = MessageModel::getOneById(intval($messageId));
        if (empty($rsdb) === true || $messageId <= 0) {
            ApiModel::outNOK();
        }

        if ($dataUser[UserModel::$primaryKey] == $rsdb["to_user_id"]) {
            MessageModel::edit($messageId, array("is_open" => 1));
            ApiModel::outOK();
        }

        ApiModel::outNOK();
    }

    public function del()
    {
        $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        $messageId = CIInput()->post("message_id") ?? 0;
        $rsdb = MessageModel::getOneById(intval($messageId));
        if (empty($rsdb) || $messageId <= 0) {
            ApiModel::outNOK();
        }

        if ($dataUser[UserModel::$primaryKey] == $rsdb["to_user_id"]) {
            MessageModel::edit($messageId, array("is_del" => 1));
            ApiModel::outOK();
        }

        ApiModel::outNOK();
    }
}
