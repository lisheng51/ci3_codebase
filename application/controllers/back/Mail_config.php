<?php

class Mail_config extends Back_Controller
{

    protected $title_module = 'Mail config';

    #[DisplayAttribute('Mail config aanmaken', 'Toevoegen')]
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

    #[DisplayAttribute('Mail config verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id)) {
            redirect($this->controller_url);
        }
        $rsdb = MailConfigModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        MailConfigModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Mail config bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = MailConfigModel::getOneById($id);
        if (empty($rsdb) === true) {
            redirect($this->controller_url);
        }

        $rsdb["pass"] = GlobalModel::decryptData($rsdb["pass"]);
        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Mail config overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = [MailConfigModel::$table . '.' . MailConfigModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('user', MailConfigModel::$table . '.user');
        MailConfigModel::$sqlWhere = setSqlWhere($data_where);
        MailConfigModel::$sqlOrderBy = setFieldOrderBy();
        $total = MailConfigModel::getTotal();
        $data["listdb"] = $this->getData();

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

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = MailConfigModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[MailConfigModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $check_double = MailConfigModel::getOneByField('user', $data["user"]);
        if (empty($check_double) === false && $check_double["is_del"] > 0) {
            $data_reset["is_del"] = 0;
            MailConfigModel::edit($check_double[MailConfigModel::$primaryKey], $data_reset);
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

        $insert_id = MailConfigModel::add($data);
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
        $id = CIInput()->post(MailConfigModel::$primaryKey);
        $rsdb = MailConfigModel::getOneById($id);
        $existdb = MailConfigModel::getOneByField('user', $data["user"]);
        if (empty($existdb) === false && $id != $existdb[MailConfigModel::$primaryKey]) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        if (empty($rsdb) === false) {
            MailConfigModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = MailConfigModel::getPostdata();
        return $data;
    }
}
