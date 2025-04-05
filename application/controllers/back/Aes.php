<?php

class Aes extends Back_Controller
{
    protected $title_module = 'AES Test';

    private function encrypt(): string
    {
        $text = CIInput()->post("text") ?? "";
        $password = CIInput()->post("password") ?? "";
        if (empty($password) === false) {
            AesModel::$password = $password;
        }
        $key = CIInput()->post("key") ?? "";
        if (empty($key) === false) {
            AesModel::$key = $key;
        }
        return AesModel::encrypt($text);
    }

    private function decrypt(): string
    {
        $hash = CIInput()->post("hash") ?? "";
        $password = CIInput()->post("password") ?? "";
        if (empty($password) === false) {
            AesModel::$password = $password;
        }

        $key = CIInput()->post("key") ?? "";
        if (empty($key) === false) {
            AesModel::$key = $key;
        }
        return AesModel::decrypt($hash);
    }

    #[DisplayAttribute('AES Test', 'AES Test')]
    public function index()
    {
        if (CIInput()->post("encrypt") === "yes") {
            $json["msg"] = $this->encrypt();
            exit(json_encode($json));
        }

        if (CIInput()->post("decrypt") === "yes") {
            $json["msg"] = $this->decrypt();
            exit(json_encode($json));
        }
        $data["title"] = 'AES test';
        $data["password"] = AesModel::$password;
        $data["key"] = AesModel::$key;
        $this->view_layout("index", $data);
    }
}
