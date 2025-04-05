<?php

if (!function_exists('debug_as_file')) {

    function debug_as_file($postdata = null)
    {
        if (is_array($postdata) === true) {
            $postdata = json_encode($postdata);
        }
        $path = config_item('cache_path');
        write_file($path . 'debug.txt', $postdata);
    }
}

if (!function_exists('stringBetween')) {

    function stringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}

if (!function_exists('setSqlWhere')) {

    function setSqlWhere(array $whereSet = []): array
    {
        $newArray = [];
        foreach ($whereSet as $array) {
            if (is_array($array) && count($array) > 0) {
                foreach ($array as $k => $v) {
                    if (!is_null($v) && $v !== '') {
                        $newArray[$k] = $v === 'value_is_null' ? null : $v;
                    }
                }
            }
        }
        return $newArray;
    }
}

if (!function_exists('includeModuleTrait')) {

    function includeModuleTrait(string $filename, string $module)
    {
        $ck_file_exist_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR . $filename . '.php';
        if (file_exists($ck_file_exist_path)) {
            include_once $ck_file_exist_path;
        }
    }
}

if (!function_exists('includeModuleModel')) {

    function includeModuleModel(string $module, string $folder = "models")
    {
        $globalPath = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        $files = glob($globalPath . "*.php");
        if (!empty($files)) {
            foreach ($files as $file) {
                includeSingleModel($file);
            }
        }
    }
}

if (!function_exists('includeCoreModel')) {

    function includeCoreModel(string $folder = "")
    {
        $globalPath = APPPATH . 'models' . DIRECTORY_SEPARATOR;
        if (!empty($folder)) {
            $globalPath .= $folder . DIRECTORY_SEPARATOR;
        }

        $files = glob($globalPath . "*.php");
        if (!empty($files)) {
            foreach ($files as $file) {
                includeSingleModel($file);
            }
        }
    }
}

if (!function_exists('includeSingleModel')) {

    function includeSingleModel(string $file)
    {
        include_once $file;
        $className = basename($file, '.php');
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->hasMethod('__constructStatic')) {
            $reflectionMethod = $reflectionClass->getMethod('__constructStatic');
            if ($reflectionMethod->isStatic() && $reflectionMethod->getDeclaringClass()->getName() === $className) {
                $reflectionMethod->invoke(null);
            }
        }
    }
}

if (!function_exists('get_cache_file')) {
    function get_cache_file(string $file = ""): string
    {
        $ck_file_exist_path = config_item('cache_path') . $file;
        if (file_exists($ck_file_exist_path)) {
            return file_get_contents($ck_file_exist_path);
        }
        return "";
    }
}


if (!function_exists('update_cache_file')) {
    function update_cache_file(string $file = "", string $json = ""): string
    {
        $ck_file_exist_path = config_item('cache_path') . $file;
        write_file($ck_file_exist_path, $json);
        return $json;
    }
}

if (!function_exists('maxUploadByte')) {
    function maxUploadByte(): int
    {
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        $byte = $upload_mb * 1024 * 1024;
        return $byte;
    }
}

if (!function_exists('isJSON')) {

    function isJSON($string = null): bool
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}

if (!function_exists('script_tag')) {

    function script_tag(string $path = "", string $attribute = ""): string
    {
        return '<script src="' . $path . '" ' . $attribute . '></script>';
    }
}

if (!function_exists('link_tag')) {

    function link_tag(string $path = "", string $attribute = 'rel="stylesheet" type="text/css"'): string
    {
        return '<link href="' . $path . '" ' . $attribute . '>';
    }
}

if (!function_exists('sys_asset_url')) {

    function sys_asset_url(string $filename = ""): string
    {
        if (empty($filename)) {
            return "";
        }

        $httpPath = basename(APPPATH);
        $filenameAdd = str_contains($filename, "?") ? '&' : '?';
        $filename .= $filenameAdd . 'v=' . ENVIRONMENT_ASSET_VERSION;
        return base_url($httpPath . '/assets/' . $filename);
    }
}

