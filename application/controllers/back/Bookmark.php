<?php

class Bookmark extends Back_Controller
{

    protected $title_module = 'Bookmark';

    #[DisplayAttribute('Bookmark verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id") ?? 0;
        $rsdb = BookMarkModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . " is niet gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data_where["parent_bookmark_id"] = $rsdb[BookMarkModel::$primaryKey];
        $data_where[BookMarkModel::$fieldIsDel] = 0;
        BookMarkModel::$sqlWhere = $data_where;
        $children = BookMarkModel::getOne();
        if (empty($children) === false) {
            $json["msg"] = "Er zijn nog onderliggende " . $this->title_module . " aanwezig. Deze dienen eerst verwijderd te worden.";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        BookMarkModel::del($id);
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        $json["name_text_color"] = "<font color=red>" . $rsdb["name"] . "</font>";

        $button_change["name"] = $rsdb["name"];
        $button_change[BookMarkModel::$primaryKey] = $rsdb[BookMarkModel::$primaryKey];
        $button_change["link"] = site_url($this->controller_url . "/back");

        $json["change_button"] = $this->back_btn($button_change);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Bookmark terugzetten', '')]
    public function back()
    {
        $id = CIInput()->post("del_id") ?? 0;
        $rsdb = BookMarkModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . " is niet gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if ($rsdb["parent_bookmark_id"] > 0) {
            $parent_request = BookMarkModel::getOneById($rsdb["parent_bookmark_id"]);
            if ($parent_request[BookMarkModel::$fieldIsDel] > 0) {
                $json["msg"] = $this->title_module . " kon niet worden hersteld! Deze staat gemarkeerd voor verwijdering.";
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        $data_reset[BookMarkModel::$fieldIsDel] = 0;
        BookMarkModel::edit($rsdb[BookMarkModel::$primaryKey], $data_reset);

        $button_change["name"] = $rsdb["name"];
        $button_change[BookMarkModel::$primaryKey] = $rsdb[BookMarkModel::$primaryKey];
        $button_change["link"] = site_url($this->controller_url . "/del");
        $json["change_button"] =  $this->remove_btn($button_change);
        $json["msg"] = $this->title_module . " is teruggezet!";
        $json["status"] = "good";
        $json["name_text_color"] = $rsdb["name"];
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Bookmark sorteren', '')]
    public function sortList()
    {
        $order_num = CIInput()->post('sort_list');
        foreach ($order_num as $id => $value) {
            $data['order_list'] = $value;
            BookMarkModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = 'Volgorde is verwerkt!';
        exit(json_encode($json));
    }

    private function showChild($f_question_id = 0, array $listdb = [])
    {
        $arr_result = [];
        foreach ($listdb as $rs) {
            $button_change["name"] = $rs["name"];
            $button_change["link"] = site_url($this->controller_url . "/del");
            $button_change[BookMarkModel::$primaryKey] = $rs[BookMarkModel::$primaryKey];
            $rs["change_button"] = $this->remove_btn($button_change);

            if ($rs[BookMarkModel::$fieldIsDel] > 0) {
                $rs["name"] = "<font color=red>" . $rs["name"] . "</font>";
                $button_change["link"] = site_url($this->controller_url . "/back");
                $rs["change_button"] = $this->back_btn($button_change);
            }

            if ($rs['parent_bookmark_id'] == $f_question_id) {
                $rs["add_child_link"] = null;
                $rs["edit_link"] = site_url($this->controller_url . "/edit");
                $rs["url"] = $rs["is_extern"] > 0 ? $rs["path"] : site_url($rs["path"]);
                if ($rs["is_sort"] > 0 && $rs['parent_bookmark_id'] == 0) {
                    $button_add["link"] = site_url($this->controller_url . "/add");
                    $button_add["parent_bookmark_id"] = $rs[BookMarkModel::$primaryKey];
                    $button_add["name"] = $rs["name"];
                    $rs["add_child_link"] = $this->add_child_btn($button_add);
                }
                $arr_result[] = $rs;
            }
        }
        return $arr_result;
    }

    private function getAll()
    {
        $arr_result = [];
        $listdb = BookMarkModel::getAll();
        foreach ($listdb as $rs) {
            $button_change["name"] = $rs["name"];
            $button_change["link"] = site_url($this->controller_url . "/del");
            $button_change[BookMarkModel::$primaryKey] = $rs[BookMarkModel::$primaryKey];
            $rs["change_button"] = $this->remove_btn($button_change);

            if ($rs[BookMarkModel::$fieldIsDel] > 0) {
                $rs["name"] = "<font color=red>" . $rs["name"] . "</font>";
                $button_change["link"] = site_url($this->controller_url . "/back");
                $rs["change_button"] = $this->back_btn($button_change);
            }

            if ($rs['parent_bookmark_id'] == 0) {
                $rs["add_child_link"] = null;
                $rs["show_child"] = null;
                $rs["edit_link"] = site_url($this->controller_url . "/edit");
                if ($rs["is_sort"] > 0) {
                    $button_add["link"] = site_url($this->controller_url . "/add");
                    $button_add["parent_bookmark_id"] = $rs[BookMarkModel::$primaryKey];
                    $button_add["name"] = $rs["name"];
                    $rs["add_child_link"] = $this->add_child_btn($button_add);
                    $data["listdb"] = $this->showChild($rs[BookMarkModel::$primaryKey], $listdb);
                    $rs["show_child"] = $this->view_layout_return('child', $data);
                }

                $arr_result[] = $rs;
            }
        }

        return $arr_result;
    }

    #[DisplayAttribute('Bookmark herstellen', '')]
    public function reset()
    {
        $id = $this->arr_userdb[UserModel::$primaryKey] ?? 0;
        $status = BookMarkModel::resetAllByPermission($id);
        if ($status === true) {
            redirect($this->controller_url);
        }
    }

    #[DisplayAttribute('Bookmark beheer voor iemand', '')]
    public function indexAdmin(int $userId = 0)
    {
        if ($userId <= 0) {
            redirect($this->controller_url);
        }
        $view_mode = CIInput()->post("view_mode");
        $data_where[BookMarkModel::$table . '.' . UserModel::$primaryKey] = $userId;
        $data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel] = 0;

        if (empty($view_mode) === false && $view_mode == "no_del") {
            $data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel] = 0;
        }

        if (empty($view_mode) === false && $view_mode == "all") {
            unset($data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel]);
        }

        BookMarkModel::$sqlWhere = $data_where;
        $data["listdb"] = $this->getAll();
        $data["ckk"] = $view_mode === "all" ? 'checked' : '';
        $data["add_link"] = site_url($this->controller_url . "/add");
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . " overzicht";
        $data['event_result_box'] = "";
        $data['user_id'] = $userId;
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('Bookmark overzicht', 'Overzicht')]
    public function index()
    {
        $userId = LoginModel::userId();
        $view_mode = CIInput()->post("view_mode");
        $data_where[BookMarkModel::$table . '.' . UserModel::$primaryKey] = $userId;
        $data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel] = 0;

        if (empty($view_mode) === false && $view_mode == "no_del") {
            $data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel] = 0;
        }

