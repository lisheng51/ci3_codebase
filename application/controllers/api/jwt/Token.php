<?php

class Token extends API_Controller
{

    //protected $checkMethodPost = true;
    public function refresh()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT(false);
        $data["expires"] = ApiModel::fetchTokenMin($this->apiId);
        $data[UserModel::$primaryKey] = $dataUser[UserModel::$primaryKey];
        $data[ApiModel::$primaryKey] = $this->apiId;
        $data["ip_address"] = $dataUser["ip_address"];
        $data["browser"] = $dataUser["browser"];
        $data["platform"] = $dataUser["platform"];
        $data["password_date"] = $dataUser['password_date'];

        $valuestring = json_encode($data);
        $tokenString = GlobalModel::encryptData($valuestring);
        $token = rawurlencode($tokenString);
        ApiModel::out($token, 100, $apiLogId);
    }

    public function access()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) === true ? CIInput()->post() : $this->arrPost;

        $username = $postData["username"] ?? null;
        $password = $postData["password"] ?? null;

        if ($username === null || empty($username) === true) {
            ApiModel::out('Geen gebruikersnaam gevonden', 98);
        }

        if ($password === null || empty($password) === true) {
            ApiModel::out('Geen wachtwoord gevonden', 98);
        }

        $arr_user = LoginModel::find($username, $password);
        if (empty($arr_user)) {
            ApiModel::out('Geen account gevonden', 94);
        }

        $data["expires"] = ApiModel::fetchTokenMin($this->apiId);
        $data[UserModel::$primaryKey] = $arr_user[UserModel::$primaryKey];
        $data[ApiModel::$primaryKey] = $this->apiId;
        $data["ip_address"] = CIInput()->ip_address();
        $data["browser"] = $this->agent->browser();
        $data["platform"] = $this->agent->platform();
        $data["password_date"] = $arr_user['password_date'];

        $valuestring = json_encode($data);
        $tokenString = GlobalModel::encryptData($valuestring);
        $token = rawurlencode($tokenString);
        ApiModel::out($token, 100, $apiLogId);
    }
}
