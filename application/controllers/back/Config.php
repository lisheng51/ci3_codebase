<?php

class Config extends Back_Controller
{

    protected $title_module = 'Instelling';

    #[DisplayAttribute('Instelling bekijken', 'Bekijken')]
    public function index()
    {
        if (CIInput()->post()) {
            $this->indexAction();
        }
        $data["title"] = $this->title_module;
        ConfigModel::$configNow = ConfigModel::getAll();
        $data["webapp_smtp_pass"] = "";
        if (!empty(ConfigModel::$configNow["webapp_smtp_pass"])) {
            $data["webapp_smtp_pass"] = GlobalModel::decryptData(ConfigModel::$configNow["webapp_smtp_pass"]);
        }
        $this->view_layout("index", $data);
    }

    private function indexAction()
    {
        $webdbs = CIInput()->post();
        $webdbs["webapp_smtp_pass"] = GlobalModel::encryptData($webdbs["webapp_smtp_pass"]);
        $_core_default_admin_group_ids = CIInput()->post('_core_default_admin_group_ids');
        if (!empty($_core_default_admin_group_ids)) {
            $webdbs['_core_default_admin_group_ids']  = implode(',', $_core_default_admin_group_ids);
        }
        $status = ConfigModel::update($webdbs);
        if ($status) {
            $json["msg"] = $this->title_module . ' is bijgewerkt!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
        $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
        $json["status"] = "error";
        exit(json_encode($json));
    }

    #[DisplayAttribute('Instelling sendmail test', '')]
    public function sendMailTest()
    {
        $to_email = CIInput()->post("to_email") ?? "";
        $from_email = CIInput()->post("user") ?? "";
        $subject = $this->title_module;
        $message = __FUNCTION__;
        $message .= PHP_EOL . 'site: ' . ENVIRONMENT_BASE_URL;
        $message .= PHP_EOL . 'database: ' . ENVIRONMENT_DATABASE;

        if (empty($to_email) || empty($from_email)) {
            $json["msg"] = "Email is niet verzonden";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $mailConfig["Username"] = $from_email;
        $mailConfig["Name"] = CIInput()->post("name") ?? "";
        $mailConfig["Host"] = CIInput()->post("host") ?? "";
        $mailConfig["SMTPSecure"] = CIInput()->post("crypto") ?? "";
        $mailConfig["Password"] = CIInput()->post("pass") ?? "";
        $mailConfig["Port"] = CIInput()->post("port") ?? "";
        $mailConfig["client_id"] = CIInput()->post("client_id") ?? "";
        $mailConfig["client_secret"] = CIInput()->post("client_secret") ?? "";
        $mailConfig["tenant_id"] = CIInput()->post("tenant_id") ?? "";
        $mailConfig["refresh_token"] = CIInput()->post("refresh_token") ?? "";

        if (empty($mailConfig["refresh_token"]) && empty($mailConfig["Password"])) {
            $json["msg"] = "Password of token is leeg";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $arr_mail["from_email"] = $from_email;
        $arr_mail["to_email"] = $to_email;
        $arr_mail["subject"] = $subject;
        $arr_mail["message"] = $message;
        $arr_mail[SendMailModel::$primaryKey] = 0;

        try {
            SendMailModel::$alwaysSend = true;
            $status = SendMailModel::sendAction($arr_mail, $mailConfig);
            if ($status) {
                $json["msg"] = "Email is verzonden";
                $json["status"] = "good";
                exit(json_encode($json));
            }
        } catch (\Exception $e) {
            $debug_result = $e->getMessage();
            $json["msg"] = $debug_result;
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }
}
