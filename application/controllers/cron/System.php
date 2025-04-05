<?php

class System extends Cron_Controller
{

    public function resetSession()
    {
        $path = config_item('sess_save_path');
        delete_files($path);
        file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
        TelegramModel::log(__METHOD__);
    }

    public function index()
    {
        exit(ENVIRONMENT_UPLOAD_PATH . '|' . ENVIRONMENT_DATABASE);
    }

    public function optimize()
    {
        $this->load->dbutil();
        $this->dbutil->optimize_database();
        TelegramModel::log(__METHOD__);
    }

    public function dbBackup(string $datbase = "")
    {
        if (empty($datbase)) {
            $datbase = $this->db->database;
        }
        $this->db->db_select($datbase);
        $this->load->dbutil($this->db);

        if ($this->dbutil->database_exists($datbase)) {
            $path = config_item('cache_path') . date('Ymd') . DIRECTORY_SEPARATOR;
            if (is_dir($path) === false) {
                mkdir($path, 0755, true);
            }
            $ck_file_exist_path = $path . $datbase . '.gz';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $backup = $this->dbutil->backup();
            write_file($ck_file_exist_path, $backup);
            TelegramModel::log(__METHOD__ . ' => ' . $datbase);
        }
    }

    public function dbRestore(string $datbase = "", string $datefolder = "")
    {
        if (empty($datbase)) {
            $datbase = $this->db->database;
        }

        if (empty($datefolder)) {
            $datefolder = date('Ymd');
        }
        $this->db->db_select($datbase);
        $this->load->dbutil($this->db);

        $path = config_item('cache_path') . $datefolder . DIRECTORY_SEPARATOR;
        $ck_file_exist_path = $path . $datbase . '.gz';
        if (file_exists($ck_file_exist_path) && $this->dbutil->database_exists($datbase)) {
            $templine = '';
            $lines = gzfile($ck_file_exist_path);
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $this->db->simple_query($templine);
                    $templine = '';
                }
            }
            TelegramModel::log(__METHOD__ . ' => ' . $datbase);
        }
    }
}
