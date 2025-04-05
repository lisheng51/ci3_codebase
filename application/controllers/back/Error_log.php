<?php

class Error_log extends Back_Controller
{

    protected $title_module = 'Foutmelding';

    #[DisplayAttribute('Foutmelding overzicht', 'Foutmelding overzicht')]
    public function index()
    {
        $result = $this->getData();
        $data["total"] = $result["total"];
        $data["pagination"] = GlobalModel::showPage($data["total"]);
        $data["listdb"] = $result["listdb"];
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . ' overzicht';
        $data["event_result_box"] = '';
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('SQL overzicht', 'SQL overzicht')]
    public function sql()
    {
        $result = $this->getData('sql');
        $data["total"] = $result["total"];
        $data["pagination"] = GlobalModel::showPage($data["total"]);
        $data["listdb"] = $result["listdb"];
        $data["result"] = $this->view_layout_return("ajax_list_sql", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = 'SQL overzicht';
        $data["event_result_box"] = '';
        $this->view_layout("index", $data);
    }

    private function getData(string $level = "error")
    {
        $date_start = "";
        $date_end = "";
        $reportrange = CIInput()->post("reportrange") ?? "";
        if (!empty($reportrange)) {
            $arr_range = explode("t/m", $reportrange);
            $date_start = strtotime(trim($arr_range[0]));
            $date_end = strtotime(trim($arr_range[1]));
        }

        $page_limit = CIInput()->post("page_limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;

        $page_number = CIInput()->get("page_number");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;

        $log_path = config_item('log_path');

        $arrResult = $arr = $temp = [];
        $listdb = directory_map($log_path, 1);
        if (empty($listdb)) {
            $data["total"] = 0;
            $data["listdb"] = $arrResult;
            return $data;
        }

        $log_file_extension = config_item('log_file_extension');
        foreach ($listdb as $value) {
            preg_match('/' . $level . '-(.+?).' . $log_file_extension . '/i', $value, $arr);
            if (empty($arr)) {
                continue;
            }

            $filename = end($arr);
            $timestamp = strtotime($filename);
            if (empty($date_start) === false && $timestamp < $date_start || empty($date_end) === false && $timestamp > $date_end) {
                continue;
            }

            $temp["date"] = $filename;
            $contentd = file_get_contents($log_path . $value);
            $array  = array_filter(explode(PHP_EOL, $contentd));
            $temp["downloadButton"] = viewButton($this->controller_name . '.download', $this->controller_url . "/download?fileName=" . $value, 'Download');
            $temp["listdb"] = $array;
            $temp["count"] = count($array);
            $arrResult[] = $temp;
        }

        usort($arrResult, function ($b, $a) {
            return strtotime($a["date"]) - strtotime($b["date"]);
        });

        $data["total"] = count($arrResult);
        $data["listdb"] = array_slice($arrResult, $page, $limit);
        return $data;
    }

    #[DisplayAttribute('Foutmelding downloaden', '')]
    public function download()
    {
        $fileName = CIInput()->get('fileName') ?? '';
        $file = config_item('log_path') . $fileName;
        if (!file_exists($file)) {
            redirect($this->controller_url);
        }

        force_download($fileName, file_get_contents($file));
    }
}
