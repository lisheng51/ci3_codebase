<?php

class Home extends Back_Controller
{
    public function index()
    {
        redirect($this->module_path_name . '/table/index');
    }
}
