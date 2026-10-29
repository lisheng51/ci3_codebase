<?php

class Table extends Back_Controller
{
    protected $title_module = 'Casino tafel';

    public function __construct()
    {
        parent::__construct();
        CasinoTableModel::ensureSchema();
    }

    public function index()
    {
        $data_where[] = setFieldAndOperator('name', CasinoTableModel::$table . '.name');
        $data_where[] = setFieldAndOperator('game', CasinoTableModel::$table . '.game');
        CasinoTableModel::$sqlWhere = setSqlWhere($data_where);
        if (CIInput()->post("sql_orderby_field")) {
            CasinoTableModel::$sqlOrderBy = setFieldOrderBy();
        }

        $total = CasinoTableModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . ' overzicht';
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        list($limit, $page) = GlobalModel::limitAndPage();
        $arrResult = [];
        foreach (CasinoTableModel::getList($limit, $page) as $rs) {
            $id = (int)$rs[CasinoTableModel::$primaryKey];
            $state = json_decode((string)$rs['state_json'], true) ?: [];
            $rs["online"] = CasinoTableModel::onlineCount($rs);
            $rs["phase"] = $state['phase'] ?? '-';
            $rs["play_url"] = site_url($this->module_url . '/site/home/table/' . $id);
            $rs["log_url"] = site_url($this->module_url . '/back/log/index?casino_table_id=' . $id);
            $rs["name"] = editInlineButton($this->controller_name . '.editInline', $id, 'name', $rs['name']);
            $rs["is_active"] = editBooleanInlineButton($this->controller_name . '.editInline', $id, 'is_active', (int)$rs['is_active']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $id);
            $arrResult[] = $rs;
        }
        return $arrResult;
    }

    public function add()
    {
        if (CIInput()->post()) {
            $data = $this->getPostdata();
            $insert_id = CasinoTableModel::add($data);
            $json["msg"] = $this->title_module . ' is aangemaakt!';
            $json["status"] = $insert_id > 0 ? "good" : "error";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"] . ' ' . $data['name']);
            exit(json_encode($json));
        }
        $data["rsdb"] = [];
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = null;
        $data["resetUrl"] = '';
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    public function edit($id = 0)
    {
        $rsdb = CasinoTableModel::fetch((int)$id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        if (CIInput()->post()) {
            $data = $this->getPostdata();
            if ($data['game'] !== $rsdb['game'] || (int)$data['solo'] !== (int)($rsdb['solo'] ?? 0)) {
                // another game, or solo switched on/off: give the open bets back and start with an empty state
                CasinoTableModel::reset((int)$id);
                $data['state_json'] = null;
            }
            CasinoTableModel::edit((int)$id, $data);
            $json["msg"] = $this->title_module . " is bijgewerkt!";
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"] . ' ' . $data['name']);
            exit(json_encode($json));
        }

        $data["rsdb"] = $rsdb;
        $data["title"] = $this->title_module . " wijzigen";
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["resetUrl"] = site_url($this->controller_url . '/reset/' . (int)$id);
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    /** Stops the running round: open bets go back to the players. */
    public function reset($id = 0)
    {
        $rsdb = CasinoTableModel::fetch((int)$id);
        if (empty($rsdb)) {
            exit(json_encode(['status' => 'error', 'msg' => $this->title_module . ' is niet gevonden!']));
        }
        try {
            CasinoTableModel::reset((int)$id);
        } catch (CasinoException $e) {
            exit(json_encode(['status' => 'error', 'msg' => $e->getMessage()]));
        }
        CasinoLogModel::write(0, 'refund', 0, 'Tafel gereset door beheerder', (int)$id, $rsdb['game'], (int)$rsdb['round_no'], 0);
        $json["msg"] = $this->title_module . ' is gereset, open inzetten zijn teruggegeven!';
        $json["status"] = "good";
        add_app_log($json["msg"] . ' ' . $rsdb['name']);
        exit(json_encode($json));
    }

    public function del()
    {
        $id = (int)CIInput()->post("del_id");
        $rsdb = CasinoTableModel::fetch($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        CasinoTableModel::reset($id);
        CasinoTableModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"] . ' ' . $rsdb['name']);
        exit(json_encode($json));
    }

    public function editInline()
    {
        $id = (int)(CIInput()->post("editid") ?? 0);
        $rsdb = CasinoTableModel::fetch($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $field = CIInput()->post("field") ?? "";
        if (!in_array($field, ['name', 'is_active', 'order_num'], true)) {
            $json["msg"] = 'Geen veld gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $editType = CIInput()->post("type") ?? "string";
        $fieldvalue = $editType === 'boolean' ? (int)CIInput()->post("fieldvalue") : (string)CIInput()->post("fieldvalue");
        CasinoTableModel::edit($id, [$field => $fieldvalue]);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        return CasinoTableModel::postData();
    }
}
