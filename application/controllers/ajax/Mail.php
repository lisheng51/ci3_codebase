<?php

class Mail extends Ajax_Controller
{

    public function view()
    {
        $id = CIInput()->post("mail_id");
        $type = CIInput()->post("type");

        $arr_db = [];
        if ($type === 'sys') {
            $arr_db = SendMailModel::getOneById(intval($id));
        }

        if (!empty($arr_db)) {
            $json["msg"] = preg_replace("/<img[^>]+\>/i", "", $arr_db["message"]);
            $json["status"] = "info";
            exit(json_encode($json));
        }

        $json["msg"] = "Het mail is niet gevonden!";
        $json["status"] = "error";
        exit(json_encode($json));
    }

    public function send()
    {
        $id = CIInput()->post("mail_id");
        $type = CIInput()->post("type");
        $status = false;
        //SendMailModel::$alwaysSend = true;
        switch ($type) {
            case "sys":
                $arr_mail = SendMailModel::getOneById(intval($id));
                $sendstatus = SendMailModel::sendAction($arr_mail);
                if ($sendstatus) {
                    $data["is_send"] = 1;
                    $data["send_date"] = date('Y-m-d H:i:s');
                    SendMailModel::edit($arr_mail[SendMailModel::$primaryKey], $data);
                    $status = true;
                }
                break;
            default:
                break;
        }

        if ($status) {
            $json["msg"] = "Een mail is naar dit adres gestuurd!";
            $json["status"] = "good";
            exit(json_encode($json));
        }

        $json["msg"] = "Het mail is niet verzonden!";
        $json["status"] = "error";
        exit(json_encode($json));
    }

    public function preview()
    {
        $mail_content_js = CIInput()->post("mail_content_js");
        $mail_title_js = CIInput()->post("mail_title_js");
        $mail_to_address = CIInput()->post("mail_to_address") ?? c_key('webapp_master_email_address');
        if (empty($mail_content_js)) {
            $json["msg"] = "Het voorbeeld email bestaat niet!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        AjaxckModel::value('email', $mail_to_address);

        $status = SendMailModel::preview($mail_content_js, $mail_title_js, $mail_to_address);
        if ($status) {
            $json["msg"] = "Het voorbeeld email is naar uw mailbox verzonden!";
            $json["status"] = "good";
            exit(json_encode($json));
        }

        $json["msg"] = "Het voorbeeld email verzending is mislukt";
        $json["status"] = "error";
        exit(json_encode($json));
    }
}
