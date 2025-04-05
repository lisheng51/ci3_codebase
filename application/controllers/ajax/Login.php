<?php

class Login extends Ajax_Controller
{
    public function sessionmaxlivetime()
    {
        if (LoginModel::userId() <= 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = login_url('?redirect_url=' . current_url());
            $json["msg"] = anchor($json["redirect_url"], 'Uw sessie is verlopen!');
            $json["status"] = "error";
            $json["timenow"] = "00:00:00";
            exit(json_encode($json));
        }

        //$val = $_SESSION['__ci_last_regenerate'];
        //TelegramModel::log(__METHOD__ . PHP_EOL . date('d-m-Y H:i:s', $val) . PHP_EOL);
        //$valnew = $val + time() - $val;
        $_SESSION['__ci_last_regenerate'] = time();
        //TelegramModel::log(__METHOD__ . PHP_EOL . date('d-m-Y H:i:s') . PHP_EOL);


        $json["timenow"] = date('H:i:s', config_item('sess_expiration') - 3600);
        $json["msg"] = "De sessie is verlengd";
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
