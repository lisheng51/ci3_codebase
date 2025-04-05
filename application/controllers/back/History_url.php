<?php

class History_url extends Back_Controller
{

    protected $title_module = 'Geschiedenis';

    #[DisplayAttribute('Geschiedenis overzicht', 'Overzicht')]
    public function index()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [HistoryUrlModel::$table . ".date >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [HistoryUrlModel::$table . ".date <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $data_where[] = [UserModel::$primaryKey => $this->arr_userdb["user_id"]];
        $data_where[] = setFieldAndOperator('title', HistoryUrlModel::$table . '.title');
        $data_where[] = setFieldAndOperator('path', HistoryUrlModel::$table . '.path');
        HistoryUrlModel::$sqlWhere = setSqlWhere($data_where);
        HistoryUrlModel::$sqlOrderBy = setFieldOrderBy();
        $total = HistoryUrlModel::getTotal();
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
        $listdb = HistoryUrlModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["del_url"] = site_url($this->controller_url . "/del");
            $rs["date"] = date_format(date_create($rs["date"]), 'd-m-Y H:i:s');
            $arr_result[] = $rs;
        }
        return $arr_result;
    }

    #[DisplayAttribute('Geschiedenis batch verwijderen', '')]
    public function batch_del()
    {
        $arr_ids = CIInput()->post("log_id");
        if (empty($arr_ids)) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        foreach ($arr_ids as $id) {
            HistoryUrlModel::del($id);
        }
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        exit(json_encode($json));
    }

    #[DisplayAttribute('Geschiedenis verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        $rsdb = HistoryUrlModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        HistoryUrlModel::del($id);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
