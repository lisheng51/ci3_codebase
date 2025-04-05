<?php

class Message extends Back_Controller
{

    protected $title_module = 'Bericht';

    #[DisplayAttribute('Gebruiker overzicht', 'Gebruiker overzicht')]
    public function add()
    {
        $data_where[] = [UserModel::$table . '.' . UserModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('emailaddress', UserModel::$table . '.emailaddress');
        $data_where[] = [UserModel::$table . '.' . UserModel::$primaryKey . '!=' => $this->arr_userdb["user_id"]];

        UserModel::$sqlWhere = setSqlWhere($data_where);
        UserModel::$sqlOrderBy = setFieldOrderBy();
        $total = UserModel::getTotal();
        $data["listdb"] = $this->getUsers();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("user_ajax_list", $data);

        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = 'Gebruiker overzicht';
        $this->view_layout("add", $data);
    }

    private function getUsers()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = UserModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["addButton"] = addButton($this->controller_name . '.addFrom', $this->controller_url . "/addFrom/" . $rs["user_id"]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    #[DisplayAttribute('Berichten overzicht', 'Overzicht')]
    public function index()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [MessageModel::$table . ".date >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [MessageModel::$table . ".date <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $is_open = CIInput()->post_get("is_open") ?? '';
        if (empty($is_open) === false) {
            $is_open_value = $is_open == "yes" ? 1 : 0;
            $data_where[] = [MessageModel::$table . '.is_open' => $is_open_value];
        }


        $data_where[] = [MessageModel::$table . '.' . MessageModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('title', MessageModel::$table . '.title');
        $data_where[] = setFieldAndOperator('content', MessageModel::$table . '.content');
        $data_where[] = [MessageModel::$table . '.to_user_id' => $this->arr_userdb["user_id"]];

        MessageModel::$sqlWhere = setSqlWhere($data_where);
        MessageModel::$sqlOrderBy = setFieldOrderBy();

        $total = MessageModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["pagination"] = GlobalModel::showPage($total);
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $data["title"] = $this->title_module . ' overzicht (postvak in)';
        $data["select_open_status"] = MessageModel::selectOpenStatus($is_open);
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = MessageModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["title"] = empty($rs["is_open"]) === true ? "<strong>{$rs["title"]}</strong>" : $rs["title"];
            $rs["from_user_name"] = UserModel::display($rs["from_user_id"]);
            $rs["to_user_name"] = UserModel::display($rs["to_user_id"]);
            $rs["date"] = date_format(date_create($rs["date"]), 'd-m-Y H:i:s');
            $rs["viewButton"] = viewButton($this->controller_name . '.view', $this->controller_url . '/view/' . $rs["message_id"]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    #[DisplayAttribute('Berichten bekijken', 'Bekijken')]
    public function view($id = 0)
    {
        $rsdb = MessageModel::getOneById(intval($id));
        if (empty($rsdb) === true) {
            redirect($this->controller_url);
        }

        $supervisor = UserModel::isSuperUser();
        if ($this->arr_userdb["user_id"] != $rsdb["to_user_id"] && $this->arr_userdb["user_id"] != $rsdb["from_user_id"] && $supervisor === false) {
            showError();
        }

        if (CIInput()->post()) {
            $this->addFrom();
        }

        if ($this->arr_userdb["user_id"] == $rsdb["to_user_id"]) {
            if ($rsdb['open_at'] === null) {
                $dataEdit['open_at'] = date('Y-m-d H:i:s');
                $dataEdit['is_open'] = 1;
                MessageModel::edit($id, $dataEdit);
            }
        }

        $rsdb["from_user_name"] = UserModel::display($rsdb["from_user_id"]);
        $rsdb["to_user_name"] = UserModel::display($rsdb["to_user_id"]);
        $rsdb["date"] = F_datetime::convert_datetime($rsdb["date"]);

        $data["rsdb"] = $rsdb;
        $data["reply_status"] = '';
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        if ($this->arr_userdb["user_id"] == $rsdb["from_user_id"]) {
            $data["reply_status"] = 'invisible';
        }
        $data["title"] = $this->title_module . " informatie";
        $this->view_layout("view", $data);
    }

    #[DisplayAttribute('Berichten versturen', '')]
    public function addFrom($to_user_id = 0)
    {
        if (CIInput()->post()) {
            $this->addFromAction();
        }
        $arr_user = UserModel::getOneById(intval($to_user_id));
        if (empty($arr_user) === true) {
            redirect($this->controller_url);
        }
        $data["title"] = $this->title_module . ' toevoegen';

        $data["to_user_id"] = $to_user_id;
        $data["to_user_name"] = $arr_user["display_info"];
        $this->view_layout("addFrom", $data);
    }

    private function addFromAction()
    {
        $data = $this->get_postdata();
        $messageId = MessageModel::add($data);
        if ($messageId > 0) {
            SendMailModel::messageCopie($messageId, UserModel::getOneById($data["to_user_id"]));
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . " is verzonden!";
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    #[DisplayAttribute('Berichten verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id) === true) {
            redirect($this->controller_url);
        }
        $rsdb = MessageModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . " kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_del"] = 1;
        MessageModel::edit($id, $data);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function get_postdata(): array
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }

        $data = MessageModel::getPostdata();
        return $data;
    }

    #[DisplayAttribute('Mijn berichten', 'Mijn')]
    public function my()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [MessageModel::$table . ".date >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [MessageModel::$table . ".date <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $is_open = CIInput()->post_get("is_open") ?? '';
        if (empty($is_open) === false) {
            $is_open_value = $is_open == "yes" ? 1 : 0;
            $data_where[] = [MessageModel::$table . '.is_open' => $is_open_value];
        }


        $data_where[] = [MessageModel::$table . '.' . MessageModel::$fieldIsDel => 0];
        $data_where[] = setFieldAndOperator('title', MessageModel::$table . '.title');
        $data_where[] = setFieldAndOperator('content', MessageModel::$table . '.content');
        $data_where[] = [MessageModel::$table . '.from_user_id' => $this->arr_userdb["user_id"]];

        MessageModel::$sqlWhere = setSqlWhere($data_where);
        MessageModel::$sqlOrderBy = setFieldOrderBy();

        $total = MessageModel::getTotal();
        $data["listdb"] = $this->getData();
        $data["pagination"] = GlobalModel::showPage($total);
        $data["total"] = $total;
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["addButton"] = addButton($this->controller_name . '.add', $this->controller_url . '/add');
        $data["title"] = $this->title_module . ' overzicht (postvak out)';
        $data["select_open_status"] = MessageModel::selectOpenStatus($is_open);
        $this->view_layout("index", $data);
    }
}
