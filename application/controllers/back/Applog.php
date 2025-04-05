<?php

class Applog extends Back_Controller
{

    protected $title_module = 'Logging - systeem';

    public function index_2()
    {
        $data["title"] = $this->title_module . " overzicht";
        $this->view_layout("index_2", $data);
    }

    #[DisplayAttribute('Widget laatste logging', 'Logging')]
    public function widget_last()
    {
        $limit = CIInput()->get("limit") ?? 20;
        AppLogModel::$sqlSelect =
            [
                [UserModel::$table => 'emailaddress']
            ];
        AppLogModel::$sqlJoin =
            [
                [UserModel::$table => UserModel::$primaryKey]
            ];

        AppLogModel::$joinLeftTables = [UserModel::$table];
        $data["listdb"] = AppLogModel::getList($limit);
        $data["title"] = 'Laatste logs';
        $content = $this->view_layout_return("widget/last", $data);
        exit($content);
    }

    #[DisplayAttribute('Logging overzicht', 'Overzicht')]
    public function index()
    {
        AppLogModel::$sqlSelect =
            [
                [UserModel::$table => 'emailaddress']
            ];
        AppLogModel::$sqlJoin =
            [
                [UserModel::$table => UserModel::$primaryKey]
            ];

        AppLogModel::$joinLeftTables = [UserModel::$table];
        $reportrange = CIInput()->post("reportrange");
        if (!empty($reportrange)) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [AppLogModel::$table . ".date >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [AppLogModel::$table . ".date <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $data_where[] = setFieldAndOperator('display_info', UserModel::$table . '.display_info');
        $data_where[] = setFieldAndOperator('emailaddress', UserModel::$table . '.emailaddress');
        $data_where[] = setFieldAndOperator('description', AppLogModel::$table . '.description');
        $data_where[] = setFieldAndOperator('path', AppLogModel::$table . '.path');

        AppLogModel::$sqlWhere = setSqlWhere($data_where);
        AppLogModel::$sqlOrderBy = setFieldOrderBy();

        $total = AppLogModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["ajax_batch_del_url"] = $this->controller_url . "/batch_del";
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . " overzicht";
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = AppLogModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["del_url"] = site_url($this->controller_url . "/del");
            $rs["view_url"] = site_url($this->controller_url . "/view/" . $rs[AppLogModel::$primaryKey]);
            $arr_result[] = $rs;
        }
        return $arr_result;
    }

    #[DisplayAttribute('Logging detail', 'Bekijken')]
    public function view($id = 0)
    {
        $rsdb = AppLogModel::getOneById(intval($id));
        if (empty($rsdb) === true) {
            $json["msg"] = "Geen details gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb["date"] = F_datetime::convert_datetime($rsdb["date"]);
        $rsdb_user = UserModel::getOneById($rsdb["user_id"]);
        $arr_result = array_merge($rsdb, $rsdb_user);

        exit(json_encode($arr_result));
    }

    #[DisplayAttribute('Logging batch verwijderen', '')]
    public function batch_del()
    {
        $arr_ids = CIInput()->post("log_id");
        if (empty($arr_ids)) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        foreach ($arr_ids as $id) {
            AppLogModel::del($id);
        }
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        exit(json_encode($json));
    }

    #[DisplayAttribute('Logging verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        $rsdb = AppLogModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        AppLogModel::del($id);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
