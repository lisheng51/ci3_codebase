<?php

class Home extends MX_Controller
{

    public function index()
    {
        $module = $this->router->module;
        redirect($module . '/site');
    }
}
