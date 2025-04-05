<?php

class Code extends API_Controller
{

    public function access()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $tokenString = GlobalModel::encryptData($this->apiId);
        $token = rawurlencode($tokenString);
        ApiModel::outOK($token, $apiLogId);
    }

    public function index()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) ? CIInput()->post() : $this->arrPost;
        $emailaddress = $postData["username"];
        if (is_null($emailaddress) || empty($emailaddress)) {
            ApiModel::out('Geen email gevonden', 98);
        }

        $data["access_code"] = F_string::random(6);
        $data["datetime"] = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $dataMessage["content"] = 'Beste gebruiker, uw toegangscode is ' . $data["access_code"] . ', het is geldig tot ' . $data["datetime"];
        $dataMessage["title"] = 'Uw toegangscode';
        SendMailModel::addSend($dataMessage, $emailaddress);

        $valuestring = json_encode($data);
        $encrypted_string = GlobalModel::encryptData($valuestring);
        $hashkey = rawurlencode($encrypted_string);

        $json["hashkey"] = $hashkey;
        $json["msg"] = "Er is een mail gestuurd";
        $json["status"] = "good";
        ApiModel::out($json, 100, $apiLogId);
    }

    public function access_datetime()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) ? CIInput()->post() : $this->arrPost;
        $sec = $postData["sec"] ?? 60;
        $json[ApiModel::$primaryKey] = $this->apiId;
        $json["datetime"] = date('Y-m-d H:i:s', time() + $sec);
        $valuestring = json_encode($json);
        $encrypted_string = GlobalModel::encryptData($valuestring);
        $hashkey = rawurlencode($encrypted_string);
        ApiModel::outOK($hashkey, $apiLogId);
    }

    public function accessCode()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) ? CIInput()->post() : $this->arrPost;

        $username = $postData["username"];
        if (is_null($username) || empty($username)) {
            ApiModel::out('Geen gebruikersnaam gevonden', 98);
        }

        $user_db = LoginModel::getOneByField('username', $username);
        if (empty($user_db) || $user_db["is_del"] == 1) {
            ApiModel::out('Er is geen gebruiker gevonden', 94);
        }

        $sendStatus = SendmailModel::accessCodeLogin($user_db);
        if (!$sendStatus) {
            $json["msg"] = "Even druk, mail wordt wat later verzonden";
            $json["status"] = "good";
            ApiModel::out($json, 100, $apiLogId);
        }
        $json["msg"] = "Er is een mail gestuurd";
        $json["status"] = "good";
        ApiModel::out($json, 100, $apiLogId);
    }


    public function checkAccessCode()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) ? CIInput()->post() : $this->arrPost;

        $username = $postData["username"];
        if (is_null($username) || empty($username)) {
            ApiModel::out('Geen gebruikersnaam gevonden', 98);
        }

        $access_code = $postData["access_code"];
        if (is_null($access_code) || empty($access_code)) {
            ApiModel::out('Geen code gevonden', 98);
        }

        $arr_rs = LoginModel::getOneByField('username', $username);

        if (empty($arr_rs) || empty($arr_rs["access_code"]) || $arr_rs["is_del"] == 1) {
            ApiModel::out('Er is geen gebruiker gevonden', 94);
        }

        if (date("Y-m-d H:i:s") > $arr_rs["access_code_date"]) {
            ApiModel::out('Code is verlopen', 95);
        }

        if ($access_code !== $arr_rs["access_code"]) {
            ApiModel::out('Code is niet juist', 97);
        }

        $json["msg"] = "Code is juist";
        $json["status"] = "good";
        ApiModel::out($json, 100, $apiLogId);
    }
}
