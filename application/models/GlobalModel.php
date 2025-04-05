<?php

class GlobalModel
{
    public static $querySegmentPageNumber = 'page_number';
    public static function savePostGet()
    {
        if (CIInput()->post()) {
            $formSession = CIInput()->post();
            $formSession[self::$querySegmentPageNumber] = CIInput()->get(self::$querySegmentPageNumber);
            CISession()->set_userdata(self::fetchSessionKey(), $formSession);
        }
    }

    public static function fetchSessionKey()
    {
        $sessionKey = strtolower(uri_string() . '_formPostSession');
        return $sessionKey;
    }

    public static function emptyPostGet()
    {
        CISession()->set_userdata(self::fetchSessionKey());
    }


    public static function loadPostGet(string $key = '', $valueType = 'string')
    {
        $formSession = CISession()->userdata(self::fetchSessionKey());
        $result = null;
        if (empty($formSession) || empty($key) || !isset($formSession[$key])) {
            return $result;
        }

        if (!empty($formSession) && !empty($key) && isset($formSession[$key])) {
            $result =  $formSession[$key];
        }

        if ($valueType === 'float') {
            return (float) $result;
        }

        if ($valueType === 'int') {
            return (int) $result;
        }

        if ($valueType === 'string') {
            return (string) $result;
        }
        if ($valueType === 'array') {
            return (array) $result;
        }

        return $result;
    }


    public static function showPostGet()
    {
        $formSession = CISession()->userdata(self::fetchSessionKey());
        if (!empty($formSession)) {
            return $formSession;
        }
        return null;
    }

    public static function redirectWithPageNumber()
    {
        if (!CIInput()->get() && !CIInput()->post()) {
            $query_string_segment = self::$querySegmentPageNumber;
            $pageNumberSession = loadPostGet($query_string_segment, 'int');
            if (!isset($_GET[$query_string_segment]) && $pageNumberSession > 1) {
                $fields_string = http_build_query([
                    $query_string_segment => $pageNumberSession
                ]);
                $url = current_url() . '?' . $fields_string;
                $formSession[self::$querySegmentPageNumber] = 0;
                CISession()->set_userdata(self::fetchSessionKey(), $formSession);
                redirect($url);
            }
        }
    }

    public static function editTimeInfo($rsdb = null)
    {
        if (empty($rsdb)) {
            return "";
        }

        $date_start = $rsdb["datecreated"] ?? $rsdb["created_at"] ?? null;
        $date_end = $rsdb["datemodified"] ?? $rsdb["modified_at"] ?? null;

        $createdby = $rsdb["createdby"] ?? 0;
        $modifiedby = $rsdb["modifiedby"] ?? 0;

        $data["created_at"] = F_datetime::convert_datetime($date_start);
        $data["createdby"] = UserModel::display($createdby);
        $data["modified_at"] = null;
        $data["modifiedby"] = null;
        if (!empty($date_end)) {
            $data["modified_at"] = F_datetime::convert_datetime($date_end);
            $data["modifiedby"] = UserModel::display($modifiedby);
        }

        return self::loadview('_share/global/edit_time_info', $data, true);
    }

    public static function eventResultBox(string $body = "", string $title = "Toelichting", string $extrabody = "")
    {
        if (empty($body)) {
            $class = CIRouter()->class;
            $method = CIRouter()->method;
            $languageTag = $class . '_' . $method;
            $module = CIRouter()->module;
            if (!empty($module)) {
                $languageTag = $module . '_' . $class . '_' . $method;
            }
            $body =  lang('event_result_box_' . strtolower($languageTag) . '_body');
        }

        $data["title"] = $title;
        $data["body"] = $body . $extrabody;
        if (empty($data['body'])) {
            return '';
        }
        return self::loadview("_share/global/event_result_box", $data, true);
    }

    public static function loadView(string $viewpath = '', array $data = [], bool $return = false)
    {
        return CILoader()->view($viewpath, $data, $return);
    }

    public static function decryptData(string $result = ""): string
    {
        return CIEncryption()->decrypt($result);
    }

    public static function encryptData(string $result = ""): string
    {
        return CIEncryption()->encrypt($result);
    }

