<?php

class Mail_temp extends Back_Controller
{

    protected $title_module = 'Mail template';

    #[DisplayAttribute('Mail template toevoegen', 'Aanmaken')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["rsdb"] = [];
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = '';
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Mail template verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id)) {
            redirect($this->controller_url);
        }
        $rsdb = MailTempModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        MailTempModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Mail template bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = MailTempModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Mail template overzicht', 'Overzicht')]
    public function index()
    {
        MailTempModel::joinLanguage();
        $data_where[] = [MailTempModel::$table . '.' . MailTempModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('subject', MailTempModel::$table . '.subject');
        $data_where[] = setFieldAndOperator('trigger_name', MailTempModel::$table . '.trigger_name');
        $data_where[] = setFieldAndOperator(LanguageModel::$primaryKey, MailTempModel::$table . '.' . LanguageModel::$primaryKey);
        MailTempModel::$sqlWhere = setSqlWhere($data_where);
        MailTempModel::$sqlOrderBy = setFieldOrderBy();
        $total = MailTempModel::getTotal();
        $data["listdb"] = $this->getData();

        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["event_result_box"] = '';
        $data["title"] = $this->title_module . ' overzicht';
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('Mail template inline bewerken', '')]
    public function view(int $language_id = 1)
    {
        if (CIInput()->post()) {
            $this->viewAction();
        }

        $module = CIInput()->get('module') ?? '';
        $where[] = [MailTempModel::$fieldIsDel => 0];
        $where[] = [LanguageModel::$primaryKey => $language_id];
        if (!empty($module)) {
            $where[] = setFieldAndOperator('', MailTempModel::$table . '.' . 'trigger_name', $module . '_', false, 'regexp');
        }
        MailTempModel::$sqlWhere = setSqlWhere($where);
        $listdb = MailTempModel::getAll();
        if (empty($listdb)) {
            redirect($this->controller_url);
        }
        $rsdb = LanguageModel::getOneById($language_id);
        $data["listdb"] = $listdb;
        $data["title"] = $this->title_module . ' wijzigen - ' . $rsdb['name'];
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("view", $data);
    }

    private function viewAction()
    {
        $ids = CIInput()->post('mail_temp_subject');
        foreach ($ids as $id => $subject) {
            $body = CIInput()->post('mail_temp_body[' . $id . ']');
            $data['subject'] = $subject;
            $data['body'] = $body;
            MailTempModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = $this->title_module . ' is bijgewerkt';
        exit(json_encode($json));
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = MailTempModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[MailTempModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $existdb = MailTempModel::getExist($data);

        if (!empty($existdb) && $existdb[MailTempModel::$fieldIsDel] > 0) {
            $data[MailTempModel::$fieldIsDel] = 0;
            MailTempModel::edit($existdb[MailTempModel::$primaryKey], $data);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        if (!empty($existdb)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = MailTempModel::add($data);
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
        $id = CIInput()->post(MailTempModel::$primaryKey);

        $existdb2 = MailTempModel::getExist($data);
        if (!empty($existdb2) && $existdb2[MailTempModel::$fieldIsDel] > 0) {
            CIDb()->from(MailTempModel::$table)->where(MailTempModel::$primaryKey, $existdb2[MailTempModel::$primaryKey])->limit(1)->delete();
        }

        $existdb = MailTempModel::getExist($data, $id);
        if (!empty($existdb)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb = MailTempModel::getOneById($id);
        if (!empty($rsdb)) {
            MailTempModel::edit($id, $data);
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
        $data = MailTempModel::getPostdata();
        return $data;
    }
}
