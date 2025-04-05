<?php

class API_Controller extends MX_Controller
{

    protected $apiId = ENVIRONMENT !== 'development' ? 0 : 1;
    protected $checkCodeAccessDatetime = false;
    protected $usingBodySecurity = false;
    protected $usingBodyOutSecurity = false;
    protected $checkPermission = false;
    protected $checkUserPermission = false;
    protected $checkMethodPost = false;
    protected $arrPost = [];
    protected $version = '';
    protected $arrUserdb = [];

    public function __construct()
    {
        parent::__construct();
        if ($this->checkMethodPost && isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
            if (strtoupper($method) !== 'POST') {
                ApiModel::out('HTTP methode is geen POST', 90);
            }
        }
        $this->load->database();
        LanguageModel::setLanguage();
        ApiModel::$usingBodyOutSecurity = $this->usingBodyOutSecurity;
        $this->version = ApiModel::getVersion();

        $rsdb =  ApiModel::getOneByRequest();
        if ($this->apiId > 0) {
            $rsdb = ApiModel::getOneById($this->apiId);
        }

        if (empty($rsdb)) {
            ApiModel::out('API is niet geldig', 99);
        }

        $this->apiId = $rsdb[ApiModel::$primaryKey];

        if ($this->checkCodeAccessDatetime) {
            ApiModel::checkCodeAccessDatetime($rsdb);
        }

        if ($this->checkPermission) {
            ApiModel::checkPermission($rsdb["permission_group_ids"], 92);
        }

        if ($this->checkUserPermission) {
            $userDB = ApiModel::checkFetchByJWT();
            $this->arrUserdb = $userDB;
            if (!UserModel::isSuperUser($userDB)) {
                ApiModel::checkPermission($userDB["permission_group_ids"], 91);
            }
        }

        if ($this->usingBodySecurity) {
            $inputBody = CIInput()->post('body');
            if (is_null($inputBody)) {
                ApiModel::out('Geen body gevonden', 98);
            }
            $body = AesModel::decrypt(rawurldecode($inputBody));
            $postData = json_decode($body, true);
            if (is_null($postData)) {
                ApiModel::out('Geen body gevonden', 98);
            }
            $this->arrPost = $postData;
        }

        if (!empty($this->router->module) && $this->router->module !== 'api') {
            $lang = LanguageModel::getLanguage();
            ModuleModel::language($lang);
        }
    }

    protected function addLog(string $msg = '')
    {
        return ApiLogModel::insert($msg, $this->apiId);
    }
}

class Cron_Controller extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!is_cli() && ENVIRONMENT !== 'development') {
            exit;
        }

        $this->load->database();
    }
}

class Su_Controller extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!AccessCheckModel::ipAllow()) {
            showError();
        }
        AccessCheckModel::authorized();
        UserModel::secureId();
        $this->load->database();
        LanguageModel::setLanguage();

        if (!empty($this->router->module) && $this->router->module !== 'su') {
            $lang = LanguageModel::getLanguage();
            ModuleModel::language($lang);
        }
    }
}

class Ajax_Controller extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        LanguageModel::setLanguage();

        if (!empty($this->router->module) && $this->router->module !== 'ajax') {
            $lang = LanguageModel::getLanguage();
            ModuleModel::language($lang);
        }
    }
}

class Site_Controller extends MX_Controller
{

    protected $controller_name;
    protected $controller_url;
    protected $path_name = 'site';
    protected $module_path_name;
    protected $module_url = "";
    protected $title_module = '';
    protected $module_style = '';

    public function __construct()
    {
        parent::__construct();
        AccessCheckModel::maintenance();
        $this->load->database();
        $this->controller_name = get_class($this);
        $this->controller_url = $this->path_name . "/" . $this->controller_name;
        LanguageModel::setLanguage();

        if (!empty($this->router->module) && $this->router->module !== $this->path_name) {
            $this->module_url = $this->router->module;
            $this->module_path_name = $this->router->module . "/" . $this->path_name;
            $this->controller_url = $this->module_path_name . "/" . $this->controller_name;
            $lang = LanguageModel::getLanguage();
            ModuleModel::language($lang);

            if (empty($this->title_module)) {
                $class = $this->router->class;
                $this->title_module = $this->lang->line($this->router->module . '_' . $this->path_name . '_' . strtolower($class) . '_title', false);
            }

            if (empty($this->title_module)) {
                $rsdb = ModuleModel::getOneByField('path', $this->module_url);
                if (!empty($rsdb)) {
                    $this->title_module = $rsdb['path_description'];
                }
            }

            $data = ModuleModel::getInfo($this->router->module);
            if (isset($data['module_style_site'])) {
                $this->module_style = $data['module_style_site'] . '/';
            }
        }
    }

