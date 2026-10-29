<?php
class Pre_system
{
    public function library_autoload()
    {
        $helpers = glob(APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($helpers as $filename) {
            require_once $filename;
        }
    }
}
