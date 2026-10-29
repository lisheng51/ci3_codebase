<?php

class Log extends Back_Controller
{
    protected $title_module = 'Casino log';

    public function index()
    {
        $this->setFilter();
        if (CIInput()->post("sql_orderby_field")) {
            CasinoLogModel::$sqlOrderBy = setFieldOrderBy();
        }

        $total = CasinoLogModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        CasinoTableModel::sqlReset();
        CasinoTableModel::$sqlOrderBy = ["order_num" => 'asc', "casino_table_id" => 'asc'];
        $data["tables"] = CasinoTableModel::getAll();
        $data["title"] = $this->title_module;
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function setFilter()
    {
        $log = CasinoLogModel::$table;
        $data_where[] = setFieldAndOperator('casino_user_id', $log . '.casino_user_id');
        $data_where[] = setFieldAndOperator('casino_table_id', $log . '.casino_table_id');
        $data_where[] = setFieldAndOperator('game', $log . '.game');
        $data_where[] = setFieldAndOperator('action', $log . '.action');
        $data_where[] = setFieldAndOperator('round_no', $log . '.round_no');
        $data_where[] = setFieldAndOperator('email', CasinoUserModel::$table . '.email');
        $where = setSqlWhere($data_where);

        $from = (string)(CIInput()->post_get('date_from') ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[CIDb()->dbprefix($log) . '.created_at >='] = $from . ' 00:00:00';
        }
        $to = (string)(CIInput()->post_get('date_to') ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[CIDb()->dbprefix($log) . '.created_at <='] = $to . ' 23:59:59';
        }

        CasinoLogModel::$sqlWhere = $where;
        CasinoLogModel::$sqlSelect = [[CasinoUserModel::$table => 'email'], [CasinoUserModel::$table => 'name']];
        CasinoLogModel::$sqlJoin = [[CasinoUserModel::$table => CasinoUserModel::$table . '.casino_user_id = ' . $log . '.casino_user_id']];
        CasinoLogModel::$joinLeftTables = [CasinoUserModel::$table];
    }

    private function getData()
    {
        list($limit, $page) = GlobalModel::limitAndPage();
        $tables = [];
        $arrResult = [];
        foreach (CasinoLogModel::getList($limit, $page) as $rs) {
            $tableId = (int)$rs['casino_table_id'];
            if ($tableId > 0 && !isset($tables[$tableId])) {
                $tables[$tableId] = CasinoTableModel::fetch($tableId)['name'] ?? ('#' . $tableId);
            }
            $rs['table_name'] = $tableId > 0 ? $tables[$tableId] : '';
            $rs['player_url'] = (int)$rs['casino_user_id'] > 0 ? site_url($this->module_url . '/back/player/edit/' . (int)$rs['casino_user_id']) : '';
            $arrResult[] = $rs;
        }
        return $arrResult;
    }
}
