<?php

class Permission extends Back_Controller
{

    protected $title_module = 'Toestemming';

    #[DisplayAttribute('Toestemming sorteren', '')]
    public function sortList()
    {
        $order_num = CIInput()->post('sort_list');
        foreach ($order_num as $id => $value) {
            $data['order_num'] = $value;
            PermissionModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = 'Volgorde is verwerkt!';
        exit(json_encode($json));
    }

    #[DisplayAttribute('Toestemming inline bewerken', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = PermissionModel::getOneById($id);
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
        PermissionModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["rsdb"] = [];
        $data["delButton"] = '';
        $data["title"] = $this->title_module . ' toevoegen';
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Toestemming verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id) === true) {
            redirect($this->controller_url);
        }
        $rsdb = PermissionModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        PermissionModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Toestemming bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        PermissionModel::joinModule();
        $rsdb = PermissionModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }

        $parentValue = '';
        if ($rsdb['parent_id'] > 0) {
            $parentRs = PermissionModel::getOneById($rsdb['parent_id']);
            $parentValue = $parentRs["link_dir"] . '.' . $parentRs["object"] . '.' . $parentRs["method"];
        }
        $data["rsdb"] = $rsdb;
        $data["parentValue"] = $parentValue;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Toestemming overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = setFieldAndOperator(ModuleModel::$primaryKey, PermissionModel::$table . '.' . ModuleModel::$primaryKey);
        $data_where[] = setFieldAndOperator('description', PermissionModel::$table . '.description');
        $data_where[] = setFieldAndOperator('link_dir', PermissionModel::$table . '.link_dir');
        $data_where[] = [ModuleModel::$table . '.is_active' => 1];
        PermissionModel::$sqlWhere = setSqlWhere($data_where);
        PermissionModel::$sqlOrderBy = setFieldOrderBy('order_num#asc');
        PermissionModel::joinModule();
        $total = PermissionModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = null; //GlobalModel::showPage($total);;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module . ' overzicht';
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $arr_result = [];
        $listdb = PermissionModel::getAll();

        foreach ($listdb as $rs) {
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[PermissionModel::$primaryKey]);
            $rs["link_title"] = editInlineButton($this->controller_name . '.editInline', $rs[PermissionModel::$primaryKey], 'link_title', $rs['link_title'] ?? '');
            $rs["description"] = editInlineButton($this->controller_name . '.editInline', $rs[PermissionModel::$primaryKey], 'description', $rs['description'] ?? '');
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $check_double = PermissionModel::fetchExist($data);
        if (!empty($check_double)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $insert_id = PermissionModel::add($data);
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
        $id = CIInput()->post(PermissionModel::$primaryKey);
        $check_double = PermissionModel::fetchExist($data, $id);
        if (!empty($check_double)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb = PermissionModel::getOneById($id);
        if (!empty($rsdb)) {
            PermissionModel::edit($id, $data);
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
        $data = PermissionModel::getPostdata();
        return $data;
    }
}
