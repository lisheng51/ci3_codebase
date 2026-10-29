<?php

class Home extends MX_Controller
{

    public function index()
    {
        $module = CIRouter()->module;
        redirect($module . '/' . AccessCheckModel::$backPath);
    }

    // /opt/plesk/php/8.4/bin/php index.php webmaster home updateversion debug.uniekdating.nl
    public function updateVersion()
    {
        $versionNr = date('Ymd');
        $this->load->database();
        $text = '[' . ENVIRONMENT . '] is bijgewerkt';
        ConfigModel::updateOne($versionNr, '_core_app_buildnr');
        TelegramModel::log($text);
    }
}