if (!function_exists('module_url')) {

    function module_url(string $path = "", string $modulename = ""): string
    {
        if (empty($path)) {
            return "";
        }
        $module = $modulename;
        if (empty($modulename)) {

            $module = CIRouter()->module;
        }
        return site_url($module . '/' . $path);
    }
}

if (!function_exists('module_asset_url')) {

    function module_asset_url(string $filename = "", string $modulename = ""): string
    {
        if (empty($filename)) {
            return "";
        }
        $module = $modulename;
        if (empty($modulename)) {
            $module = CIRouter()->module;
        }

        $filenameAdd = str_contains($filename, "?") ? '&' : '?';
        $filename .= $filenameAdd . 'v=' . ENVIRONMENT_ASSET_VERSION;
        return base_url('modules/' . $module . '/assets/' . $filename);
    }
}

if (!function_exists('route_url')) {

    function route_url(string $url = '', string $extendUrl = ''): string
    {

        $urls = array_flip(CIRouter()->routes);
        if (isset($urls[$url]) === false || empty($url) === true) {
            return site_url($url);
        }
        if (empty($extendUrl) === false) {
            return site_url($urls[$url] . $extendUrl);
        }
        return site_url($urls[$url]);
    }
}

if (!function_exists('login_url')) {

    function login_url(string $path = ''): string
    {
        return site_url(ENVIRONMENT_ACCESS_URL . "/login" . $path);
    }
}

if (!function_exists('showError')) {

    function showError(int $statusCode = 401, string $setHeading = '', $setMessage = null)
    {
        $heading = match ($statusCode) {
            408 => lang('error_timeout_title'),
            409 => lang('error_is_active_title'),
            401 => lang('error_no_authorized_title'),
            404 => lang('error_user_no_find_title'),
            412 => lang('error_license_out_title'),
            default =>  lang('error_default_title'),
        };
        if (!empty($setHeading)) {
            $heading = $setHeading;
        }

        $message = match ($statusCode) {
            408 => lang('error_timeout_message'),
            409 => lang('error_is_active_message'),
            401 => lang('error_no_authorized_message'),
            404 => lang('error_user_no_find_message'),
            412 => lang('error_license_out_message'),
            default =>  lang('error_default_message'),
        };
        if (!empty($setMessage)) {
            $message = $setMessage;
        }

        show_error($message, $statusCode, $heading);
    }
}

if (!function_exists('dump')) {

    function dump($val, $vardump = false)
    {
        if ($vardump === true) {
            var_dump($val);
            exit;
        }
        echo '<pre>';
        print_r($val);
        echo '</pre>';
        exit;
    }
}

if (!function_exists('add_csrf_value')) {

    function add_csrf_value()
    {
        if (config_item('csrf_protection')) {
            $csrf = [
                'name' => CISecurity()->get_csrf_token_name(),
                'hash' => CISecurity()->get_csrf_hash()
            ];
            return '<input type="hidden" name="' . $csrf["name"] . '" value="' . $csrf["hash"] . '" />';
        }
    }
}

if (!function_exists('add_submit_button')) {

    function add_submit_button($search_data = null, $only_submit_text = false)
    {

        $submit_button_text = lang('change_button_text');
        if (empty($search_data) === true && $only_submit_text === false) {
            $submit_button_text = lang('add_button_text');
        }

        if (empty($search_data) === true && $only_submit_text) {
            $submit_button_text = lang('submit_button_text');
        }
        $data["text"] = $submit_button_text;
        $data["rsdb"] = $search_data;
        return GlobalModel::loadview("_share/global/submitButton", $data, true);
    }
}


if (!function_exists('search_button')) {

    function search_button(string $text = "")
    {
        $data["rsdb"] = null;
        $data["text"] = empty($text) ? lang('search_icon') : $text;
        return GlobalModel::loadview("_share/global/submitButton", $data, true);
    }
}

