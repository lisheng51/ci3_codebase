<?php

class MY_Log extends CI_Log
{
    public function write_log($level, $msg)
    {
        $result = parent::write_log($level, $msg);
        if ($result && ENVIRONMENT !== 'development') {
            TelegramModel::log(($_SERVER['REQUEST_URI'] ?? null) . PHP_EOL . $msg);
        }
        return $result;
    }
}
