<?php

class Module extends Back_Controller
{
    protected $title_module = 'Module';

    #[DisplayAttribute('Module inline wijzigen')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = ModuleModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $editType = CIInput()->post("type") ?? "string";
        $field = CIInput()->post("field") ?? "";

        if (empty($field)) {
            $json["msg"] =  'Geen veld gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $fieldvalue = null;

        if ($editType === 'string') {
            $fieldvalue = (string)CIInput()->post("fieldvalue");
        }

        if ($editType === 'boolean') {
            $fieldvalue = (int)CIInput()->post("fieldvalue");
        }

        if ($fieldvalue === null) {
            $json["msg"] = 'Geen waard gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data[$field] = $fieldvalue;
        ModuleModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Module sorteren', '')]
    public function sortList()
    {
        $order_num = CIInput()->post('sort_list');
        foreach ($order_num as $id => $value) {
            $data['sort_list'] = $value;
            ModuleModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = 'Volgorde is verwerkt!';
        exit(json_encode($json));
    }

    #[DisplayAttribute('Module bijwerken', '')]
    public function update($dir = 'back')
    {
        $moduleId = CIInput()->post('id') ?? 0;
        $arrModule = ModuleModel::getOneById(intval($moduleId));
        if (empty($arrModule) === true) {
            $json["status"] = "error";
            $json['msg'] = 'Geen module gevonden!';
            exit(json_encode($json));
        }
        $data = ModuleModel::getInfo($arrModule["path"]);
        if (empty($data)) {
            $json["status"] = "error";
            $json['msg'] = 'Geen info gevonden!';
            exit(json_encode($json));
        }

        ModuleModel::update($data, $dir);
        ModuleModel::setup($data);
        $json["msg"] = "Module " . $data["path_description"] . " is bijgewerkt!";
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Module toevoegen', '')]
    public function add($dir = 'back')
    {
        $path = CIInput()->post('path') ?? "";
        $data = ModuleModel::getInfo($path);
        if (empty($data)) {
            $json["status"] = "error";
            $json['msg'] = 'Geen info gevonden!';
            exit(json_encode($json));
        }

        ModuleModel::update($data, $dir);
        ModuleModel::setup($data);
        $json["msg"] = "Module " . $data["path_description"] . " is geïnstalleerd!";
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Module verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id) === true) {
            redirect($this->controller_url);
        }
        $rsdb = ModuleModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        //$data["is_active"] = 0;
        //ModuleModel::edit($id, $data);
        ModuleModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Module overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = setFieldAndOperator('path_description', ModuleModel::$table . '.path_description');
        $is_active = CIInput()->post_get("is_active") ?? "";
        if ($is_active != "") {
            $is_active = intval($is_active);
            $data_where[] = [ModuleModel::$table . '.' . "is_active" => $is_active];
        }

        ModuleModel::$sqlWhere = setSqlWhere($data_where);
        ModuleModel::$sqlOrderBy = setFieldOrderBy('sort_list#asc');
        $total = ModuleModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module . ' overzicht';
        $data["new"] = $this->getNew($data["listdb"]);
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $arr_result = [];
        $listdb = ModuleModel::getAll();
        foreach ($listdb as $rs) {
            $rs["is_active"] = editBooleanInlineButton($this->controller_name . '.editInline', $rs[ModuleModel::$primaryKey], 'is_active', $rs['is_active']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[ModuleModel::$primaryKey]);
            $rs["changelog_url"] = site_url($this->path_name . "/Change_log/index/{$rs[ModuleModel::$primaryKey]}");
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    #[DisplayAttribute('Module bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = ModuleModel::getOneById($id);
        if (empty($rsdb) === true) {
            redirect($this->controller_url);
        }
        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $this->view_layout("edit", $data);
    }

    private function editAction()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = ModuleModel::getPostdata();
        $id = CIInput()->post(ModuleModel::$primaryKey);
        $rsdb = ModuleModel::getOneById($id);
        if (empty($rsdb) === false) {
            ModuleModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function getNew(array $listdbnow = []): array
    {
        $modulesmap = directory_map(FCPATH  . 'modules' . DIRECTORY_SEPARATOR, 1);
        $arrResultNew[] = '_core';
        $arrResultNow = [];
        foreach ($modulesmap as $module) {
            $arrResultNew[] = rtrim($module, DIRECTORY_SEPARATOR);
        }

        if (empty($arrResultNew)) {
            return [];
        }

        foreach ($listdbnow as $module) {
            $arrResultNow[] = $module["path"];
        }

        $result = array_diff($arrResultNew, $arrResultNow);
        $listdb = [];
        foreach ($result as $key => $module) {
            $data = ModuleModel::getInfo($module);
            if (isset($data['path'])) {
                $listdb[$key]["path"] = $data["path"];
                $listdb[$key]["path_description"] = $data["path_description"];
            }
        }

        $arr["total"] = count($listdb);
        $arr["listdb"] = $listdb;
        return $arr;
    }
}
