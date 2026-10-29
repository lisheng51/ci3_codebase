<?php

class Player extends Back_Controller
{
    protected $title_module = 'Casino speler';

    public function index()
    {
        $data_where[] = setFieldAndOperator('name', CasinoUserModel::$table . '.name');
        $data_where[] = setFieldAndOperator('email', CasinoUserModel::$table . '.email');
        CasinoUserModel::$sqlWhere = setSqlWhere($data_where);
        if (CIInput()->post("sql_orderby_field")) {
            CasinoUserModel::$sqlOrderBy = setFieldOrderBy();
        }

        $total = CasinoUserModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . ' overzicht';
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        list($limit, $page) = GlobalModel::limitAndPage();
        $arrResult = [];
        foreach (CasinoUserModel::getList($limit, $page) as $rs) {
            $id = (int)$rs[CasinoUserModel::$primaryKey];
            $rs["is_active"] = editBooleanInlineButton($this->controller_name . '.editInline', $id, 'is_active', (int)$rs['is_active']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $id);
            $rs["log_url"] = site_url($this->module_url . '/back/log/index?casino_user_id=' . $id);
            $arrResult[] = $rs;
        }
        return $arrResult;
    }

    /** Player details and changing the chips (with a reason, written to the casino log). */
    public function edit($id = 0)
    {
        $rsdb = CasinoUserModel::find((int)$id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        if (CIInput()->post()) {
            $this->editAction($rsdb);
        }

        $data["rsdb"] = $rsdb;
        $data["logUrl"] = site_url($this->module_url . '/back/log/index?casino_user_id=' . (int)$id);
        $data["title"] = $this->title_module . " wijzigen";
        $data["event_result_box"] = "";
        $this->view_layout("edit", $data);
    }

    private function editAction(array $rsdb)
    {
        $id = (int)$rsdb[CasinoUserModel::$primaryKey];
        $delta = (int)CIInput()->post('delta');
        $reason = trim((string)CIInput()->post('reason'));
        $isActive = (int)(CIInput()->post('is_active') ?? 1) > 0 ? 1 : 0;

        if ($delta !== 0) {
            AjaxckModel::value('reden', $reason);
            $real = CasinoUserModel::adjust($id, $delta);
            CasinoLogModel::write($id, 'admin', $real, $reason);
        }
        if ($isActive !== (int)$rsdb['is_active']) {
            CasinoUserModel::edit($id, ['is_active' => $isActive]);
            CasinoLogModel::write($id, 'admin', 0, $isActive ? 'Gedeblokkeerd' : 'Geblokkeerd');
        }

        $json["msg"] = $this->title_module . ' is bijgewerkt!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url . '/edit/' . $id);
        add_app_log($json["msg"] . ' ' . $rsdb['email'] . ($delta !== 0 ? ' fiches ' . sprintf('%+d', $delta) . ': ' . $reason : ''));
        exit(json_encode($json));
    }

    public function editInline()
    {
        $id = (int)(CIInput()->post("editid") ?? 0);
        $rsdb = CasinoUserModel::find($id);
        if (empty($rsdb) || CIInput()->post("field") !== 'is_active') {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $value = (int)CIInput()->post("fieldvalue") > 0 ? 1 : 0;
        CasinoUserModel::edit($id, ['is_active' => $value]);
        CasinoLogModel::write($id, 'admin', 0, $value ? 'Gedeblokkeerd' : 'Geblokkeerd');
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"] . ' ' . $rsdb['email']);
        exit(json_encode($json));
    }
}