if (!function_exists('reset_button')) {

    function reset_button(string $text = "")
    {
        $data["text"] = empty($text) === true ? lang('reset_icon') : $text;
        $data["type"] = 'button';
        $data["extraClass"] = 'reset';
        return GlobalModel::loadview("_share/global/resetButton", $data, true);
    }
}

if (!function_exists('add_reset_button')) {

    function add_reset_button(string $text = "")
    {
        $data["text"] = empty($text) === true ? lang('reset_icon') : $text;
        $data["type"] = 'reset';
        $data["extraClass"] = '';
        return GlobalModel::loadview("_share/global/resetButton", $data, true);
    }
}
if (!function_exists('add_back_button')) {

    function add_back_button(string $url = "", string $text = "")
    {
        $defaultUrl = AccessCheckModel::backUrl();
        $data["url"] = empty($url) ? $defaultUrl : site_url($url);
        $data["text"] = empty($text) ? lang('back_button_text') : $text;
        return GlobalModel::loadview("_share/global/backButton", $data, true);
    }
}

if (!function_exists('get_curl')) {

    function get_curl($url = "", $params = [], $methode = "POST", $CURLOPT_HTTPHEADER = [])
    {
        if (empty($url) === true || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        switch ($methode) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return;
        }

        if (curl_errno($ch)) {
            return;
        }
        curl_close($ch);
        if ($response === false || empty($response) === true) {
            return;
        }
        return $response;
    }
}

if (!function_exists('add_app_log')) {

    function add_app_log($description = "", $uid = 0): int
    {
        return ApplogModel::insert($description, $uid);
    }
}

if (!function_exists('select_page_limit')) {

    function select_page_limit(int $setLimit = 0, bool $withLabel = true, array $listdb = [20 => "20", 50 => "50", 100 => "100", 200 => "200"], string $name = 'page_limit')
    {
        $label = "";
        if ($withLabel === true) {
            $label = lang("select_page_limit_label", "select_page_limit_label");
        }

        $limit = CIInput()->post_get($name) ?? loadPostGet($name, 'int');

        if ($setLimit > 0 && empty($limit) === true) {
            $limit = $setLimit;
        }

        $select = $label . '<select name="' . $name . '" class="form-control">';
        $select .= "<option value=''>------</option>";
        foreach ($listdb as $key => $rs) {
            $ckk = $limit == $key ? "selected" : '';
            $select .= "<option value={$key} $ckk >$rs</option>";
        }
        $select .= '</select>';
        $html = '<div class="form-group">' . $select . '</div>';
        return $html;
    }
}

if (!function_exists('span_tooltip')) {

    function span_tooltip(string $tooltip = "")
    {
        $data["msg"] = $tooltip;
        return GlobalModel::loadview("_share/global/span_tooltip", $data, true);
    }
}

if (!function_exists('invalid_feedback')) {

    function invalid_feedback(string $class = "invalid-feedback", string $text = "")
    {
        $msg = empty($text) === true ? lang('required_field_text_global') : $text;
        return '<div class="' . $class . '">' . $msg . '</div>';
    }
}

if (!function_exists('select_boolean')) {

    function select_boolean(string $name = 'is_boolean', int $setId = 0, bool $with_empty = false, array $listdb = [0 => "Nee", 1 => "Ja"])
    {
        $id = CIInput()->post_get($name) ?? loadPostGet($name) ?? $setId;
        if ($id != "" && $setId === 2) {
            $id = intval($id);
        }

        if ($setId < 2) {
            $id = $setId;
        }

        $select = '<select name="' . $name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $key => $value) {
            $ckk = $id === $key ? "selected" : '';
            $select .= "<option value={$key} $ckk >$value</option>";
        }
        $select .= '</select>';
        $html = '<div class="form-group">' . $select . '</div>';
        return $html;
    }
}

if (!function_exists('select')) {

    function select(string $name = '', array $listdb = [], string $setValue = "", bool $with_empty = false): string
    {
        $select = '<select name="' . $name . '" class="form-control nochange">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $value => $label) {
            $ckk = $setValue == $value ? "selected" : '';
            $select .= '<option value="' . $value . '" ' . $ckk . '>' . $label . '</option>';
        }
        $select .= '</select>';
        return $select;
    }
}

