<?php

class Login extends Site_Controller
{
    private $tem_key = '2fa_send';

    public function direct()
    {
        $key = rawurldecode(CIInput()->get('hashkey'));
        $arr_keys = explode("#_#", $this->encryption->decrypt($key));
        if (empty($arr_keys[0]) === true) {
            showError();
        }

        $userId = $arr_keys[0];
        $_core_access_code = $arr_keys[1];
        UserModel::joinLogin();
        $arr_rs = UserModel::getOneById($userId);

        if (empty($arr_rs) === true || empty($arr_rs["access_code"]) === true || $arr_rs["is_active"] == 0 || $arr_rs["is_del"] == 1) {
            showError();
        }

        if (date("Y-m-d H:i:s") > $arr_rs["access_code_date"]) {
            showError();
        }

        if ($_core_access_code !== $arr_rs["access_code"]) {
            showError();
        }

        LoginModel::$loginUserData = $arr_rs;
        LoginModel::addSession();
        redirect(AccessCheckModel::redirectUrl());
    }

    public function directEasy()
    {
        $key = rawurldecode(CIInput()->get('key'));
        $arrTokenValue = json_decode($this->encryption->decrypt($key), true);
        $userId = $arrTokenValue[UserModel::$primaryKey];
        if ($userId <= 0) {
            redirect(login_url());
        }

        $token_expires = $arrTokenValue['datetime'] ?? null;
        if (date("Y-m-d H:i:s") > $token_expires) {
            showError();
        }

        UserModel::joinLogin();
        $arr_rs = UserModel::getOneById($userId);

        if (empty($arr_rs) === true || $arr_rs["is_active"] == 0 || $arr_rs["is_del"] == 1) {
            showError();
        }

        LoginModel::$loginUserData = $arr_rs;
        LoginModel::addSession();
        redirect(AccessCheckModel::redirectUrl());
    }

    public function active()
    {
        $key = rawurldecode(CIInput()->get('hashkey'));
        $arr_keys = explode("#_#", $this->encryption->decrypt($key));
        if (empty($arr_keys[0]) === true) {
            showError();
        }

        LoginModel::joinUser();
        $user_db = LoginModel::getOneByField('username', $arr_keys[1]);
        if (empty($user_db) === true) {
            showError();
        }

        if ($user_db["is_active"] > 0) {
            showError();
        }
        LoginModel::updatePasswordResetDate($user_db["user_id"]);
        $encrypted_string = $this->encryption->encrypt($user_db["user_id"] . "#_#" . $user_db["emailaddress"]);
        $hashkey = rawurlencode($encrypted_string);
        redirect(ENVIRONMENT_ACCESS_URL . "/password_reset/reset?hashkey=$hashkey");
    }

    public function index_dev()
    {
        if (!AccessCheckModel::ipAllow()) {
            showError();
        }
        if (CIInput()->post()) {
            $this->index_dev_action();
        }

        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }

