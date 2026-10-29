<?php

class Password_reset extends Site_Controller
{

    private $tem_key = "password_rest_form_send";

    public function index()
    {
        if ($this->input->post()) {
            $this->index_action();
        }

        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }
        $data["title"] = "Wachtwoord vergeten ?";
        $data["h4"] = "Wachtwoord vergeten ?";
        $this->view_layout("index", $data);
    }

    private function index_action()
    {
        AjaxckModel::toMuchSendMail($this->tem_key);
        AjaxckModel::failcheck(FailCheckTypeModel::PasswordReset);
        $arr_input = $this->input->post('user');
        $emailaddress = $arr_input["username"];
        $json = [];

        AjaxckModel::value('email', $emailaddress);

        if (empty($emailaddress) === true) {
            $json["msg"] = "Emailadres is leeg!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        LoginModel::joinUser();
        $user_db = LoginModel::getOneByField('username', $emailaddress);
        if (empty($user_db) === true || $user_db["is_active"] == 0) {
            FailCheckModel::update(FailCheckTypeModel::PasswordReset);
            $json["msg"] = "Er is geen gebruiker gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        SendMailModel::passwordReset($user_db);
        FailCheckModel::reset(FailCheckTypeModel::PasswordReset);
        $json["msg"] = "Er is een herstelmail naar uw mailbox gestuurd";
        $json["status"] = "good";
        $this->session->set_tempdata($this->tem_key, $this->input->ip_address());
        exit(json_encode($json));
    }

    public function reset()
    {
        if ($this->input->post()) {
            $this->reset_action();
        }

        $hashkey = rawurldecode($this->input->get('hashkey'));

        $arr_keys_check = explode("#_#", $this->encryption->decrypt($hashkey));
        if (empty($arr_keys_check[0]) === true) {
            showError();
        }

        $user_db_check = LoginModel::getOneByField('username', $arr_keys_check[1]);
        if (empty($user_db_check) === true || $user_db_check["user_id"] !== $arr_keys_check[0]) {
            showError();
        }

        if (time() > strtotime($user_db_check["password_reset_date"])) {
            showError();
        }

        $data["title"] = "Wachtwoord instellen";
        $data["h4"] = "Wachtwoord instellen";
        $this->view_layout("set_password", $data);
    }

    private function reset_action()
    {
        $password1 = $this->input->post('password1');
        $password2 = $this->input->post('password2');
        $hashkey = rawurldecode($this->input->get('hashkey'));
        $json = [];

        $arr_keys_check = explode("#_#", $this->encryption->decrypt($hashkey));
        $user_db_check = LoginModel::getOneByField('username', $arr_keys_check[1]);
        if (empty($user_db_check) === true || $user_db_check["user_id"] !== $arr_keys_check[0]) {
            $json["msg"] = "Geen gebruiker gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (time() > strtotime($user_db_check["password_reset_date"])) {
            $json["msg"] = "Uw wachtwoordherstel verzoeken is verlopen, graag dient om opnieuw aan te vragen!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $uid = $user_db_check["user_id"];

        if (empty($password1) === true || empty($password2) === true) {
            $json["msg"] = "Wachtwoord is leeg!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if ($password1 !== $password2) {
            $json["msg"] = "Wachtwoorden zijn niet gelijk!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (ENVIRONMENT !== 'development') {
            AjaxckModel::password($password1);
            if (password_verify($password1, $user_db_check["password"]) === true) {
                $json["msg"] = "Wachtwoord mag niet hetzelfde zijn als de vorige!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        $data_login["password"] = password_hash($password1, PASSWORD_DEFAULT);
        $data_login["password_date"] = date('Y-m-d H:i:s');
        $data_login["password_reset_date"] = null;
        LoginModel::edit($uid, $data_login);

        $json["msg"] = "Wachtwoord is nu verwerkt";
        if ($user_db_check["is_del"] > 0) {
            UserModel::edit($uid, array("is_del" => 0));
        }

        if ($user_db_check["is_active"] == 0) {
            $data["is_active"] = 1;
            UserModel::edit($uid, $data);
            $json["msg"] = "Uw account is nu geactiveerd";
            SendMailModel::userActiveConfirm($user_db_check);
        }

        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = login_url();
        exit(json_encode($json));
    }
}
