<?php

class Mail extends Site_Controller
{

    public function index()
    {
        redirect(site_url());
    }

    public function sys()
    {
        $hash = rawurldecode(CIInput()->get('hashkey') ?? '');
        $decrypt =  GlobalModel::decryptData($hash);
        if (empty($decrypt)) {
            exit();
        }
        $arr_keys = explode("#_#", $decrypt);
        $data["event_value"] = $arr_keys[0];
        $data["to_email"] = $arr_keys[1];
        SendMailModel::$sqlWhere = $data;
        $arr_db = SendMailModel::getOne();

        if (empty($arr_db) === true) {
            exit();
        }

        exit($arr_db["message"]);
    }

    public function sys_open()
    {
        $hash = rawurldecode(CIInput()->get('hashkey') ?? '');
        $decrypt =  GlobalModel::decryptData($hash);
        if (empty($decrypt)) {
            exit();
        }
        $arr_keys = explode("#_#", $decrypt);
        $data["event_value"] = $arr_keys[0];
        $data["to_email"] = $arr_keys[1];
        $data["is_open"] = 0;
        SendMailModel::$sqlWhere = $data;
        $arr_db = SendMailModel::getOne();
        if (empty($arr_db) === false) {
            $edit["is_open"] = 1;
            $edit["open_date"] = date('Y-m-d H:i:s');
            SendMailModel::edit($arr_db[SendMailModel::$primaryKey], $edit);
        }

        header("Content-Type: image/png");
        $im = @imagecreate(110, 20);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, 5, 5, "", $text_color);
        imagepng($im);
        imagedestroy($im);
    }
}