if (!function_exists('oneStepCkDatetime')) {

    function oneStepCkDatetime(string $inputname = 'start', bool $is_empty = false)
    {
        $startInputData = CIInput()->post($inputname) ?? "";
        if (empty($startInputData) === true && $is_empty === true) {
            return null;
        }
        if (strlen($startInputData) !== 19) {
            $json["msg"] = "Er is geen datum tijd gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        AjaxckModel::datetime($startInputData);
        $value = F_datetime::convert_datetime($startInputData, 'Y-m-d H:i:s');
        AjaxckModel::value('datetime', $value, "Datum tijd is niet juist");
        return $value;
    }
}

if (!function_exists('oneStepCkDate')) {

    function oneStepCkDate(string $inputname = 'start', bool $is_empty = false)
    {
        $startInputData = CIInput()->post($inputname) ?? "";
        if (empty($startInputData) === true && $is_empty) {
            return null;
        }
        if (strlen($startInputData) !== 10) {
            $json["msg"] = "Er is geen datum gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        AjaxckModel::date($startInputData);
        $value = F_datetime::convert_date($startInputData, 'Y-m-d');
        AjaxckModel::value('date', $value, "Datum is niet juist");
        return $value;
    }
}

if (!function_exists('c_key')) {

    function c_key(string $key = ""): string
    {
        $value = ConfigModel::fetch($key);
        if ($key === 'webapp_default_show_per_page' && empty($value)) {
            return "50";
        }
        if ($key === '_core_app_buildnr' && empty($value)) {
            return date('Y');
        }
        return $value;
    }
}

if (!function_exists('lang')) {

    function lang(string $line = "", string $notFoundValue = ""): string
    {
        $str = LanguageTagModel::fetch($line);
        if (empty($str)) {
            $str = CILang()->line($line, false);
        }
        if (!empty($str)) {
            return stripslashes($str);
        }
        return $notFoundValue;
    }
}


if (!function_exists('isInternet')) {

    function isInternet(string $url = 'www.google.nl'): bool
    {
        $connected = @fsockopen($url, 443);
        $isConn = false;
        if ($connected) {
            $isConn = true;
            fclose($connected);
        }
        return $isConn;
    }
}
if (!function_exists('url_exists')) {

    function url_exists(array $servers = []): array
    {
        $listdb = [];
        if (empty($servers) === false) {
            $ch = [];
            $mh = curl_multi_init();
            foreach ($servers as $type => $url) {
                $ch[$type] = curl_init();
                curl_setopt($ch[$type], CURLOPT_URL, $url);
                curl_setopt($ch[$type], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$type], CURLOPT_PROXYPORT, 3128);
                curl_setopt($ch[$type], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$type], CURLOPT_SSL_VERIFYPEER, 0);
                curl_multi_add_handle($mh, $ch[$type]);
            }

            $running = 0;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);

            foreach (array_keys($ch) as $key) {
                $http_code = curl_getinfo($ch[$key], CURLINFO_HTTP_CODE);
                $response = curl_multi_getcontent($ch[$key]);
                $appStatusOnline = true;
                if ($http_code !== 200 || $response === null) {
                    $appStatusOnline = false;
                }
                $listdb[$key] = $appStatusOnline;
                curl_multi_remove_handle($mh, $ch[$key]);
                curl_close($ch[$key]);
            }
            curl_multi_close($mh);
        }
        return $listdb;
    }
}
if (!function_exists('setFieldAndOperator')) {

    function setFieldAndOperator(string $inputname = '', string $field = "", $defaultKeyValue = null, bool $toConvertDateTime = false, string $defaultOperator = "=", string $setTableFix = "")
    {
        $setOperator = CIInput()->post_get($inputname . '_operator') ?? $defaultOperator;
        $searchKey = CIInput()->post_get($inputname) ?? $defaultKeyValue;

        if (!empty($searchKey)) {
            $operator = "=";
            if ($toConvertDateTime === true) {
                $regEx = '/(\d{2})-(\d{2})-(\d{4})/';
                if (preg_match($regEx, $searchKey) > 0) {
                    $searchKey = @date_format(date_create($searchKey), 'Y-m-d');
                }

                $regEx2 = '/(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2})/';
                if (preg_match($regEx2, $searchKey) > 0) {
                    $searchKey = @date_format(date_create($searchKey), 'Y-m-d H:i:s');
                }
            }
            $searchContent = $searchKey;
            $tableFix = CIDb()->dbprefix($field);
            if (!empty($setTableFix)) {
                $tableFix = $setTableFix . $field;
                if ($setTableFix === 'value_is_null') {
                    $tableFix = $field;
                }
            }

            switch ($setOperator) {
                case '=':
                    $operator = '=';
                    break;
                case '!=':
                    $operator = '!=';
                    break;
                case '>':
                    $operator = '>';
                    break;
                case '>=':
                    $operator = '>=';
                    break;
                case '<':
                    $operator = '<';
                    break;
                case '<=':
                    $operator = '<=';
                    break;
                case 'notregexp':
                    $operator = 'NOT REGEXP "(' . $searchKey . ')"';
                    $searchContent = 'value_is_null';
                    break;
                case 'regexp':
                    $operator = 'REGEXP "(' . $searchKey . ')"';
                    $searchContent = 'value_is_null';
                    break;
                case 'in':
                    $elements = [];
                    foreach ($searchKey as $element) {
                        if (is_numeric($element)) {
                            $elements[] = $element;
                        } else {
                            $elements[] = '"' . $element . '"';
                        }
                    }
                    $operator = 'IN (' . implode(', ', $elements) . ')';
                    $searchContent = 'value_is_null';
                    break;
                case 'notin':
                    $elements = [];
                    foreach ($searchKey as $element) {
                        if (is_numeric($element)) {
                            $elements[] = $element;
                        } else {
                            $elements[] = '"' . $element . '"';
                        }
                    }
                    $operator = 'NOT IN (' . implode(', ', $elements) . ')';
                    $searchContent = 'value_is_null';
                    break;
                case 'find':
                    $elements = [];
                    foreach ($searchKey as $element) {
                        if (is_numeric($element)) {
                            $elements[] = 'FIND_IN_SET (' . $element . ',' . $tableFix . ')';
                        } else {
                            $elements[] = 'FIND_IN_SET (' . '"' . $element . '"' . ',' . $tableFix . ')';
                        }
                    }
                    $operator = implode(' OR ', $elements);
                    $searchContent = 'value_is_null';
                    return [$operator => $searchContent];
                case 'not_find':
                    $elements = [];
                    foreach ($searchKey as $element) {
                        if (is_numeric($element)) {
                            $elements[] = 'NOT FIND_IN_SET (' . $element . ',' . $tableFix . ')';
                        } else {
                            $elements[] = 'NOT FIND_IN_SET (' . '"' . $element . '"' . ',' . $tableFix . ')';
                        }
                    }
                    $operator = implode(' AND ', $elements);
                    $searchContent = 'value_is_null';
                    return [$operator => $searchContent];
            }
            return [$tableFix . ' ' . $operator => $searchContent];
        }
    }
}

