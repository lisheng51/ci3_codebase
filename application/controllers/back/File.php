<?php

class File extends Back_Controller
{

    protected $title_module = 'Bestand';

    #[DisplayAttribute('Bestand uploaden demo', 'Upload', 13, 'add')]
    public function demo()
    {
        if (CIInput()->post()) {
            $uploadData = [];
            $filesCount = count($_FILES['userFiles']['name']);
            for ($i = 0; $i < $filesCount; $i++) {
                $name = $_FILES['userFiles']['name'][$i];
                $tmp_name = $_FILES['userFiles']['tmp_name'][$i];
                $dataFile['decode_string'] = base64_encode(file_get_contents($tmp_name));
                $dataFile['file_name'] = $name;
                $uploadData[] = $dataFile;
            }
            dump($uploadData);

            foreach ($uploadData as $data) {
                $decode_string = $data['decode_string'];
                file_put_contents(UploadModel::$rootFolder . DIRECTORY_SEPARATOR . $data['file_name'], base64_decode($decode_string));
            }
        }
        $data["title"] = $this->title_module . ' toevoegen';
        $this->view_layout("demo", $data);
    }

    #[DisplayAttribute('Bestand uploaden', 'Upload', 10, 'index')]
    public function add()
    {
        if (isset($_FILES['userFiles']) && CIInput()->post()) {
            $this->addAction($_FILES['userFiles']);
        }

        $data["rsdb"] = null;
        $data["title_status"] = 'd-none';
        $data["input_file"] = '<input type="file" class="form-control" name="userFiles[]" required multiple/>';
        $data["delButton"] = null;
        $data["title"] = $this->title_module . ' toevoegen';
        $data["select_type"] = UploadTypeModel::select();
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('Bestand verwijderen', '', 12, 'add')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        if (empty($id)) {
            redirect($this->controller_url);
        }
        $rsdb = UploadModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $path_name = UploadTypeModel::fetchData($rsdb[UploadTypeModel::$primaryKey], 'path');
        $file_path = UploadTypeModel::showDir($path_name) . DIRECTORY_SEPARATOR . $rsdb['file_name'];
        if (unlink($file_path)) {
            UploadModel::del($id);
            $json["msg"] = $this->title_module . ' is verwijderd!';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    #[DisplayAttribute('Bestand bewerken', '', 11, 'add')]
    public function edit($id)
    {
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = UploadModel::getOneById($id);
        if (empty($rsdb) === true) {
            redirect($this->controller_url);
        }

        $data["rsdb"] = $rsdb;
        $path_name = UploadTypeModel::fetchData($rsdb[UploadTypeModel::$primaryKey], 'path');
        $data["title_status"] = null;
        $path = base_url(UploadTypeModel::showDir($path_name) . DIRECTORY_SEPARATOR . $data["rsdb"]['file_name']);
        $showPath = str_replace('\\', '/', $path);
        $data["input_file"] = '<p>' . $showPath . '</p>';
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["title"] = $this->title_module . ' wijzigen';
        $data["select_type"] = UploadTypeModel::select($data["rsdb"]["type_id"]);
        $this->view_layout("edit", $data);
    }

    #[DisplayAttribute('make_dir', 'make_dir', 16, 'Config.index')]
    public function make_dir()
    {
        UploadTypeModel::makeDir($this->arr_userdb["user_id"]);
        redirect($this->controller_url);
    }

    #[DisplayAttribute('Bestand overzicht', 'Overzicht', 9, 'make_dir')]
    public function index()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = [UploadModel::$table . ".created_at >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = [UploadModel::$table . ".created_at <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $data_where[] = setFieldAndOperator('title', UploadModel::$table . '.title');
        $data_where[] = [UploadModel::$table . '.createdby' => $this->arr_userdb["user_id"]];
        $data_where[] = setFieldAndOperator(UploadTypeModel::$primaryKey, UploadModel::$table . '.' . UploadTypeModel::$primaryKey);
        UploadModel::$sqlWhere = setSqlWhere($data_where);
        UploadModel::$sqlOrderBy = setFieldOrderBy();

        $total = UploadModel::getTotal();
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
        $this->view_layout("index", $data);
    }

    private function getData()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = UploadModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . "/edit/" . $rs["upload_id"]);
            $rs["type_name"] = UploadTypeModel::fetchData($rs[UploadTypeModel::$primaryKey], 'name');
            $rs["created_at"] = date_format(date_create($rs["created_at"]), 'd-m-Y H:i:s');
            $rs["path"] = base_url(UploadTypeModel::showDir(UploadTypeModel::fetchData($rs[UploadTypeModel::$primaryKey], 'path')) . DIRECTORY_SEPARATOR . $rs['file_name']);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = UploadModel::getPostdata();
        return $data;
    }

    private function addAction(array $postfiles = [])
    {
        $type_id = CIInput()->post(UploadTypeModel::$primaryKey);

        $path_name = UploadTypeModel::fetchData($type_id, 'path');
        $allowed_types = UploadTypeModel::fetchData($type_id, 'allowed_types');
        $allowedTypes = explode('|', $allowed_types);

        if (empty($path_name)) {
            $json["msg"] = "Geen type gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $uploadPath = UploadTypeModel::showDir($path_name);
        $listdb = reArrayFiles($postfiles);
        if (empty($listdb)) {
            $json["msg"] = "Geen bestand gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        foreach ($listdb as $rs) {
            if ($rs['error'] == 0) {
                $filename = $rs['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), $allowedTypes)) {
                    $title =  url_title($type_id . '_' . $filename);
                    $checkdouble = UploadModel::getOneByField('title', $title);
                    if (empty($checkdouble)) {
                        $file_ext = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                        if (move_uploaded_file($rs['tmp_name'], $file_ext)) {
                            $data['file_name'] = $filename;
                            $data[UploadTypeModel::$primaryKey] = $type_id;
                            $data['title'] = $title;
                            UploadModel::add($data);
                        }
                    }
                }
            }
        }

        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        $json["msg"] = $this->title_module . ' is toegevoegd!';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function editAction()
    {
        $data = $this->getPostdata();
        $id = CIInput()->post(UploadModel::$primaryKey);
        $rsdb = UploadModel::getOneById($id);
        $existdb = UploadModel::getOneByField('title', $data["title"]);
        if (empty($existdb) === false && $id != $existdb[UploadModel::$primaryKey]) {
            $json["msg"] = $this->title_module . " bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $this->rename_file($data[UploadTypeModel::$primaryKey], $rsdb[UploadTypeModel::$primaryKey], $rsdb['file_name']);

        if (empty($rsdb) === false) {
            UploadModel::edit($id, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt!';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function rename_file($new_type_id = 0, $old_type_id = 0, $file_name = "")
    {
        if ($new_type_id != $old_type_id) {
            $path_name_new = UploadTypeModel::fetchData($new_type_id, 'path');
            $path_name_old = UploadTypeModel::fetchData($old_type_id, 'path');

            $allowed_types = UploadTypeModel::fetchData($new_type_id, 'allowed_types');

            $file_path_old = UploadTypeModel::showDir($path_name_old) . '/' . $file_name;
            $file_path_new = UploadTypeModel::showDir($path_name_new) . '/' . $file_name;

            $path_parts = pathinfo($file_path_old);
            $allow_types = explode('|', $allowed_types);

            if (in_array(strtolower($path_parts['extension']), $allow_types) === false) {
                $json["msg"] = "Bestand type is niet juist!";
                $json["status"] = "error";
                exit(json_encode($json));
            }

            rename($file_path_old, $file_path_new);
        }
    }
}
