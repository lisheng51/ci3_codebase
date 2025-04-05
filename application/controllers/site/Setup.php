<?php

class Setup extends Site_Controller
{

    public function index()
    {
        if (LoginModel::userId() > 0) {
            redirect(AccessCheckModel::redirectUrl());
        }

        if (CIInput()->post()) {
            $this->indexAction();
        }

        $data["title"] = "Setup";
        $data["h4"] = "Maak webmaster aan";
        $this->view_layout("index", $data);
    }

    private function insertSqlFile()
    {
        $fileList = ModuleModel::sqlFiles();
        foreach ($fileList as $tableName) {
            if ($this->db->table_exists($tableName) === false) {
                $templine = '';
                $lines = file(APPPATH . "sql" . DIRECTORY_SEPARATOR . $tableName . '.sql');
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '') {
                        continue;
                    }
                    $templine .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        $this->db->query($templine);
                        $templine = '';
                    }
                }
            }
        }
    }

    private function indexAction()
    {
        $databasename = $this->db->database;
        $this->load->dbutil();
        if ($this->dbutil->database_exists($databasename) === false) {
            $this->load->dbforge();
            if ($this->dbforge->create_database($databasename) === false) {
                $json["msg"] = "Database bestaat nog niet!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }
        $this->insertSqlFile();
        $rsdb = UserModel::getOne();
        if (!empty($rsdb)) {
            $json["msg"] = "Installatie is al gedaan!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $email = CIInput()->post("email");
        $password = CIInput()->post("password");
        AjaxckModel::value('email', $email);
        AjaxckModel::value('password', $password);
        $data["emailaddress"] = $email;
        $data["is_active"] = 1;
        $data["display_info"] = 'Web master';
        $uid = UserModel::add($data);
        if ($uid > 0) {
            $this->makeUploadDir();
            $this->makeDir(config_item('cache_path'));
            $this->makeDir(config_item('sess_save_path'));
            $this->makeDir(config_item('log_path'));
            $data_login["user_id"] = $uid;
            $data_login["username"] = $email;
            $data_login["password_date"] = date('Y-m-d H:i:s');
            $data_login["password"] = password_hash($password, PASSWORD_DEFAULT);
            LoginModel::add($data_login);
            $data_newpic["createdby"] = $uid;
            UserModel::edit($uid, $data_newpic);
            UploadTypeModel::makeDir($uid);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = login_url();
            $json["msg"] = "Installatie is oke!";
            $json["status"] = "good";
            exit(json_encode($json));
        }
    }

    private function makeDir(string $path = "")
    {
        if (empty($path) === false && is_dir($path) === false) {
            mkdir($path, 0755, true);
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
        }

        if (empty($path) === false && is_dir($path) === true) {
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
        }
    }

    private function makeUploadDir()
    {
        $val = UploadTypeModel::fetchData(1, 'allowed_types');
        $path = FCPATH . UploadModel::$rootFolder;
        if (empty($path) === false && is_dir($path) === false) {
            mkdir($path, 0755, true);
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'SetEnvIfNoCase Request_URI "\\.(' . $val . ')$" let_me_in
Order Deny,Allow
Deny from All
Allow from env=let_me_in');
        }

        if (empty($path) === false && is_dir($path)) {
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'SetEnvIfNoCase Request_URI "\\.(' . $val . ')$" let_me_in
Order Deny,Allow
Deny from All
Allow from env=let_me_in');
        }
    }
}
