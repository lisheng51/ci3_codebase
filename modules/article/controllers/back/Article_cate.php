<?php

class Article_cate extends Back_Controller
{

    protected $title_module = 'Artikel catergorie';

    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["rsdb"] = null;
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = null;
        $this->view_layout("edit", $data);
    }

    public function del()
    {
        $id = CIInput()->post("del_id");
        $rsdb = ArticleCateModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_del"] = 1;
        ArticleCateModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    public function edit($id)
    {
        $rsdb = ArticleCateModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }

        if (CIInput()->post()) {
            $this->editAction($rsdb);
        }

        $data["title"] = $this->title_module . ' wijzigen';
        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $this->view_layout("edit", $data);
    }

    public function index()
    {
        $data_where[] = [ArticleCateModel::$table . ".is_del" => 0];
        $data_where[] = setFieldAndOperator('search_title', ArticleCateModel::$table . '.name');
        $data_where[] = setFieldAndOperator('search_content', ArticleCateModel::$table . '.description');
        ArticleCateModel::$sqlWhere = setSqlWhere($data_where);
        ArticleCateModel::$sqlOrderBy = setFieldOrderBy();
        $total = ArticleCateModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . " overzicht";
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        list($limit, $page) = GlobalModel::limitAndPage();

        $arr_result = [];
        $listdb = ArticleCateModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["edit_url"] = site_url($this->controller_url . "/edit/{$rs[ArticleCateModel::$primaryKey]}");
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $existdb = ArticleCateModel::getExist($data);
        if (empty($existdb) === false && $existdb["is_del"] > 0) {
            $data_reset["is_del"] = 0;
            ArticleCateModel::edit($existdb[ArticleCateModel::$primaryKey], $data_reset);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is aangemaakt!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        if (empty($check_double) === false) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = ArticleCateModel::add($data);
        if ($insert_id > 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function editAction(array $rsdb  = [])
    {
        $data = $this->getPostdata();
        $id = $rsdb[ArticleCateModel::$primaryKey];
        $existdb = ArticleCateModel::getExist($data, $id);
        if (empty($existdb) === false) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        ArticleCateModel::edit($id, $data);
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        $json["msg"] = $this->title_module . ' is bijgewerkt!';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = ArticleCateModel::getPostdata();
        return $data;
    }
}
