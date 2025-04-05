<?php

class Login extends API_Controller
{

    public function windows()
    {
        $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        $sess_array[UserModel::$primaryKey] = $dataUser[UserModel::$primaryKey];
        $sess_array['redirect_url'] = $dataUser["redirect_url"];
        LoginModel::$loginUserData = $sess_array;
        LoginModel::addSession();
        redirect(AccessCheckModel::redirectUrl());
    }

    public function index()
    {
        $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        $base_url = CIInput()->post('base_url');
        if (empty($base_url) === true) {
            ApiModel::outNOK(98, 'base_url is niet gevonden');
        }
        $sec = CIInput()->post('sec') ?? 10;
        $data[UserModel::$primaryKey] = $dataUser[UserModel::$primaryKey];
        $data["datetime"] = date('Y-m-d H:i:s', time() + $sec);
        $valuestring = json_encode($data);
        $encrypted_string = GlobalModel::encryptData($valuestring);
        $hashkey = rawurlencode($encrypted_string);
        $message = $base_url . ENVIRONMENT_ACCESS_URL . "/Login/directEasy?key=$hashkey";
        ApiModel::outOK($message);
    }

    public function password()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) === true ? CIInput()->post() : $this->arrPost;

        $username = $postData["username"] ?? null;
        if ($username === null || empty($username) === true) {
            ApiModel::out('Geen gebruikersnaam gevonden', 98);
        }

        $access_code = $postData["access_code"] ?? null;
        if ($access_code === null || empty($access_code) === true) {
            ApiModel::out('Geen code gevonden', 98);
        }

        $arr_rs = LoginModel::getOneByField('username', $username);

        if (empty($arr_rs) === true || empty($arr_rs["access_code"]) === true) {
            ApiModel::out('Er is geen gebruiker gevonden', 94);
        }

        if (date("Y-m-d H:i:s") > $arr_rs["access_code_date"]) {
            ApiModel::out('Code is verlopen', 95);
        }

        if ($access_code !== $arr_rs["access_code"]) {
            ApiModel::out('Code is niet juist', 97);
        }

        $password1 = $postData["password1"];
        $password2 = $postData["password2"];

        if (empty($password1) === true || empty($password2) === true) {
            ApiModel::out('Geen wachtwoord gevonden', 98);
        }

        if ($password1 !== $password2) {
            ApiModel::out('Wachtwoorden zijn niet gelijk', 98);
        }

        $uid = $arr_rs[UserModel::$primaryKey];
        $data_login["password"] = password_hash($password1, PASSWORD_DEFAULT);
        $data_login["password_date"] = date('Y-m-d H:i:s');
        $data_login["password_reset_date"] = null;
        LoginModel::edit($uid, $data_login);

        $json["msg"] = "Wachtwoord is bijgewerkt";
        if ($arr_rs["is_active"] == 0) {
            $data["is_active"] = 1;
            UserModel::edit($uid, $data);
            $json["msg"] = "Uw account is nu geactiveerd";
        }

        $json["status"] = "good";
        ApiModel::out($json, 100, $apiLogId);
    }


    public function register()
    {

        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) === true ? CIInput()->post() : $this->arrPost;

        $username = $postData["username"] ?? null;
        if ($username === null || empty($username) === true) {
            ApiModel::out('Geen gebruikersnaam gevonden', 98);
        }

        $arr_user = LoginModel::getOneByField('username', $username);
        if (empty($arr_user) === false && $arr_user["is_del"] == 1) {
            UserModel::edit($arr_user[UserModel::$primaryKey], ["is_del" => 0]);
        }

        if (empty($arr_user) === false && $arr_user["is_active"] > 0) {
            ApiModel::out('Deze gebruikersnaam bestaat al', 98);
        }

        $access_code = $postData["access_code"] ?? null;
        if (empty($access_code) === true) {
            ApiModel::outNOK(99, 'Code is niet gevonden');
        }

        $code = $postData["access_code_cookie"] ?? null;
        if (empty($code) === true) {
            ApiModel::outNOK(99, 'Code is niet gevonden');
        }
        $key = rawurldecode($code);
        $arrTokenValue = json_decode(GlobalModel::decryptData($key), true);
        if ($arrTokenValue['access_code'] != $access_code) {
            ApiModel::outNOK(97, "Code kan niet verifiëren!");
        }

        $token_expires = $arrTokenValue['datetime'] ?? null;
        if (date("Y-m-d H:i:s") > $token_expires) {
            ApiModel::outNOK(95, "Code is verlopen!");
        }

        $password1 = $postData["password1"];
        $password2 = $postData["password2"];

        if (empty($password1) === true || empty($password2) === true) {
            ApiModel::out('Geen wachtwoord gevonden', 98);
        }

        if ($password1 !== $password2) {
            ApiModel::out('Wachtwoorden zijn niet gelijk', 98);
        }

        $data["emailaddress"] = strtolower($username);
        $data["permission_group_ids"] = 2;

        $uid = UserModel::add($data);
        if ($uid > 0) {
            $data_login["user_id"] = $uid;
            $data_login["username"] = $data["emailaddress"];
            $data_login["redirect_url"] = '';
            $data_login["password"] = password_hash($password1, PASSWORD_DEFAULT);
            $data_login["password_date"] = date('Y-m-d H:i:s');
            $data_login["password_reset_date"] = null;
            LoginModel::add($data_login);
            $data_newpic["createdby"] = $uid;
            UserModel::edit($uid, $data_newpic);
            UploadTypeModel::makeDir($uid);
            $json["msg"] = "Account is aangemaakt";
            $json["status"] = "good";
            $json["id"] = $uid;
            ApiModel::out($json, 100, $apiLogId);
        }

        ApiModel::out('Account is niet aangemaakt', 93);
    }
}
