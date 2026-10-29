<?php

class Article extends Site_Controller
{
    public function view($id = 0)
    {
        if (is_numeric($id) === false) {
            redirect($this->controller_url);
        }
        $rsdb = ArticleModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        $data["rsdb"] = $rsdb;
        $data["title"] = $rsdb["title"];
        ArticleModel::updateHits($rsdb);
        $this->view_layout("view", $data);
    }
}