        if (empty($view_mode) === false && $view_mode == "all") {
            unset($data_where[BookMarkModel::$table . '.' . BookMarkModel::$fieldIsDel]);
        }

        BookMarkModel::$sqlWhere = $data_where;
        $data["listdb"] = $this->getAll();
        $data["ckk"] = $view_mode === "all" ? 'checked' : '';
        $data["add_link"] = site_url($this->controller_url . "/add");
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . " overzicht";
        $data['event_result_box'] = "";
        $data[UserModel::$primaryKey] = $userId;
        $data["breadcrumbData"] = [
            [
                "name" => 'Herstellen',
                "url" => $this->controller_url . "/reset",
            ],
            [
                "name" => 'Importeren',
                "url" => $this->controller_url . "/import",
            ],
            [
                "name" => 'Exporteren',
                "url" => $this->controller_url . "/export",
            ]
        ];
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('Bookmark aanmaken', '')]
    public function add()
    {
        $data = BookMarkModel::getPostdata();

        $data_where[] = [BookMarkModel::$table . '.path' => $data["path"]];
        $data_where[] = [BookMarkModel::$table . '.' . UserModel::$primaryKey => $data[UserModel::$primaryKey]];

        if ($data["is_sort"] > 0) {
            $data_where[] = [BookMarkModel::$table . '.name' => $data["name"]];
        }

        BookMarkModel::$sqlWhere = setSqlWhere($data_where);
        $check_double = BookMarkModel::getOne();

        if (empty($check_double) === false && $check_double[BookMarkModel::$fieldIsDel] > 0) {
            $data_reset[BookMarkModel::$fieldIsDel] = 0;
            BookMarkModel::edit($check_double[BookMarkModel::$primaryKey], $data_reset);
            $json["ajax_edit_result"] = $this->get_one_tr($check_double[BookMarkModel::$primaryKey]);
            $json["msg"] = $this->title_module . ' is aangemaakt!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }

        if (empty($check_double) === false) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = BookMarkModel::add($data);
        if ($insert_id > 0) {
            $json["ajax_edit_result"] = $this->get_one_tr($insert_id);
            $json["msg"] = $this->title_module . " is aangemaakt!";
            $json["status"] = "good";
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    #[DisplayAttribute('Bookmark bewerken', '')]
    public function edit()
    {
        $data = BookMarkModel::getPostdata();
        $id = CIInput()->post(BookMarkModel::$primaryKey);
        $data_where[] = [BookMarkModel::$table . '.path' => $data["path"]];
        $data_where[] = [BookMarkModel::$table . '.' . UserModel::$primaryKey => $data[UserModel::$primaryKey]];

        if ($data["is_sort"] > 0) {
            $data_where[] = [BookMarkModel::$table . '.name' => $data["name"]];
        }

        BookMarkModel::$sqlWhere = setSqlWhere($data_where);
        $existdb = BookMarkModel::getOne();

        if (empty($existdb) === false && $id != $existdb[BookMarkModel::$primaryKey]) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }


        $rsdb = BookMarkModel::getOneById($id);
        if (empty($rsdb) === false) {
            BookMarkModel::edit($id, $data);
            $json["msg"] = $this->title_module . " is bijgewerkt!";
            $json["status"] = "good";
            $json["ajax_edit_result"] = $this->get_one_tr($id);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    #[DisplayAttribute('Bookmark getone', '')]
    public function getOne(int $id = 0)
    {
        $rsdb = BookMarkModel::getOneById($id);

        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . " is niet gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $json["is_extern"] = select_boolean('is_extern', intval($rsdb["is_extern"] ?? 0));
        $json["open_new"] = select_boolean('open_new', intval($rsdb["open_new"] ?? 0));
        $json["select_sort"] = BookMarkModel::selectParent($rsdb["parent_bookmark_id"] ?? 0, $rsdb[UserModel::$primaryKey]);
        $json["rsdb"] = $rsdb;
        exit(json_encode($json));
    }

    private function get_one_tr($id = 0)
    {
        $rs = BookMarkModel::getOneById($id);
        $data["order_list"] = $rs["order_list"];
        $data[BookMarkModel::$primaryKey] = $rs[BookMarkModel::$primaryKey];
        $data["tr_class"] = "parent";
        $data["parent_bookmark_id"] = $rs["parent_bookmark_id"];
        if ($rs["parent_bookmark_id"] > 0) {
            $data["tr_class"] = "treegrid-parent-{$rs["parent_bookmark_id"]} child";
        }
        $data["content_td"] = $this->get_one_td($rs);
        return $this->view_layout_return('ajax_one_tr', $data);
    }


    private function get_one_td($rs = [])
    {
        $rs["is_extern"] = $rs["is_extern"] > 0 ? "Ja" : "Nee";
        $rs["open_new"] = $rs["open_new"] > 0 ? "Ja" : "Nee";
        $rs["add_child_link"] = null;
        $rs["url"] = $rs["is_extern"] > 0 ? $rs["path"] : site_url($rs["path"]);
        $rs["add_view_link"] = '<a class="btn btn-success btn-sm" href=' . $rs["url"] . '>' . lang('view_icon') . '</a>';
        if ($rs["is_sort"] > 0 && $rs["parent_bookmark_id"] <= 0) {
            $button_add["link"] = site_url($this->controller_url . "/add");
            $button_add["parent_bookmark_id"] = $rs[BookMarkModel::$primaryKey];
            $button_add["name"] = $rs["name"];
            $rs["add_child_link"] = $this->add_child_btn($button_add);
            $rs["add_view_link"] = null;
        }

        $button_change["name"] = $rs["name"];
        $button_change["link"] = site_url($this->controller_url . "/del");
        $button_change[BookMarkModel::$primaryKey] = $rs[BookMarkModel::$primaryKey];
        $rs["change_button"] = $this->remove_btn($button_change);

        if ($rs[BookMarkModel::$fieldIsDel] == 1) {
            $rs["name"] = "<font color=red>" . $rs["name"] . "</font>";
            $button_change["link"] = site_url($this->controller_url . "/back");
            $rs["change_button"] = $this->back_btn($button_change);
        }

        $rs["edit_link"] = site_url($this->controller_url . "/edit");

        return $this->view_layout_return('ajax_one_td', $rs);
    }

    private function remove_btn(array $button_change = [])
    {
        return '<button id="' . $button_change[BookMarkModel::$primaryKey] . '_removebutton" type="button" data-remove_content_info ="' . $button_change["name"] . '" class="btn btn-danger btn-sm" data-search_data ="' . $button_change[BookMarkModel::$primaryKey] . '" data-del_link="' . $button_change["link"] . '" data-toggle="modal" data-target="#Modal_delete_question" ><i class="fa-fw fas fa-times"></i></button>';
    }

    private function back_btn(array $button_change = [])
    {
        return '<button id="' . $button_change[BookMarkModel::$primaryKey] . '_backbutton" type="button" data-remove_content_info ="' . $button_change["name"] . '" class="btn btn-success btn-sm" data-search_data ="' . $button_change[BookMarkModel::$primaryKey] . '" data-del_link="' . $button_change["link"] . '" data-toggle="modal" data-target="#Modal_back_question" ><i class="fa-fw fas fa-recycle"></i></button>';
    }

    private function add_child_btn(array $button_add = [])
    {
        return '<button type="button" class="btn btn-success btn-sm" data-search_name ="' . $button_add["name"] . '" data-search_data ="' . $button_add["parent_bookmark_id"] . '" data-add_child_link="' . $button_add["link"] . '" data-toggle="modal" data-target="#Modal_question_add_child" ><i class="fa-fw fa fa-plus"></i></button>';
    }

    #[DisplayAttribute('Bookmark exporteren', '')]
    public function export(int $userId = 0)
    {
        $listdb = BookMarkModel::navBarData($userId);
        exit(json_encode($listdb));
    }

    #[DisplayAttribute('Bookmark importeren', '')]
    public function import()
    {
        if (CIInput()->post()) {
            $this->importAction();
        }

        $data["title"] = $this->title_module . ' importeren';
        $data["event_result_box"] = "";
        $this->view_layout("import", $data);
    }

    private function importAction()
    {
        $content = CIInput()->post('content');
        if (empty($content) === true || isJSON($content) === false) {
            $json["msg"] = "Geen content gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $listdb = json_decode($content, true);

        if ($listdb === null || empty($listdb) === true) {
            $json["msg"] = "Data is niet juist!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $checkData = end($listdb);
        if (!isset($checkData['name']) || empty($checkData['name']) === true) {
            $json["msg"] = "Data is niet juist!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $userId = $this->arr_userdb[UserModel::$primaryKey];
        $this->db->from(BookMarkModel::$table)->where(UserModel::$primaryKey, $userId)->delete();

        foreach ($listdb as $rsdb) {
            $data["name"] = $rsdb["name"];
            $data["is_sort"] = 1;
            $data["icon"] = $rsdb["icon"];
            $data[UserModel::$primaryKey] = $userId;
            $data["description"] = "import";
            $data["parent_bookmark_id"] = 0;
            $data["is_extern"] = 0;
            $data["open_new"] = 0;
            $data["order_list"] = $rsdb["order_list"];
            $parentId =  BookMarkModel::add($data);
            if ($parentId > 0 && empty($rsdb["navBarDataChild"]) === false) {
                foreach ($rsdb["navBarDataChild"] as $child) {
                    $data2["name"] = $child["name"];
                    $data2["is_sort"] = 0;
                    $data["icon"] = $child["icon"];
                    $data2[UserModel::$primaryKey] =  $userId;
                    $data2["description"] = "import";
                    $data2["parent_bookmark_id"] = $parentId;
                    $data2["is_extern"] = $child["is_extern"];
                    $data2["open_new"] = $child["open_new"];
                    $data2["order_list"] = $child["order_list"];
                    $data2["path"] = $child["path"];
                    BookMarkModel::add($data2);
                }
            }
        }

        $json["msg"] = $this->title_module . ' is bijgewerkt!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }
}
