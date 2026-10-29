<?php

class Home extends Su_Controller
{
    public function sql()
    {
        $module = $this->router->module;
        $add_insert_list = [];
        exit(GlobalModel::makeSql($module, $add_insert_list));
    }
}
