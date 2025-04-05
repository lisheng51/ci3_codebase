<?php

class Home extends Back_Controller
{

    protected $title_module = 'Dashboard';

    #[DisplayAttribute('Dashboard', 'Dashboard', 100)]
    public function index()
    {
        $data["title"] = $this->title_module;
        $data["breadcrumb"] = "";
        $data["event_result_box"] = "";
        if (ModuleModel::isActive('widget')) {
            redirect('widget/back/Home/dashboard');
        }
        $this->view_layout("index", $data);
    }
}
