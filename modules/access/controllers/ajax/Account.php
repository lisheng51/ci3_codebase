<?php

class Account extends Ajax_Controller
{

    public function login(int $sec = 3)
    {
        if (!UserModel::isSuperUser()) {
            $json["msg"] = "geen link gevonden";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $domain = $this->input->post('domain');
        $userId = $this->input->post('userId');
        $username = $this->input->post('username');

        $val = explode(DIRECTORY_SEPARATOR, dirname(__FILE__), -5);
        $base_url = 'http://' . $domain . '/' . end($val) . '/';
        if (ENVIRONMENT !== 'development') {
            $base_url = 'https://' . $domain . '/';
        }

        $data[UserModel::$primaryKey] = $userId;
        $data["datetime"] = date('Y-m-d H:i:s', time() + $sec);
        $valuestring = json_encode($data);

        $encrypted_string = $this->encryption->encrypt($valuestring);
        $hashkey = rawurlencode($encrypted_string);

        $json["type_done"] = "redirect";
        $module = $this->router->module;
        $json["redirect_url"] = $base_url . $module . "/Login/directEasy?key=$hashkey";
        $json["msg"] = $username . ' is ingelogd!';
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
