<?php

class Home extends Su_Controller
{

    public function sql()
    {
        $module = $this->router->module;
        $add_insert_list = ['bc_article_cate', 'bc_article_status'];
        exit(GlobalModel::makeSql($module, $add_insert_list));
    }
}
