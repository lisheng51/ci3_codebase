<?php

class Register extends Site_Controller
{

    private $tem_key = "register_form_send";

    public function index()
    {
        if (!AccessCheckModel::ipAllow()) {
            showError();
        }
        if ($this->input->post()) {
            $this->add_action();
        }

        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }

        $data["title"] = "Welkom bij registreren";
        $data["h4"] = "Registreren";
        $data["emailaddress"] = $this->input->get("emailaddress");
        $this->view_layout("index", $data);
    }

    public function active()
    {
        $key = rawurldecode($this->input->get('hashkey'));
        $arr_keys = explode("#_#", $this->encryption->decrypt($key));
        if (empty($arr_keys[0]) === true) {
            showError();
        }

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

    private function get_postdata()
    {
        $arr_input = $this->input->post('user');
        $data["emailaddress"] = strtolower($arr_input["username"]);
        $data["permission_group_ids"] = 2;
        AjaxckModel::value('email', $data["emailaddress"]);
        return $data;
    }

    private function send_active_mail(array $arr_user = [])
    {
        if (empty($arr_user) === true) {
            return;
        }
        SendMailModel::userActive($arr_user);
        $json["msg"] = "Bedankt voor het aanmelden, een activatiemail is naar uw mail gestuurd!";
        $json["status"] = "good";
        $this->session->set_tempdata($this->tem_key, $this->input->ip_address());
        exit(json_encode($json));
    }

    private function add_action()
    {
        AjaxckModel::toMuchSendMail($this->tem_key);
        $data = $this->get_postdata();

        $arr_user = LoginModel::getOneByField('username', $data["emailaddress"]);
        if (empty($arr_user) === false && $arr_user["is_active"] > 0) {
            $json["msg"] = "Het emailadres bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (empty($arr_user) === false && $arr_user["is_active"] == 0) {
            $this->send_active_mail($arr_user);
        }

        $uid = UserModel::add($data);
        if ($uid > 0) {
            $data_login["user_id"] = $uid;
            $data_login["username"] = $data["emailaddress"];
            $data_login["redirect_url"] = '';
            LoginModel::add($data_login);
            $data_newpic["createdby"] = $uid;
            UserModel::edit($uid, $data_newpic);
            UploadTypeModel::makeDir($uid);
            $this->send_active_mail(UserModel::getOneById($uid));
        }
    }
}