        $data["title"] = lang('access_login_title');
        UserModel::$sqlOrderBy = ['user_id' => 'asc'];
        $data["select_user"] = UserModel::select();
        $this->view_layout('index_dev', $data);
    }

    private function index_dev_action()
    {
        $user_id = CIInput()->post(UserModel::$primaryKey) ?? 0;
        UserModel::joinLogin();
        $arr_user = UserModel::getOneById($user_id);
        if (empty($arr_user)) {
            $json["msg"] = "Verkeerde gebruikersnaam!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if ($arr_user["is_active"] == 0 || $arr_user["is_del"] == 1) {
            $json["msg"] = "Verkeerde gebruikersnaam!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        LoginModel::$loginUserData = $arr_user;
        LoginModel::addSession();
        $json["msg"] = "Even geduld aub... U wordt automatisch doorgeschakeld";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = AccessCheckModel::redirectUrl();
        exit(json_encode($json));
    }

    public function index()
    {
        $apiId = 0;
        $code = CIInput()->get('code') ?? null;
        if ($code !== null) {
            $apiId = $this->encryption->decrypt(rawurldecode($code));
            if ($apiId > 0 && LoginModel::userId() > 0) {
                LoginModel::joinUser();
                $arr_user = LoginModel::getOneById(LoginModel::userId());
                $data["expires"] = ApiModel::fetchTokenMin($apiId);
                $data[UserModel::$primaryKey] = LoginModel::userId();
                $data[ApiModel::$primaryKey] = $apiId;
                $data["password_date"] = $arr_user['password_date'];

                $valuestring = json_encode($data);
                $tokenString = $this->encryption->encrypt($valuestring);
                $token = rawurlencode($tokenString);
                $getRedirectUrl = CIInput()->get('redirectUrl') ?? current_url();
                $redirect_url = $getRedirectUrl . '?token=' . $token;
                redirect($redirect_url);
            }
        }


        if (CIInput()->post()) {
            $this->indexAction($apiId);
        }

        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }
        $data["title"] = lang('access_login_title');
        $this->view_layout("index", $data);
    }

    private function sendAccessCode($arr_user = [], $password = null)
    {

        $_core_access_code = CIInput()->post('_core_access_code') ?? "";
        if (empty($_core_access_code) && $arr_user["with_access_code"] > 0) {
            SendMailModel::accessCodeLogin($arr_user, $password);
            $json["msg"] = lang("access_code_send_text");
            $json["status"] = "good";
            $json["type_done"] = "show_access_code_input";
            $json["input_html"] = $this->view_layout_return('access_code_input');
            exit(json_encode($json));
        }

        if (!empty($_core_access_code) && $arr_user["with_access_code"] > 0) {
            LoginModel::$webappAccessCode = $_core_access_code;
        }
    }


    private function send2faCode(array $arr_user = [])
    {
        $verifyCode = CIInput()->post('_core_one_code') ?? "";
        if ($arr_user["with_2fa"] > 0 && !empty($arr_user["phone"]) && empty($arr_user["2fa_secret"])) {
            $hashKey = $this->session->tempdata($this->tem_key);
            if (empty($hashKey)) {
                $v2fa = substr(str_shuffle("0123456789"), 0, 6);
                $this->session->set_tempdata($this->tem_key, $v2fa);
                //TelegramModel::log($v2fa);
                $status = TelegramModel::sms($arr_user["phone"], 'verificatiecode is:' . $v2fa);
                if ($status) {
                    $json["msg"] = lang("access_2facode_label_text") . ' van sms invullen';
                    $json["status"] = "info";
                    $json["type_done"] = "2fa";
                    exit(json_encode($json));
                }
            }

            if (!empty($hashKey) && empty($verifyCode)) {
                $json["msg"] = lang('access_2facode_label_text') . " is niet juist!";
                $json["status"] = "error";
                exit(json_encode($json));
            }

            if (!empty($hashKey) && $verifyCode !== $hashKey) {
                $json["msg"] = lang('access_2facode_label_text') . " is niet juist!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        if ($arr_user["with_2fa"] > 0 && !empty($arr_user["2fa_secret"])) {

            if (empty($verifyCode)) {
                $json["msg"] = lang("access_2facode_label_text") . ' van app invullen';
                $json["status"] = "info";
                $json["type_done"] = "2fa";
                exit(json_encode($json));
            }

            if (LoginModel::check2FA($arr_user, $verifyCode) === false) {
                FailCheckModel::update(FailcheckTypeModel::userLogin);
                $json["msg"] = lang('access_2facode_label_text') . " is niet juist!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }
    }

    private function indexAction(int $apiId = 0)
    {
        $arr_input = CIInput()->post('user') ?? [];
        $username = $arr_input["username"] ?? '';
        $password = $arr_input["password"] ?? '';

        AjaxckModel::value('Gebruikersnaam', $username);
        AjaxckModel::value('Wachtwoord', $password);

        AjaxckModel::failcheck(FailCheckTypeModel::userLogin);
        $json = [];

        $arr_user = LoginModel::find($username, $password);
        if (empty($arr_user)) {
            FailCheckModel::update(FailCheckTypeModel::userLogin);
            $json["msg"] = "Verkeerde gebruikersnaam of wachtwoord!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $this->send2faCode($arr_user);
        // if (LoginModel::check_password_date($arr_user["password_date"]) === false) {
        //     $json["msg"] = "Uw wachtwoord is verlopen, via wachtwoord vergeten kunt u nieuw wachtwoord aanmaken !";
        //     $json["status"] = "error";
        //     exit(json_encode($json));
        // }
        $this->sendAccessCode($arr_user, $password);

        LoginModel::$loginUserData = $arr_user;
        LoginModel::addSession();
        FailCheckModel::reset(FailCheckTypeModel::userLogin);
        $json["msg"] = "Even geduld aub... U wordt automatisch doorgeschakeld";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = AccessCheckModel::redirectUrl();

        if ($apiId > 0) {
            $data["expires"] = ApiModel::fetchTokenMin($apiId);
            $data[UserModel::$primaryKey] = $arr_user[UserModel::$primaryKey];
            $data[ApiModel::$primaryKey] = $apiId;
            $data["password_date"] = $arr_user['password_date'];
            $valuestring = json_encode($data);
            $tokenString = $this->encryption->encrypt($valuestring);
            $token = rawurlencode($tokenString);
            $getRedirectUrl = CIInput()->get('redirectUrl') ?? current_url();
            $redirectUrl = $getRedirectUrl . '?token=' . $token;
            $json["redirect_url"] = $redirectUrl;
        }
        exit(json_encode($json));
    }

    public function logout()
    {
        LoginModel::logout();
        AccessCheckModel::startRedirect(login_url());
    }

    public function logoutAll()
    {
        LoginModel::logout();
        redirect('social_access/Microsoft/logout');
    }
}
