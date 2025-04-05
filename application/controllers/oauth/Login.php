<?php

class Login extends MX_Controller
{

    public function index()
    {
        $hasGetToken = CIInput()->get('token') ?? null;
        if ($hasGetToken !== null) {
            exit(rawurlencode($hasGetToken));
        }

        $code = CIInput()->get('code') ?? null;
        AjaxckModel::value('code', $code);
        $apiId = GlobalModel::decryptData(rawurldecode($code));

        if (empty($apiId)) {
            $json["msg"] = "Code is niet juist";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (LoginModel::userId() > 0) {
            $this->load->database();
            $arr_user = UserModel::getOneById(LoginModel::userId());
            $data["expires"] = ApiModel::fetchTokenMin($apiId);
            $data[UserModel::$primaryKey] = LoginModel::userId();
            $data[ApiModel::$primaryKey] = $apiId;
            $data["ip_address"] = $arr_user["ip_address"];
            $data["browser"] = $arr_user["browser"];
            $data["platform"] = $arr_user["platform"];
            $data["password_date"] = $arr_user['password_date'];

            $valuestring = json_encode($data);
            $tokenString = GlobalModel::encryptData($valuestring);
            $token = rawurlencode($tokenString);
            $getRedirectUrl = CIInput()->get('redirectUrl') ?? current_url();
            $redirect_url = $getRedirectUrl . '?token=' . $token;
            redirect($redirect_url);
        }

        LanguageModel::setLanguage();
        $lang = LanguageModel::getLanguage();
        ModuleModel::language($lang);
        if (CIInput()->post()) {
            $this->indexAction($apiId);
        }

        $data["title"] = "Oauth login";
        $this->load->view("oauth/index", $data);
    }

    private function indexAction(int $apiId = 0)
    {
        $username = CIInput()->post('username') ?? "";
        $password = CIInput()->post('password') ?? "";

        $json = [];
        AjaxckModel::value('Gebruikersnaam', $username);
        AjaxckModel::value('Wachtwoord', $password);

        $this->load->database();
        $arr_user = LoginModel::find($username, $password);
        if (empty($arr_user) === true) {
            $json["msg"] = "Verkeerde gebruikersnaam of wachtwoord!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $onecode = CIInput()->post('webapp_one_code') ?? "";
        if (LoginModel::check2FA($arr_user, $onecode) === false) {
            $json["msg"] = "2FA code is niet juist!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (LoginModel::find($username, $password)) {
            //oginModel::update_data($arr_user['user_id']);
            $data["expires"] = ApiModel::fetchTokenMin($apiId);
            $data[UserModel::$primaryKey] = $arr_user[UserModel::$primaryKey];
            $data[ApiModel::$primaryKey] = $apiId;
            $data["ip_address"] = CIInput()->ip_address();
            $data["browser"] = $this->agent->browser();
            $data["platform"] = $this->agent->platform();
            $data["password_date"] = $arr_user['password_date'];

            $valuestring = json_encode($data);
            $tokenString = GlobalModel::encryptData($valuestring);
            $token = rawurlencode($tokenString);
            $getRedirectUrl = CIInput()->get('redirectUrl') ?? current_url();
            $redirectUrl = $getRedirectUrl . '?token=' . $token;
            $json["msg"] = "Even geduld aub... U wordt automatisch doorgeschakeld";
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = $redirectUrl;
            exit(json_encode($json));
        }

        $json["msg"] = "Verkeerde gebruikersnaam of wachtwoord!";
        $json["status"] = "error";
        exit(json_encode($json));
    }
}
