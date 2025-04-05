<?php

class Spamword extends Back_Controller
{

    protected $title_module = 'Spamword';

    #[DisplayAttribute('Spamword toevoegen', 'Aanmaken')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["rsdb"] = null;
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = null;
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Spamword verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id)) {
            redirect($this->controller_url);
        }
        $rsdb = SpamwordModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        SpamwordModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Spamword bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $data["rsdb"] = SpamwordModel::getOneById($id);
        if (empty($data["rsdb"]) === true) {
            redirect($this->controller_url);
        }

        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Spamword inline bewerken', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = SpamwordModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $field = CIInput()->post("field") ?? "";
        $fieldvalue = CIInput()->post("fieldvalue") ?? "";

        if (empty($field) === true) {
            $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data[$field] = $fieldvalue;

        if ($field === 'word') {
            $existdb = SpamwordModel::getOneByField($field, $data[$field]);
            if (empty($existdb) === false && $id != $existdb[SpamwordModel::$primaryKey]) {
                $json["msg"] = $this->title_module . ' bestaat al!';
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        SpamwordModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Spamword overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = [SpamwordModel::$table . '.' . SpamwordModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('word', SpamwordModel::$table . '.word');
        SpamwordModel::$sqlWhere = setSqlWhere($data_where);
        SpamwordModel::$sqlOrderBy = setFieldOrderBy();
        $total = SpamwordModel::getTotal();
        $data["listdb"] = $this->getList();

        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["event_result_box"] = "";
        $data["title"] = $this->title_module . ' overzicht';
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $this->view_layout("index", $data);
    }

    private function getList()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = SpamwordModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["word"] = editInlineButton($this->controller_name . '.editInline', $rs[SpamwordModel::$primaryKey], 'word', $rs['word']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[SpamwordModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $check_double = SpamwordModel::getOneByField('word', $data["word"]);
        if (!empty($check_double) && $check_double[SpamwordModel::$fieldIsDel] > 0) {
            $data_reset["is_del"] = 0;
            SpamwordModel::edit($check_double[SpamwordModel::$primaryKey], $data_reset);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        if (empty($check_double) === false) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = SpamwordModel::add($data);
        if ($insert_id > 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function editAction()
    {
        $data = $this->getPostdata();
        $id = CIInput()->post(SpamwordModel::$primaryKey);
        $rsdb = SpamwordModel::getOneById($id);
        $existdb = SpamwordModel::getOneByField('word', $data["word"]);

        if (empty($existdb) === false && $id != $existdb[SpamwordModel::$primaryKey]) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        if (empty($rsdb) === false) {
            SpamwordModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = SpamwordModel::getPostdata();
        return $data;
    }
}
