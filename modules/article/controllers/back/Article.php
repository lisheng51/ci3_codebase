<?php

class Article extends Back_Controller
{

    protected $title_module = 'Artikel';

    //#[PermissionAttribute('asdfasdf.asdfasf.asdfasf')]
    public function add()
    {
        if ($this->input->post()) {
            $this->addAction();
        }
        $data["rsdb"] = null;
        $data["created_at"] = date('d-m-Y H:i:s');
        $data["title"] = $this->title_module . " toevoegen";
        $data["delButton"] = null;
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    public function del()
    {
        $id = $this->input->post("del_id");
        $rsdb = ArticleModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . " is niet gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_del"] = 1;
        ArticleModel::edit($id, $data);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    public function edit($id)
    {
        $rsdb = ArticleModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }

        if ($this->input->post()) {
            $this->editAction($rsdb);
        }

        $data["rsdb"] = $rsdb;
        $data["created_at"] = F_datetime::convert_datetime($rsdb["created_at"]);
        $data["title"] = $this->title_module . " wijzigen";
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    public function index()
    {
        $data_where[] = [ArticleModel::$table . ".is_del" => 0];
        $data_where[] = setFieldAndOperator('search_title', ArticleModel::$table . '.title');
        $data_where[] = setFieldAndOperator('search_description', ArticleModel::$table . '.description');
        $data_where[] = setFieldAndOperator(ArticleCateModel::$primaryKey, ArticleModel::$table . '.' . ArticleCateModel::$primaryKey);

        ArticleModel::$sqlSelect[] = [ArticleCateModel::$table => 'name as cate_name'];
        ArticleModel::$sqlJoin[] = [ArticleCateModel::$table => ArticleCateModel::$primaryKey];

        ArticleModel::$sqlSelect[] = [ArticleStatusModel::$table => 'label as status_name'];
        ArticleModel::$sqlJoin[] = [ArticleStatusModel::$table => ArticleStatusModel::$primaryKey];

        ArticleModel::$sqlWhere = setSqlWhere($data_where);
        ArticleModel::$sqlOrderBy = setFieldOrderBy();
        $total = ArticleModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);

        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if ($this->input->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . " overzicht";
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        list($limit, $page) = GlobalModel::limitAndPage();

        $arr_result = [];
        $listdb = ArticleModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["created_at"] = F_datetime::convert_datetime($rs["created_at"]);
            $rs["edit_url"] = site_url($this->controller_url . "/edit/{$rs[ArticleModel::$primaryKey]}");
            $rs["view_url"] = site_url($this->controller_url . "/view/{$rs[ArticleModel::$primaryKey]}");
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $existdb = ArticleModel::getExist($data);
        if (empty($existdb) === false && $existdb["is_del"] > 0) {
            $data_reset["is_del"] = 0;
            ArticleModel::edit($existdb[ArticleModel::$primaryKey], $data_reset);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . " is aangemaakt!";
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        if (empty($check_double) === false) {
            $json["msg"] = $this->title_module . " bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = ArticleModel::add($data);
        if ($insert_id > 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . " is aangemaakt!";
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function editAction(array $rsdb = [])
    {
        $data = $this->getPostdata();
        $id = $rsdb[ArticleModel::$primaryKey];
        $existdb = ArticleModel::getExist($data, $id);
        if (empty($existdb) === false) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        ArticleModel::edit($id, $data);
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        $json["msg"] = $this->title_module . " is bijgewerkt!";
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function getPostdata()
    {
        if ($this->input->post("del_id")) {
            $this->del();
        }
        $data = ArticleModel::getPostdata();
        return $data;
    }

    public function view($id = 0)
    {
        $data["rsdb"] = ArticleModel::getOneById($id);
        if (empty($data["rsdb"]) === true) {
            redirect($this->controller_url);
        }

        $data["title"] = $data["rsdb"]["title"];
        $data["event_result_box"] = "";
        ArticleModel::updateHits($data["rsdb"]);
        $this->view_layout("view", $data);
    }
}
