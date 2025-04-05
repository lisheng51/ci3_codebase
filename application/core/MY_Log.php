<?php

class MY_Log extends CI_Log
{

    public function __construct()
    {
        parent::__construct();
    }

    public function write_log($level, $msg)
    {
        if ($this->_enabled === false) {
            return false;
        }

        $level = strtoupper($level);

        if ((!isset($this->_levels[$level]) or ($this->_levels[$level] > $this->_threshold)) && !isset($this->_threshold_array[$this->_levels[$level]])) {
            return false;
        }

        $filepath = $this->_log_path . strtolower($level) . '-' . date('Y-m-d') . '.' . $this->_file_ext;
        $input["time"] = date('H:i:s');
        $input["uri"] = uri_string();
        $input['post'] = json_encode($_POST);
        $input['message'] = $msg;
        $input['get'] = json_encode($_GET);
        $handle = fopen($filepath, "a+");
        $string = json_encode($input);
        fwrite($handle, $string . PHP_EOL);
        fclose($handle);
        if (ENVIRONMENT !== 'development') {
            TelegramModel::log($input["uri"] . PHP_EOL . $msg);
        }
        return true;
    }
}
