<?php

class Home extends MX_Controller
{

    public function index()
    {
        $module = CIRouter()->module;
        redirect($module . '/' . AccessCheckModel::$backPath . '/' . $module);
    }
}
