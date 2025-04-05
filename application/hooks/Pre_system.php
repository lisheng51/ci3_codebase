<?php
class Pre_system
{

    public function webmaster_vendor_autoload()
    {
        $filename = FCPATH. 'modules' . DIRECTORY_SEPARATOR . 'webmaster' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (file_exists($filename)) {
            require_once($filename);
        }
    }

    public function library_autoload()
    {
        $helpers = glob(APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($helpers as $filename) {
            require_once $filename;
        }
    }
}