if (!function_exists('labelSelectInput')) {

    function labelSelectInput(string $label = "", string $inputname = "", string $defaultKeyValue = "", string $defaultOperator = "regexp")
    {
        $data["label"] = $label;
        $operatorName = $inputname . '_operator';
        // $fixValue = empty(loadPostGet($operatorName) === true) ? 'regexp' : loadPostGet($operatorName);
        // $operatorName_value = CIInput()->post_get($operatorName) ?? $fixValue;
        // if (empty($defaultOperator) === false) {
        //     $operatorName_value = $defaultOperator;
        // }
        $data["select"] = select($operatorName, ['regexp' => 'Bevat', '' => '=', '!=' => '<>', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<=', 'notregexp' => 'Bevat niet'], $defaultOperator);
        $data["inputname_value"] = CIInput()->post_get($inputname) ?? $defaultKeyValue;
        $data["inputname"] = $inputname;
        return GlobalModel::loadview("_share/global/label_select_input", $data, true);
    }
}


if (!function_exists('labelSelectSelectMulti')) {

    function labelSelectSelectMulti(string $label = "", string $inputname = "", string $selectMulti = "", string $defaultOperator = "in")
    {
        $data["label"] = $label;
        $operatorName = $inputname . '_operator';
        $data["select"] = select($operatorName, ['in' => '=', 'notin' => '<>', 'find' => 'Bevat', 'not_find' => 'Bevat niet'], $defaultOperator);
        $data["selectMulti"] = $selectMulti;
        return GlobalModel::loadview("_share/global/label_select_selectmulti", $data, true);
    }
}

if (!function_exists('select_order_by')) {

    function select_order_by(array $listdb = [], string $setSelected = '', string $setDefault = "", bool $withLabel = true, string $selectName = 'sql_orderby_field'): string
    {

        $label = "";
        if ($withLabel) {
            $label = lang("select_order_by_label", "select_order_by_label");
        }
        $idNow = CIInput()->post_get($selectName) ?? loadPostGet($selectName);
        if (!empty($setSelected) && empty($idNow)) {
            $idNow = $setSelected;
        }

        $default = !empty($setDefault) ? $setDefault : array_key_first($listdb);
        $select = $label . '<select name=' . $selectName . ' class="form-control">';
        $select .= '<option value=' . $default . ' >------</option>';
        foreach ($listdb as $key => $value) {
            $ckk = $idNow === $key ? "selected" : '';
            $select .= "<option value={$key} $ckk >$value</option>";
        }
        $select .= '</select>';
        $html = '<div class="form-group">' . $select . '</div>';
        return $html;
    }
}

if (!function_exists('setFieldOrderBy')) {

    function setFieldOrderBy(string $setSelected = '', string $selectName = 'sql_orderby_field')
    {
        $orderby_field = CIInput()->post_get($selectName) ?? loadPostGet($selectName);
        if (!empty($setSelected) && empty($orderby_field)) {
            $orderby_field = $setSelected;
        }

        $data_order_by = [];
        if (!empty($orderby_field)) {
            if (strpos($orderby_field, '&&') !== false) {
                $array = explode("&&", $orderby_field);
                foreach ($array as $value) {
                    $orderbyItem = explode("#", $value);
                    if (isset($orderbyItem[0]) && isset($orderbyItem[1])) {
                        $data_order_by[$orderbyItem[0]] = $orderbyItem[1];
                    }
                }
            }

            if (strpos($orderby_field, '&&') === false) {
                $orderby_field_data = explode("#", $orderby_field);
                if (isset($orderby_field_data[0]) && isset($orderby_field_data[1])) {
                    $data_order_by[$orderby_field_data[0]] = $orderby_field_data[1];
                }
            }
        }
        return $data_order_by;
    }
}


if (!function_exists('addButton')) {

    function addButton(string $permission = "", string $url = "", string $text = "", string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        $data["disabled"] = 'disabled';
        if ($checkHas === true && $url !== "") {
            $data["disabled"] = '';
        }
        $path = $url;
        if (empty($setPath) === false) {
            $path = $setPath . '/' . AccessCheckModel::$backPath . '/' . $url;
        }
        $data["url"] = site_url($path);
        $data["text"] = empty($text) ? lang('add_icon') : $text;
        return GlobalModel::loadview("_share/global/addButton", $data, true);
    }
}

if (!function_exists('delButton')) {

    function delButton(string $permission = "", $searchData = 0, string $text = "", string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        $data["disabled"] = 'disabled';
        if ($checkHas === true && $searchData > 0) {
            $data["disabled"] = '';
        }
        $data["searchData"] = $searchData;
        $data["text"] = empty($text) ? lang('del_icon') : $text;
        return GlobalModel::loadview("_share/global/delButton", $data, true);
    }
}


if (!function_exists('editButton')) {

    function editButton(string $permission = "", string $url = "", string $text = "", string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        $data["disabled"] = 'disabled';
        if ($checkHas === true && $url !== "") {
            $data["disabled"] = '';
        }
        $path = $url;
        if (empty($setPath) === false) {
            $path = $setPath . '/' . AccessCheckModel::$backPath . '/' . $url;
        }
        $data["url"] = site_url($path);
        $data["text"] = empty($text) ? lang('edit_icon') : $text;
        return GlobalModel::loadview("_share/global/editButton", $data, true);
    }
}


if (!function_exists('viewButton')) {

    function viewButton(string $permission = "", string $url = "", string $text = "", string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        $data["disabled"] = 'disabled';
        if ($checkHas === true && $url !== "") {
            $data["disabled"] = '';
        }
        $path = $url;
        if (empty($setPath) === false) {
            $path = $setPath . '/' . AccessCheckModel::$backPath . '/' . $url;
        }
        $data["url"] = site_url($path);
        $data["text"] = empty($text) ? lang('view_icon') : $text;
        return GlobalModel::loadview("_share/global/viewButton", $data, true);
    }
}


if (!function_exists('inlineEditButton')) {

    function editInlineButton(string $permission = "", int $editId = 0, string $field = "", string $text = "", string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        if ($checkHas === true && $editId > 0 && empty($field) === false) {
            $data["editId"] = $editId;
            $data["field"] = $field;
            $data["text"] = $text;
            return GlobalModel::loadview("_share/global/editInlineButton", $data, true);
        }
        return $text;
    }
}


if (!function_exists('editBooleanInlineButton')) {

    function editBooleanInlineButton(string $permission = "", int $editId = 0, string $field = "", int $value = 0, string $style = "primary",  string $setPath = "")
    {
        $checkHas = PermissionModel::checkHas($permission, $setPath);
        if ($checkHas === true && $editId > 0 && empty($field) === false) {
            $data["editId"] = $editId;
            $data["field"] = $field;
            $data["value"] = $value;
            $data["style"] = $style;
            return GlobalModel::loadview("_share/global/editBooleanInlineButton", $data, true);
        }
        return $value;
    }
}

if (!function_exists('defaultFieldByLanguage')) {

    function defaultFieldByLanguage(string $field = "name", string $setLanguage = ""): string
    {
        return LanguageModel::fieldByLanguage($field, $setLanguage);
    }
}

if (!function_exists('app_form_action_status')) {

    function app_form_action_status()
    {
        if (CIInput()->get('format') === "json") {
            return "no";
        }
        return "yes";
    }
}

if (!function_exists('referrer_url')) {

    function referrer_url(bool $set = false)
    {
        if ($set) {
            $value = current_url() . '?' . $_SERVER['QUERY_STRING'];
            return CISession()->set_userdata('referrer_url', $value);
        }

        $session_data = CISession()->userdata('referrer_url');
        if (empty($session_data) === false && $session_data !== null) {
            CISession()->set_userdata('referrer_url', null);
            return $session_data;
        }

        return null;
    }
}

if (!function_exists('loadPostGet')) {

    function loadPostGet(string $key = '', $valueType = 'string')
    {
        $result = GlobalModel::loadPostGet($key, $valueType);
        if ($result === null && $valueType === 'string') {
            return '';
        }
        if ($result === null && $valueType === 'float') {
            return 0;
        }
        if ($result === null && $valueType === 'int') {
            return 0;
        }
        if ($result === null && $valueType === 'array') {
            return [];
        }
        return $result;
    }
}

if (!function_exists('reArrayFiles')) {
    function reArrayFiles(array $file_post = []): array
    {
        $file_ary = [];
        if (!empty($file_post)) {
            $file_count = count($file_post['name']);
            $file_keys = array_keys($file_post);

            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $file_post[$key][$i];
                }
            }
        }
        return $file_ary;
    }
}
