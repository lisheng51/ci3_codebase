<?php

class Permission_group extends Back_Controller
{

    protected $title_module = 'Toestemming groep';

    public function update()
    {
        $gid = CIInput()->get("gid") ?? 0;
        $pid = CIInput()->get("pid") ?? 0;
        $status = PermissionGroupModel::updateOnePermission($gid, $pid);
        if ($status) {
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
        $json["status"] = "error";
        exit(json_encode($json));
    }

    public function all(int $typeid = 1)
    {
        PermissionModel::joinModule();
        PermissionModel::$sqlOrderBy = ['sort_list' => 'asc', 'order_num' => 'asc'];
        PermissionModel::$sqlWhere = [ModuleModel::$table . ".is_active" => 1];
        $listdbAll = PermissionModel::getAll();
        $listdbGroup = PermissionGroupModel::allByTypeId($typeid);
        $listdb = PermissionModel::getTree($listdbAll);
        $data["listdb"] = $this->buildOptions($listdb, $listdbGroup);
        $data["listdbGroup"] = $listdbGroup;
        $this->view_layout("all", $data);
    }

    #[DisplayAttribute('Toestemming groep inline bewerken', '')]
    public function sortList()
    {
        $order_num = CIInput()->post('sort_list');
        foreach ($order_num as $id => $value) {
            $data['sort_list_group'] = $value;
            PermissionGroupModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = 'Volgorde is verwerkt!';
        exit(json_encode($json));
    }

    private function buildOptions(array $listdb = [], array $listdbGroup = []): array
    {
        $arrResult = [];
        $allids = array_column($listdbGroup, PermissionGroupModel::$primaryKey);
        foreach ($listdb as $rs) {
            $gids = [];
            foreach ($listdbGroup as $item) {
                $has = PermissionGroupModel::hasPermission($item, $rs[PermissionModel::$primaryKey]);
                if ($has) {
                    $gids[] = $item[PermissionGroupModel::$primaryKey];
                }
            }

            $rs['allids'] = $allids;
            $rs['gids'] = $gids;
            if (!empty($rs['_child'])) {
                $rs['_child'] =  $this->buildOptions($rs['_child'], $listdbGroup);
            }
            $rs["show_child"] = $this->view_layout_return('child_option', $rs);
            $arrResult[] = $rs;
        }

        return $arrResult;
    }




    #[DisplayAttribute('Toestemming groep toevoegen', 'Aanmaken')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["rsdb"] = [];
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = '';
        $data["permissions"] = $this->getPermission();
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Toestemming groep verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id) === true) {
            redirect($this->controller_url);
        }
        $rsdb = PermissionGroupModel::getOneById($id);
        if (empty($rsdb) === true || $rsdb['is_lock'] > 0) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_del"] = 1;
        PermissionGroupModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Toestemming groep bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = PermissionGroupModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }

        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["permissions"] = $this->getPermission($rsdb["permission_ids"]);
        $data["title"] = $this->title_module . ' wijzigen';

        $this->view_layout("edit", $data);
    }


    #[DisplayAttribute('Toestemming groep inline bewerken', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = PermissionGroupModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $field = CIInput()->post("field") ?? "";
        $fieldvalue = CIInput()->post("fieldvalue") ?? "";

        if (empty($field)) {
            $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data[$field] = $fieldvalue;

        if ($field === 'name') {
            $existdb = PermissionGroupModel::getOneByField($field, $data[$field]);
            if (empty($existdb) === false && $id != $existdb[PermissionGroupModel::$primaryKey]) {
                $json["msg"] = $this->title_module . ' bestaat al!';
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        PermissionGroupModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Toestemming groep overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = [PermissionGroupModel::$table . '.' . PermissionGroupModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('name', PermissionGroupModel::$table . '.name');
        $data_where[] = setFieldAndOperator(PermissionGroupTypeModel::$primaryKey, PermissionGroupModel::$table . '.' . PermissionGroupTypeModel::$primaryKey);
        PermissionGroupModel::$sqlWhere = setSqlWhere($data_where);
        PermissionGroupModel::$sqlOrderBy = setFieldOrderBy('sort_list_group#asc');
        $total = PermissionGroupModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module . ' overzicht';
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        PermissionModel::joinModule();
        PermissionModel::$sqlOrderBy = ['sort_list' => 'asc', 'order_num' => 'asc'];
        PermissionModel::$sqlWhere = [ModuleModel::$table . ".is_active" => 1];
        $listdbAll = PermissionModel::getAll();
        $listdbPermission = PermissionModel::getTree($listdbAll);

        $arr_result = [];
        $listdb = PermissionGroupModel::getAll();
        foreach ($listdb as $rs) {
            $rs["listdbPermission"]  = $this->getPermission($rs["permission_ids"], $listdbPermission, true);
            $rs["type_name"] = PermissionGroupTypeModel::fetchData($rs[PermissionGroupTypeModel::$primaryKey], 'name');
            $rs["name"] = editInlineButton($this->controller_name . '.editInline', $rs[PermissionGroupModel::$primaryKey], 'name', $rs['name']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[PermissionGroupModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $check_double = PermissionGroupModel::getOneByField('name', $data["name"]);
        if (empty($check_double) === false && $check_double["is_del"] > 0) {
            $data_reset["is_del"] = 0;
            PermissionGroupModel::edit($check_double[PermissionGroupModel::$primaryKey], $data_reset);
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

        $insert_id = PermissionGroupModel::add($data);
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
        $id = CIInput()->post(PermissionGroupModel::$primaryKey);

        $existdb = PermissionGroupModel::getOneByField('name', $data["name"]);
        if (empty($existdb) === false && $id != $existdb[PermissionGroupModel::$primaryKey]) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb = PermissionGroupModel::getOneById($id);
        if (empty($rsdb) === false) {
            PermissionGroupModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function getPostdata(): array
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = PermissionGroupModel::getPostdata();
        return $data;
    }

    private function getPermission(string $permission_ids = "", array $listdb = [], bool $onlyHas = false): array
    {
        $hasIds = explode(',', $permission_ids);
        if (empty($listdb)) {
            PermissionModel::$sqlOrderBy = ['sort_list' => 'asc', 'order_num' => 'asc'];
            PermissionModel::joinModule();
            PermissionModel::$sqlWhere = [ModuleModel::$table . ".is_active" => 1];
            $listdb = PermissionModel::getTree(PermissionModel::getAll());
        }

        $backListView = $this->displayView($listdb, $hasIds, $onlyHas);

        $arr_result = [];
        foreach ($backListView as $rs) {
            $arr_result[$rs["path_description"] . "#_#" . $rs[ModuleModel::$primaryKey]][] = $rs;
        }
        return $arr_result;
    }

    private function displayView(array $listdb = [], array $hasIds = [], bool $onlyHas = false): array
    {
        $arr_result = [];
        foreach ($listdb as $rs) {
            if ($onlyHas === true && in_array($rs[PermissionModel::$primaryKey], $hasIds) === false) {
                continue;
            }
            $labelExtra = $rs["has_link"] > 0 ? ' (' . $rs["link_title"] . ')' : '';
            $label = $rs["description"] . $labelExtra;
            $rs["checkbox_label"] = $label;
            $rs["checkbox_value"] = in_array($rs[PermissionModel::$primaryKey], $hasIds) ? "checked" : "";
            $rs['debug_link'] = $rs["link_dir"] . '/' . $rs["object"] . '/' . $rs["method"];
            if ($rs['use_path'] > 0) {
                $rs['debug_link'] = $rs["path"] . '/' . $rs["link_dir"] . '/' . $rs["object"] . '/' . $rs["method"];
            }

            $rs["toggleId"] = $rs[ModuleModel::$primaryKey];
            $rs["show_child"] = "";

            if (!empty($rs['_child'])) {
                $rs['_child'] =  $this->displayView($rs['_child'], $hasIds);
                $rs["show_child"] = $this->view_layout_return('child', $rs);
            }
            $arr_result[] = $rs;
        }

        return $arr_result;
    }
}