    protected function view_layout_return(string $view_file = "index", array $data = [])
    {
        $data["path_name"] = $this->path_name;
        $data["module_path_name"] = $this->module_path_name;
        $data["module_url"] = $this->module_url;
        $data["controller_url"] = $this->controller_url;
        $data["controller_name"] = $this->controller_name;
        $data["title_module"] = $this->title_module;
        $view_path_name = $this->path_name;
        return $this->load->view($this->path_name . '/' . AccessCheckModel::viewFile($this->controller_name, $view_path_name, $view_file), $data, true);
    }

    protected function view_layout(string $view_file = "index", array $data = [], string $layoutFileName = 'layout')
    {
        $dataLayout["title"] = null;
        $dataLayout["description"] = null;
        $dataLayout["keywords"] = null;
        $dataLayout["path_name"] = $this->path_name;
        $dataLayout["module_path_name"] = $this->module_path_name;
        $dataLayout["module_url"] = $this->module_url;
        $dataLayout["controller_url"] = $this->controller_url;
        $dataLayout["controller_name"] = $this->controller_name;
        $dataLayout["title_module"] = $this->title_module;

        if (isset($data['title']) && !empty($data['title'])) {
            $dataLayout["title"] = $data["title"];
        }

        if (isset($data['description']) && !empty($data['description'])) {
            $dataLayout["description"] = $data["description"];
        }

        if (isset($data['keywords']) && !empty($data['keywords'])) {
            $dataLayout["keywords"] = $data["keywords"];
        }

        $view_path_name = $this->path_name;
        BreadcrumbModel::setTemplate($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, "breadcrumb"));
        BreadcrumbModel::setData(["name" => "Home", "url" => null, "active_status" => null]);
        $breadcrumbTitle = "Overzicht";
        if (isset($data['title']) && !empty($data['title'])) {
            $breadcrumbTitle = $data["title"];
        }
        BreadcrumbModel::setData(["name" => $breadcrumbTitle, "url" => $this->controller_url, "active_status" => "active"]);
        if (isset($data['breadcrumbData']) && !empty($data['breadcrumbData'])) {
            BreadcrumbModel::setMultidata($data['breadcrumbData']);
        }

        if (isset($data['breadcrumbDataReset']) && !empty($data['breadcrumbDataReset'])) {
            BreadcrumbModel::emptyData();
            BreadcrumbModel::setMultidata($data['breadcrumbDataReset']);
        }
        $data["breadcrumb"] = BreadcrumbModel::show();
        $dataLayout['content'] = $this->view_layout_return($view_file, $data);
        $this->load->view($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, $layoutFileName), $dataLayout);
    }
}

class Back_Controller extends MX_Controller
{
    protected $arr_userdb;
    protected $controller_name;
    protected $controller_url;
    protected $path_name;
    protected $module_path_name;
    protected $module_url = "";
    protected $title_module = '';
    protected $permissionAutoMode = true;
    protected $module_style = '';

    public function __construct()
    {
        parent::__construct();
        AccessCheckModel::maintenance();
        AccessCheckModel::authorized();
        PermissionModel::$autoMode = $this->permissionAutoMode;
        $this->load->database();

        $this->path_name = AccessCheckModel::$backPath;
        $this->controller_name = get_class($this);
        $this->controller_url = $this->path_name . "/" . $this->controller_name;
        UserModel::joinLogin();
        $this->arr_userdb = UserModel::getOneById(LoginModel::userId());
        LanguageModel::setLanguage();
        PermissionModel::checkForUser($this->arr_userdb);
        //is module path
        if (!empty($this->router->module) && $this->router->module !== AccessCheckModel::$backPath) {
            $this->module_url = $this->router->module;
            $this->module_path_name = $this->router->module . "/" . $this->path_name;
            $this->controller_url = $this->module_path_name . "/" . $this->controller_name;
            $lang = LanguageModel::getLanguage();
            ModuleModel::language($lang);

            if (empty($this->title_module)) {
                $class = $this->router->class;
                $this->title_module = $this->lang->line($this->router->module . '_' . AccessCheckModel::$backPath . '_' . strtolower($class) . '_title', false);
            }

            if (empty($this->title_module)) {
                $rsdb = ModuleModel::getOneByField('path', $this->module_url);
                if (!empty($rsdb)) {
                    $this->title_module = $rsdb['path_description'];
                }
            }

            $data = ModuleModel::getInfo($this->module_url);
            if (isset($data['module_style_back'])) {
                $this->module_style = $data['module_style_back'] . '/';
            }
        }
    }

