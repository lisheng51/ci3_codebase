<?php

class Language_tag extends Back_Controller
{

    protected $title_module = 'Taal tag';

    #[DisplayAttribute('Taal tag toevoegen', 'Aanmaken')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["showTinymce"] = CIInput()->get('mode') ?? '';
        $data["rsdb"] = [];
        $data["title"] = $this->title_module . ' toevoegen';
        $data["delButton"] = '';
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("edit", $data);
    }

    private function syncAction()
    {
        $tables = [CIDb()->dbprefix . LanguageTagModel::$table];
        $domain = CIInput()->post('domain');
        AjaxckModel::value('domein', $domain);
        $domainTodo = rtrim($domain, "/") . '/';
        $url =  $domainTodo . 'api/Database/copie';
        $resultCk = get_curl($url);
        if ($resultCk === null) {
            $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        foreach ($tables as $table) {
            $fields_string = http_build_query(['table' => $table]);
            $stream = fopen($url . '?' . $fields_string, 'r');
            $totalcount = (int) stream_get_contents($stream);
            fclose($stream);
            $curl_url = $url . '/list?' . $fields_string;
            if ($totalcount > 0) {
                $pagecount = 500;
                $this->db->truncate($table);
                for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                    $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                    $result = get_curl($curl_url . '&' . $fields_string);
                    if ($result === null) {
                        continue;
                    }
                    $listdb = json_decode($result, true);
                    $this->db->insert_batch($table, $listdb);
                    unset($listdb);
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

    #[DisplayAttribute('Taal tag synchroniseren', 'synchroniseren')]
    public function sync()
    {
        if (CIInput()->post()) {
            $this->syncAction();
        }
        $data["rsdb"] = [];
        $data["title"] = $this->title_module . ' synchroniseren';
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("sync", $data);
    }

    #[DisplayAttribute('Taal tag verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id)) {
            redirect($this->controller_url);
        }
        $rsdb = LanguageTagModel::getOneById($id);
        if (empty($rsdb)) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        LanguageTagModel::del($id);
        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        $lang = LanguageModel::getOneById($rsdb[LanguageModel::$primaryKey]);
        LanguageTagModel::updateCache($lang['folder']);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Taal tag bewerken', '')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }
        $data["showTinymce"] = CIInput()->get('mode') ?? '';
        $rsdb = LanguageTagModel::getOneById($id);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        $data["rsdb"] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Taal tag inline bewerken', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = LanguageTagModel::getOneById($id);
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
        $data[LanguageModel::$primaryKey] = $rsdb[LanguageModel::$primaryKey];
        if ($field === 'tag') {
            $existdb = LanguageTagModel::getExist($data, $id);
            if (!empty($existdb)) {
                $json["msg"] = $this->title_module . ' bestaat al!';
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        LanguageTagModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);

        $lang = LanguageModel::getOneById($rsdb[LanguageModel::$primaryKey]);
        LanguageTagModel::updateCache($lang['folder']);

        exit(json_encode($json));
    }

    #[DisplayAttribute('Taal tag overzicht', 'Overzicht')]
    public function index()
    {
        LanguageTagModel::joinLanguage();
        $data_where[] = setFieldAndOperator('tag', LanguageTagModel::$table . '.tag');
        $data_where[] = setFieldAndOperator(LanguageModel::$primaryKey, LanguageTagModel::$table . '.' . LanguageModel::$primaryKey);
        LanguageTagModel::$sqlWhere = setSqlWhere($data_where);
        LanguageTagModel::$sqlOrderBy = setFieldOrderBy();
        $total = LanguageTagModel::getTotal();
        $data["listdb"] = $this->getData();

        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["event_result_box"] = '';
        $data["title"] = $this->title_module . ' overzicht';
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = LanguageTagModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["tag"] = editInlineButton($this->controller_name . '.editInline', $rs[LanguageTagModel::$primaryKey], 'tag', $rs['tag']);
            $rs["value"] = editInlineButton($this->controller_name . '.editInline', $rs[LanguageTagModel::$primaryKey], 'value', $rs['value']);
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[LanguageTagModel::$primaryKey]);
            $rs["editButtonTinymce"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs[LanguageTagModel::$primaryKey] . '?mode=tinymce', '<i class="fa-fw fa-solid fa-marker"></i>');
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    #[DisplayAttribute('Alle taal tag inline bewerken', '')]
    public function view(int $language_id = 1)
    {
        if (CIInput()->post()) {
            $this->viewAction($language_id);
        }

        $listdb = LanguageTagModel::getAllByField(LanguageModel::$primaryKey, $language_id);
        if (empty($listdb)) {
            redirect($this->controller_url);
        }
        $rsdb = LanguageModel::getOneById($language_id);
        $data["listdb"] = $listdb;
        $data["title"] = $this->title_module . ' wijzigen - ' . $rsdb['name'];
        $data["event_result_box"] = "";
        $data["breadcrumbData"] = [
            [
                "name" => 'Overzicht',
                "url" => $this->controller_url
            ],
            [
                "name" => $data["title"]
            ]
        ];
        $this->view_layout("view", $data);
    }

    private function viewAction(int $language_id = 1)
    {
        $ids = CIInput()->post('view');
        foreach ($ids as $id => $value) {
            $data['value'] = $value;
            LanguageTagModel::edit($id, $data);
        }
        $json["status"] = "good";
        $json['msg'] = $this->title_module . ' is bijgewerkt';
        $lang = LanguageModel::getOneById($language_id);
        LanguageTagModel::updateCache($lang['folder']);
        exit(json_encode($json));
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        $existdb = LanguageTagModel::getExist($data);

        if (!empty($existdb)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $insert_id = LanguageTagModel::add($data);
        if ($insert_id > 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            $lang = LanguageModel::getOneById($data[LanguageModel::$primaryKey]);
            LanguageTagModel::updateCache($lang['folder']);
            exit(json_encode($json));
        }
    }

    private function editAction()
    {
        $data = $this->getPostdata();
        $id = CIInput()->post(LanguageTagModel::$primaryKey);
        $existdb = LanguageTagModel::getExist($data, $id);
        if (!empty($existdb)) {
            $json["msg"] = $this->title_module . ' bestaat al!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $rsdb = LanguageTagModel::getOneById($id);
        if (!empty($rsdb)) {
            LanguageTagModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            $lang = LanguageModel::getOneById($data[LanguageModel::$primaryKey]);
            LanguageTagModel::updateCache($lang['folder']);
            exit(json_encode($json));
        }
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = LanguageTagModel::getPostdata();
        return $data;
    }
}
