<?php

class Sendmail extends Back_Controller
{

    protected $title_module = 'Sendmail';

    #[DisplayAttribute('Sendmail overzicht', 'Overzicht')]
    public function index()
    {
        $reportrange = CIInput()->post("reportrange");
        if (empty($reportrange) === false) {
            $arr_range = explode("t/m", $reportrange);
            $data_where[] = ["send_date >=" => date_format(date_create(trim($arr_range[0])), 'Y-m-d 00:00:00')];
            $data_where[] = ["send_date <=" => date_format(date_create(trim($arr_range[1])), 'Y-m-d 23:59:59')];
        }

        $data_where[] = setFieldAndOperator('to_email', SendMailModel::$table . '.to_email');
        $data_where[] = setFieldAndOperator('from_email', SendMailModel::$table . '.from_email');
        $data_where[] = setFieldAndOperator('subject', SendMailModel::$table . '.subject');
        SendMailModel::$sqlWhere = setSqlWhere($data_where);
        SendMailModel::$sqlOrderBy = setFieldOrderBy();

        $total = SendMailModel::getTotal();
        $data["listdb"] = $this->getList();
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total);
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }
        $data["title"] = $this->title_module . ' overzicht';
        $this->view_layout("index", $data);
    }

    private function getList()
    {
        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $arr_result = [];
        $listdb = SendMailModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs["del_url"] = site_url($this->controller_url . "/del");
            $rs["send_date_view"] = F_datetime::convert_datetime($rs["send_date"]);
            $rs["open_date_view"] = F_datetime::convert_datetime($rs["open_date"]);
            $rs["view_url"] = SendMailModel::createEvent($rs, 'sys');
            $reply_to_json = json_decode($rs["reply_to_json"], true) ?? [];
            $cc_json = json_decode($rs["cc_json"], true) ?? [];
            $bcc_json = json_decode($rs["bcc_json"], true) ?? [];
            $rs["attach"] = $this->showAttach($rs['attach']);
            $rs["file"] = $this->showFile($rs[SendMailModel::$primaryKey]);
            $rs["reply"] = implode(", ", array_keys($reply_to_json));
            $rs["cc"] = implode(", ", array_keys($cc_json));
            $rs["bcc"] = implode(", ", array_keys($bcc_json));
            $rs["to_email"] = editInlineButton($this->controller_name . '.editInline', $rs[SendMailModel::$primaryKey], 'to_email', $rs['to_email']);
            $arr_result[] = $rs;
        }
        return $arr_result;
    }

    #[DisplayAttribute('Sendmail inline wijzigen', '')]
    public function editInline()
    {
        $id = CIInput()->post("editid") ?? 0;
        $rsdb = SendMailModel::getOneById($id);
        if (empty($rsdb) === true) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $field = CIInput()->post("field") ?? "";
        $fieldvalue = CIInput()->post("fieldvalue") ?? "";

        if (empty($field) === true) {
            $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data[$field] = $fieldvalue;

        if ($field !== 'to_email') {
            $json["msg"] = $this->title_module . ' is niet bijgewerkt!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        SendMailModel::edit($id, $data);
        $json["msg"] = $this->title_module . ' is bijgewerkt';
        $json["status"] = "good";
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Sendmail bijlage downloaden', '')]
    public function downloadFile(int $id = 0)
    {
        $rsdb = SendMailFileModel::getOneById($id);
        if (empty($rsdb) === false) {
            $file_content = base64_decode($rsdb["base64"]);
            $filename = $rsdb["file_name"];
            force_download($filename, $file_content);
        }
    }

    private function showFile(int $mail_id = 0): string
    {
        $content = "";
        if ($mail_id > 0) {
            $files = SendMailFileModel::getAllByField(SendMailModel::$primaryKey, $mail_id);
            if (empty($files) === false) {
                foreach ($files as $rsdb) {
                    $link = site_url($this->controller_url . "/downloadFile/" . $rsdb[SendMailFileModel::$primaryKey]);
                    $filename = $rsdb["file_name"];
                    $content .= '<a href="' . $link . '" class="btn btn-info btn-sm mt-1">' . $filename . '</a> ';
                }
            }
        }
        return $content;
    }

    private function showAttach($attach = null): string
    {
        $content = "";
        if (empty($attach) === false) {
            $multiAttach = explode(",", $attach);
            foreach ($multiAttach as $key => $path) {
                $key++;
                $content .= '<a target="_blank" href="' . base_url($path) . '" class="btn btn-info btn-sm mt-1">' . $key . '</a> ';
            }
        }
        return $content;
    }

    #[DisplayAttribute('Sendmail verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        $rsdb = SendMailModel::getOneById(intval($id));
        if (empty($rsdb) === true) {
            $json["msg"] = "Deze kan niet worden verwijderd!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        SendMailModel::del(intval($id));
        $json["msg"] = $this->title_module . " is verwijderd!";
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