    protected function view_layout_return(string $view_file = "index", array $data = [])
    {
        $data["path_name"] = $this->path_name;
        $data["module_path_name"] = $this->module_path_name;
        $data["module_url"] = $this->module_url;
        $data["controller_url"] = $this->controller_url;
        $data["controller_name"] = $this->controller_name;
        $data["title_module"] = $this->title_module;
        $view_path_name = AccessCheckModel::$backPath;
        return $this->load->view($this->path_name . '/' . AccessCheckModel::viewFile($this->controller_name, $view_path_name, $view_file), $data, true);
    }

    protected function view_layout(string $view_file = "index", array $data = [], string $layoutFileName = 'layout')
    {
        $dataLayout["title"] = c_key('webapp_title');
        $arr_no_open_message = MessageModel::getNoOpenMessage();
        $dataLayout["listdb_message"] = $arr_no_open_message["listdb"];
        $dataLayout["total_new_message"] = $arr_no_open_message["total"];
        $dataLayout["show_total_new_message"] = "d-none";
        if ($dataLayout["total_new_message"] > 0) {
            $dataLayout["show_total_new_message"] = "";
        }

        $dataLayout["path_name"] = $this->path_name;
        $dataLayout["module_path_name"] = $this->module_path_name;
        $dataLayout["module_url"] = $this->module_url;
        $dataLayout["controller_url"] = $this->controller_url;
        $dataLayout["controller_name"] = $this->controller_name;
        $dataLayout["title_module"] = $this->title_module;

        $breadcrumbTitle = $dataLayout["title"];
        if (isset($data['title']) && !empty($data['title'])) {
            $dataLayout["title"] .= ' - ' . $data['title'];
            $breadcrumbTitle = $data['title'];
        }

        HistoryUrlModel::update($breadcrumbTitle);
        $dataLayout["listdb_history_url"] = HistoryUrlModel::getLast();

        $view_path_name = AccessCheckModel::$backPath;

        BreadcrumbModel::setTemplate($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, "breadcrumb"));
        BreadcrumbModel::setData(["name" => lang("back_breadcrumb_home_text"), "url" => LoginModel::redirectUrl()]);
        BreadcrumbModel::setData(["name" => lang("back_breadcrumb_controller_url_text"), "url" => $this->controller_url]);
        if (isset($data['breadcrumbData'])  && !empty($data['breadcrumbData'])) {
            BreadcrumbModel::emptyData();
            BreadcrumbModel::setData(["name" => lang("back_breadcrumb_home_text"), "url" => LoginModel::redirectUrl()]);
            BreadcrumbModel::setMultidata($data['breadcrumbData']);
        }

        if (isset($data['breadcrumbDataReset'])  && !empty($data['breadcrumbDataReset'])) {
            BreadcrumbModel::emptyData();
            BreadcrumbModel::setMultidata($data['breadcrumbDataReset']);
        }

        $breadcrumb = BreadcrumbModel::show();
        if (isset($data['breadcrumb'])) {
            $breadcrumb = $data['breadcrumb'];
        }
        $dataLayout["breadcrumb"] = $breadcrumb;
        $dataLayout["breadcrumbTitle"] = $breadcrumbTitle;

        $event_result_box = GlobalModel::eventResultBox();
        if (isset($data['event_result_box'])) {
            $event_result_box = $data['event_result_box'];
        }
        $dataLayout["event_result_box"] = $event_result_box;

        $dataLayout["asset"] = $this->load->view($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, "asset"), $dataLayout, true);
        $navbarViewFile = UserModel::navbarViewFile($this->arr_userdb);
        $dataLayout["navbar"] = $this->load->view($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, $navbarViewFile), $dataLayout, true);
        $dataLayout["display_info"] = $this->arr_userdb["display_info"];
        $dataLayout['content'] = $this->view_layout_return($view_file, $data);

        if (ModuleModel::isActive('navbar')) {
            $navbarContent =  file_get_contents(site_url('navbar/site/home/display/' . $this->arr_userdb[UserModel::$primaryKey]));
            $dataLayout["navbar"] = empty($navbarContent) ? $dataLayout["navbar"] : $navbarContent;
        }
        $this->load->view($this->module_style . $this->path_name . '/' . AccessCheckModel::viewFile("_global", $view_path_name, $layoutFileName), $dataLayout);
    }
}
