<?php

class Api extends Back_Controller
{
    use DataFormat;
    protected $title_module = 'API';

    #[DisplayAttribute('API account aanmaken', 'Toevoegen')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["title"] = $this->title_module . " toevoegen";
        $data["rsdb"] = null;
        $data["event_result_box"] = GlobalModel::eventResultBox($data["title"]);
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('API account verwijderen')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id) === true) {
            redirect($this->controller_url);
        }
        $rsdb = ApiModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        ApiModel::del($id);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('API account bewerken')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = ApiModel::getOneById(intval($id));
        if (empty($rsdb) === true) {
            redirect($this->controller_url);
        }

        $data["rsdb"] = $rsdb;
        $data["title"] = $this->title_module . " wijzigen";
        $data["event_result_box"] = GlobalModel::eventResultBox($data["title"]);
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('API account data downloaden', 'Downloaden')]
    public function download(string $type = "json")
    {
        $data_where[] = [ApiModel::$table . '.' . ApiModel::$fieldIsDel => 0];
        ApiModel::$sqlWhere = setSqlWhere($data_where);
        $json = $jsonData = [];
        $listdb = ApiModel::getAll();
        foreach ($listdb as $rs) {
            $jsonData["apiid"] = $rs[ApiModel::$primaryKey];
            $jsonData["apikey"] = $rs["secret"];
            $jsonData["name"] =  $rs["name"];
            $json[] = $jsonData;
        }

        if ($type === 'json') {
            force_download('api.json', $this->asJson($json));
        }
        force_download('api.xml', $this->asXml($json));
    }

    #[DisplayAttribute('API account overzicht', 'Overzicht')]
    public function index()
    {
        $data_where[] = [ApiModel::$table . '.' . ApiModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('secret', ApiModel::$table . '.secret');
        $data_where[] = setFieldAndOperator(PermissionGroupModel::$primaryKey, ApiModel::$table . '.permission_group_ids');
        $data_where[] = setFieldAndOperator(ApiModel::$primaryKey, ApiModel::$table . '.' . ApiModel::$primaryKey);
        $data_where[] = setFieldAndOperator('name', ApiModel::$table . '.name');
        ApiModel::$sqlWhere = setSqlWhere($data_where);
        ApiModel::$sqlOrderBy = setFieldOrderBy();
        $total = ApiModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . ' overzicht';
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $dataBox["body"] = '<a href=' . site_url($this->controller_url . '/download') . ' class="btn btn-info btn-sm ">JSON</a> | <a href=' . site_url($this->controller_url . '/download/xml') . ' class="btn btn-info btn-sm ">XML</a>';
        $data["event_result_box"] = GlobalModel::eventResultBox($dataBox["body"], 'Data downloaden');
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('API account inline bewerken', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = ApiModel::getOneById($id);
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

        if ($field === 'name') {
            $existdb = ApiModel::getOneByField($field, $data[$field]);
            if (empty($existdb) === false && $id != $existdb[ApiModel::$primaryKey]) {
                $json["msg"] = $this->title_module . ' bestaat al!';
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        ApiModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }


    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = ApiModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs['permissionGroupDb'] = PermissionGroupModel::getAllGroup($rs['permission_group_ids'] ?? "");
            $rs["name"] = editInlineButton($this->controller_name . '.editInline', $rs[ApiModel::$primaryKey], 'name', $rs['name']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[ApiModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $check_double = ApiModel::getOneByField('name', $data["name"]);
        if (empty($check_double) === false) {
            $json["msg"] = $this->title_module . " bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $insert_id = ApiModel::add($data);
        if ($insert_id > 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . " is aangemaakt!";
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function editAction()
    {
        $data = $this->getPostdata();
        $id = CIInput()->post(ApiModel::$primaryKey);
        $existdb = ApiModel::getOneByField('name', $data["name"]);
        if (empty($existdb) === false && $id != $existdb[ApiModel::$primaryKey]) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb = ApiModel::getOneById($id);
        if (empty($rsdb) === false) {
            ApiModel::edit($id, $data);
            $json["msg"] = $this->title_module . " is bijgewerkt!";
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
        $data = ApiModel::getPostdata();
        return $data;
    }

    private function myprint_r($my_array)
    {
        $html = "";
        if (is_array($my_array)) {
            foreach ($my_array as $k => $v) {
                $html .= $k . ': ' . $v . "<hr>";
            }
        }
        return $html;
    }

    #[DisplayAttribute('API log overzicht', 'Overzicht')]
    public function log()
    {
        GlobalModel::savePostGet();
        $data_where[] = setFieldAndOperator(ApiModel::$primaryKey, ApiLogModel::$table . '.' . ApiModel::$primaryKey, loadPostGet('api_id', 'array'), false, loadPostGet('api_id_operator'));
        $data_where[] = setFieldAndOperator('msg', ApiLogModel::$table . '.msg', loadPostGet('msg'), false, loadPostGet('msg_operator'));
        $data_where[] = setFieldAndOperator('path', ApiLogModel::$table . '.path', loadPostGet('path'), false, loadPostGet('path_operator'));
        $data_where[] = setFieldAndOperator('ip_address', ApiLogModel::$table . '.ip_address', loadPostGet('ip_address'), false, loadPostGet('ip_address_operator'));
        $data_where[] = setFieldAndOperator('browser', ApiLogModel::$table . '.browser', loadPostGet('browser'), false, loadPostGet('browser_operator'));
        $data_where[] = setFieldAndOperator('platform', ApiLogModel::$table . '.platform', loadPostGet('platform'), false, loadPostGet('platform_operator'));
        $data_where[] = setFieldAndOperator('post_value', ApiLogModel::$table . '.post_value', loadPostGet('post_value'), false, loadPostGet('post_value_operator'));
        $data_where[] = setFieldAndOperator('get_value', ApiLogModel::$table . '.get_value', loadPostGet('get_value'), false, loadPostGet('get_value_operator'));
        $data_where[] = setFieldAndOperator('header_value', ApiLogModel::$table . '.header_value', loadPostGet('header_value'), false, loadPostGet('header_value_operator'));
        $data_where[] = setFieldAndOperator('out_value', ApiLogModel::$table . '.out_value', loadPostGet('out_value'), false, loadPostGet('out_value_operator'));

        $reportrange = CIInput()->post("reportrange") ?? loadPostGet('reportrange');
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [ApiLogModel::$table . ".datetime >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [ApiLogModel::$table . ".datetime <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $reportrange_end = CIInput()->post("reportrange_end") ?? loadPostGet('reportrange_end');
        $reportrange_start = CIInput()->post("reportrange_start") ?? loadPostGet('reportrange_start');
        if (empty($reportrange_start) === false && empty($reportrange_end) === true) {
            $data_where[] = [ApiLogModel::$table . ".datetime >=" => date_format(date_create(trim($reportrange_start)), 'Y-m-d H:i:s')];
        }

        if (empty($reportrange_end) === false && empty($reportrange_start) === true) {
            $data_where[] = [ApiLogModel::$table . ".datetime <=" => date_format(date_create(trim($reportrange_end)), 'Y-m-d H:i:s')];
        }

        if (empty($reportrange_start) === false && empty($reportrange_end) === false) {
            $data_where[] = [ApiLogModel::$table . ".datetime >=" => date_format(date_create(trim($reportrange_start)), 'Y-m-d H:i:s')];
            $data_where[] = [ApiLogModel::$table . ".datetime <=" => date_format(date_create(trim($reportrange_end)), 'Y-m-d H:i:s')];
        }
        ApiLogModel::joinApi();
        ApiLogModel::$sqlWhere = setSqlWhere($data_where);
        ApiLogModel::$sqlOrderBy = setFieldOrderBy();

        $total = ApiLogModel::getTotal();
        $data["listdb"] = $this->log_list();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list_log_new", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module . " log overzicht";
        $this->view_layout("log", $data);
    }

    private function log_list()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;
        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;
        return ApiLogModel::getList($limit, $page);
        //        $listdb = ApiLogModel::get_list($limit, $page);
        //        $arr_result = [];
        //        foreach ($listdb as $rs) {
        //            $rs["header_value"] = $this->myprint_r(json_decode($rs["header_value"], true));
        //            $rs["post_value"] = $this->myprint_r(json_decode($rs["post_value"], true));
        //            $rs["get_value"] = $this->myprint_r(json_decode($rs["get_value"], true));
        //            $arr_result[] = $rs;
        //        }
        //
        //        return $arr_result;
    }
}
