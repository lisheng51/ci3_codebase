<?php

class Visitor extends Back_Controller
{

    protected $title_module = 'Logging - bezoeker';

    #[DisplayAttribute('bezoekers log overzicht', 'Overzicht')]
    public function index()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = ["datetime >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = ["datetime <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $data_where[] = setFieldAndOperator('path', VisitorModel::$table . '.path');
        $data_where[] = setFieldAndOperator('browser', VisitorModel::$table . '.browser');
        $data_where[] = setFieldAndOperator('platform', VisitorModel::$table . '.platform');
        $data_where[] = setFieldAndOperator('ip_address', VisitorModel::$table . '.ip_address');
        $data_where[] = setFieldAndOperator('username', LoginModel::$table . '.username');

        VisitorModel::$sqlSelect =
            [
                [LoginModel::$table => 'username']
            ];

        VisitorModel::$sqlJoin =
            [
                [LoginModel::$table => LoginModel::$primaryKey],
            ];

        VisitorModel::$sqlWhere = setSqlWhere($data_where);
        VisitorModel::$sqlOrderBy = setFieldOrderBy();
        $total = VisitorModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module;
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;
        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;
        return VisitorModel::getList($limit, $page);
    }
}