    public static function showPage(int $total = 0, int $per_page = 0, string $url = "")
    {
        $query_string_segment = self::$querySegmentPageNumber;
        $config = [];
        $page_limit = CIInput()->post("page_limit");
        $config["per_page"] = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;
        if ($per_page > 0) {
            $config["per_page"] = $per_page;
        }

        $config["base_url"] = current_url();
        if (empty($url) === false) {
            $config["base_url"] = site_url($url);
        }
        $config["total_rows"] = $total;
        $config['page_query_string'] = true;
        $config['query_string_segment'] = $query_string_segment;
        $config["use_page_numbers"] = true;
        $query_string = $_GET;
        if (isset($query_string[$query_string_segment])) {
            unset($query_string[$query_string_segment]);
        }

        if (count($query_string) > 0) {
            $config['suffix'] = '&' . http_build_query($query_string, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($query_string, '', "&");
        }
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = lang('show_page_first_link_text');
        $config['last_link'] = lang('show_page_last_link_text');
        $config['num_links'] = 5;
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['prev_link'] = lang('show_page_prev_link_text');
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['next_link'] = lang('show_page_next_link_text');
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        CIPagination()->initialize($config);
        return CIPagination()->create_links();
    }

    public static function checkPasswordDate(string $password_date = "")
    {
        $data["webapp_ck_pass_notify_day"] = c_key("webapp_ck_pass_notify_day");
        if (!LoginModel::checkPasswordDate($password_date)) {
            return self::loadview('_share/global/check_password_date', $data, true);
        }
    }

    public static function alert(string $moduleName = ""): string
    {
        $data = AjaxckModel::getSession("", true);
        if ($data === null) {
            return "";
        }
        switch ($data["status"]) {
            case "error":
                $data["className"] = "danger";
                break;
            case "good":
                $data["className"] = "success";
                break;
            case "warning":
                $data["className"] = "warning";
                break;
            default:
                $data["className"] = "info";
                break;
        }

        if (empty($moduleName) === false) {
            $ck_file_exist_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_share' . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR . 'alert.php';
            if (file_exists($ck_file_exist_path)) {
                return self::loadview($moduleName . '/_share/global/alert', $data, true);
            }
        }
        return self::loadview('_share/global/alert', $data, true);
    }

    public static function getInfo(string $path = "")
    {
        $key = 'changelog';
        $data_file = APPPATH . 'info.php';
        require($data_file);

        if (!empty($path)) {
            $data = ModuleModel::getInfo($path);
        }

        if (!isset($data[$key])) {
            return [
                [
                    "name" => "Informatie is niet gevonden",
                    "created_at" => date("Y-m-d H:i:s"),
                    "info" => [
                        "Contact met opnemen met 0614304050"
                    ]
                ]
            ];
        }
        return $data[$key];
    }

    public static function lastVersion(string $path = "")
    {
        $listdb = self::getInfo($path);
        $lastArr = end($listdb);
        return $lastArr["name"];
    }

    public static function makeSql(string $module = "", array $add_insert_list = [])
    {
        $tables = CIDb()->list_tables();
        $linesFilePath = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        if (empty($module)) {
            $linesFilePath = APPPATH . 'sql' . DIRECTORY_SEPARATOR;
        }

        $fileList = [];
        $arrSqlFile = directory_map($linesFilePath);
        if (empty($arrSqlFile) === false && empty($tables) === false) {
            foreach ($arrSqlFile as $name) {
                $fileList[] = basename($name, ".sql");
            }

            foreach ($tables as $table) {
                if (in_array($table, $fileList) === false) {
                    continue;
                }
                $add_insert = false;
                $extraString = 'ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1;';
                if (in_array($table, $add_insert_list) === true) {
                    $add_insert = true;
                    $extraString = '';
                }

                $prefs = array(
                    'tables' => [$table],
                    'format' => 'txt',
                    'add_drop' => false,
                    'add_insert' => $add_insert,
                    'newline' => "\n"
                );

                $backup = CIDbUtility()->backup($prefs);
                write_file($linesFilePath . $table . '.sql', $backup . $extraString);
            }
        }
        return $linesFilePath;
    }
}
