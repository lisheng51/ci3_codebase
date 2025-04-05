<?php

class Home extends Su_Controller
{

    public function sql()
    {
        $add_insert_list = ['bc_language'];
        exit(GlobalModel::makeSql('', $add_insert_list));
    }
}
