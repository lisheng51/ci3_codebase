<?php

class User extends Back_Controller
{

    protected $title_module = 'Account';

    #[DisplayAttribute('Account wisselen', '')]
    public function switchUser(int $userId = 0)
    {
        if (!UserModel::isSuperUser()) {
            redirect($this->controller_url);
        }

        UserModel::joinLogin();
        $arr_user = UserModel::getOneById($userId);
        if (empty($arr_user)) {
            redirect($this->controller_url);
        }

        if ($arr_user["is_active"] == 0 || $arr_user["is_del"] == 1) {
            redirect($this->controller_url);
        }

        LoginModel::$loginUserData = $arr_user;
        LoginModel::addSession();
        redirect(AccessCheckModel::redirectUrl());
    }

    #[DisplayAttribute('Account updateBypermissionGroup', '')]
    public function updateBypermissionGroup()
    {
        $id = CIInput()->post(UserModel::$primaryKey) ?? 0;
        if ($id <= 0) {
            $json["msg"] = $this->title_module . ' is niet gevonden!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $status = BookMarkModel::resetAllByPermission($id);
        if ($status === true) {
            $json["msg"] = 'Bookmark als navbar is bijgewerkt!';
            $json["status"] = "good";
            exit(json_encode($json));
        }
    }

    #[DisplayAttribute('Account profiel', 'Profiel')]
    public function profile()
    {
        if (UserModel::isSuperUser()) {
            redirect($this->controller_url . "/edit/" . $this->arr_userdb[UserModel::$primaryKey]);
        }
        if (CIInput()->post()) {
            $this->profileAction();
        }
        $rsdb = $this->arr_userdb;
        $data["title"] = $this->title_module . ' wijzigen';
        $data['rsdb'] = $rsdb;
        $data["edit_email_url"] = site_url($this->controller_url . "/edit_email");
        $data["select_2fa_status"] = select_boolean('with_2fa', intval($rsdb["with_2fa"] ?? 0));
        $data['breadcrumb'] = "";
        $this->view_layout("profile", $data);
    }

    #[DisplayAttribute('Account overzicht', 'Overzicht')]
    public function index()
    {
        //GlobalModel::redirectWithPageNumber();
        GlobalModel::savePostGet();
        $data_where[] = [UserModel::$table . '.' . UserModel::$fieldIsDel => 0];
        $data_where[] = [UserModel::$table . '.' . UserModel::$primaryKey . ' NOT IN (' . UserModel::$secureId . ')' => 'value_is_null'];
        $data_where[] = setFieldAndOperator(PermissionGroupModel::$primaryKey, UserModel::$table . '.permission_group_ids',  loadPostGet(PermissionGroupModel::$primaryKey, 'array'), false, loadPostGet(PermissionGroupModel::$primaryKey . '_operator'));
        $data_where[] = setFieldAndOperator('search_email', UserModel::$table . '.emailaddress', loadPostGet('search_email'), false, loadPostGet('search_email_operator'));
        $data_where[] = setFieldAndOperator('search_name', UserModel::$table . '.name', loadPostGet('search_name'), false, loadPostGet('search_name_operator'));

        $is_active = CIInput()->post_get("is_active") ?? loadPostGet('is_active');
        if ($is_active != "") {
            $is_active = intval($is_active);
            $data_where[] = [UserModel::$table . '.' . "is_active" => $is_active];
        }

        UserModel::$sqlWhere = setSqlWhere($data_where);
        UserModel::$sqlOrderBy = setFieldOrderBy();

        $total = UserModel::getTotal();
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
        $listdb = UserModel::getList($limit, $page);
        foreach ($listdb as $rs) {
            $rs['permissionGroupDb'] = PermissionGroupModel::getAllGroup($rs['permission_group_ids'] ?? "");
            $rs["editButton"] = editButton($this->controller_name . '.edit', $this->controller_url . '/edit/' . $rs[UserModel::$primaryKey]);
            $arr_result[] = $rs;
        }

        return $arr_result;
    }

    #[DisplayAttribute('Account verwijderen', '')]
    public function del()
    {
        $id = CIInput()->post("del_id");
        $rsdb = UserModel::getOneById(intval($id));
        if (empty($rsdb) === true || $id === UserModel::$secureId || $id == $this->arr_userdb["user_id"]) {
            $json["msg"] = $this->title_module . ' kan niet worden verwijderd!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_del"] = 1;
        $data["is_active"] = 0;
        UserModel::edit($id, $data);

        $json["msg"] = $this->title_module . ' is verwijderd!';
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url);
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Account aanmaken', 'Aanmaken')]
    public function add()
    {
        if (CIInput()->post()) {
            $this->addAction();
        }
        $data["title"] = $this->title_module . ' toevoegen';
        $data['rsdb'] = null;
        $data["delButton"] = null;
        $data["select_multiple_permissionGroup"] = PermissionGroupModel::selectMultiple();
        $data["select_2fa_status"] = null;
        $this->view_layout("edit", $data);
    }

    private function addAction()
    {
        $data = $this->getPostdata();
        LoginModel::joinUser();
        $check_email = LoginModel::getOneByField('username', $data["emailaddress"]);
        if (empty($check_email) === false && $check_email[UserModel::$fieldIsDel] > 0) {
            $data_reset[UserModel::$fieldIsDel] = 0;
            UserModel::edit($check_email[UserModel::$primaryKey], $data_reset);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            //SendMailModel::userActive($check_email);
            exit(json_encode($json));
        }

        if (empty($check_email) === false) {
            $json["msg"] = "Het emailadres bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $uid = UserModel::add($data);
        if ($uid > 0) {
            $data_login["user_id"] = $uid;
            $data_login["username"] = $data["emailaddress"];
            $data_login["redirect_url"] = CIInput()->post("redirect_url");
            $data_login["password_date"] = date('Y-m-d H:i:s');
            $password = CIInput()->post("password") ?? "";
            $data_login["password"] = password_hash($password, PASSWORD_DEFAULT);
            if (CIInput()->post("with_access_code") == "on") {
                $data_login["with_access_code"] = 1;
            }
            LoginModel::add($data_login);
            UploadTypeModel::makeDir($uid);
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            $json["msg"] = $this->title_module . ' is toegevoegd!';
            $json["status"] = "good";
            add_app_log($json["msg"]);
            //SendMailModel::userActive(UserModel::get_one_by_id($uid));
            exit(json_encode($json));
        }
    }

    private function getPostdata()
    {
        if (CIInput()->post("del_id")) {
            $this->del();
        }
        $data = UserModel::getPostdata();
        $data["emailaddress"] = strtolower(CIInput()->post("emailaddress"));
        $data["is_active"] = CIInput()->post("is_active");
        AjaxckModel::value('email', $data["emailaddress"]);

        $permission_group_ids = CIInput()->post("permission_group_id");
        if (empty($permission_group_ids) === true) {
            $json["msg"] = 'Toestemming groep is leeg!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data["permission_group_ids"] = implode(',', $permission_group_ids);
        return $data;
    }

    #[DisplayAttribute('Account wijzigen', '')]
    public function edit($id = 0)
    {
        UserModel::secureId(intval($id));
        if (CIInput()->post()) {
            $this->editAction();
        }

        $rsdb = UserModel::getOneById(intval($id));
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        $data['rsdb'] = $rsdb;
        $data["delButton"] = delButton($this->controller_name . '.del', $id);
        $data["select_multiple_permissionGroup"] = PermissionGroupModel::selectMultiple(explode(',', $rsdb["permission_group_ids"] ?? ''));
        $data["select_2fa_status"] = select_boolean('with_2fa', intval($rsdb["with_2fa"] ?? 0));
        $data["title"] = $this->title_module . ' wijzigen';
        $this->view_layout("edit", $data);
    }

    private function editAction()
    {
        $uid = CIInput()->post("user_id");
        $password = CIInput()->post("password");
        $data = $this->getPostdata();
        $check_email = LoginModel::getOneByField('username', $data["emailaddress"]);
        UserModel::joinLogin();
        $rsdb = UserModel::getOneById($uid);
        if (($data["emailaddress"] !== $rsdb["emailaddress"]) && empty($check_email) === false) {
            $json["msg"] = "Het emailadres bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data_login["username"] = $data["emailaddress"];
        if (empty($password) === false) {
            if (CIInput()->post("with_access_code") == "on") {
                $data_login["with_access_code"] = 1;
            }
            if (password_verify($password, $rsdb["password"])) {
                $json["msg"] = "Wachtwoord mag niet hetzelfde zijn als de vorige!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
            $data_login["password_date"] = date('Y-m-d H:i:s');
            $data_login["password"] = password_hash($password, PASSWORD_DEFAULT);
        }

        $data_login["redirect_url"] = CIInput()->post("redirect_url");
        $data_login["with_2fa"] = 0;

        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && empty(CIInput()->post("webapp_one_code")) === true) {
            $json["msg"] = "2fa code is leeg!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $resultStatus2fa = LoginModel::check2FAStatus($uid, CIInput()->post("webapp_one_code"));
        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && $resultStatus2fa === false) {
            $json["msg"] = "2fa code is niet juist!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && $resultStatus2fa === true) {
            $data_login["with_2fa"] = 1;
            $data_login["2fa_secret"] = $this->session->userdata('2fa_secret');
        }

        if ($rsdb["with_2fa"] > 0 && CIInput()->post("with_2fa") > 0) {
            $data_login["with_2fa"] = 1;
        }

        LoginModel::edit($uid, $data_login);
        if (!empty($rsdb)) {
            UserModel::edit($uid, $data);
            $json["msg"] = $this->title_module . ' is bijgewerkt!';
            $json["status"] = "good";
            $json["type_done"] = "redirect";
            $json["redirect_url"] = site_url($this->controller_url);
            add_app_log($json["msg"]);
            exit(json_encode($json));
        }
    }

    private function profileAction()
    {
        $rsdb = $this->arr_userdb;
        $data = UserModel::getPostdata();
        $password = CIInput()->post("password");
        $uid = $rsdb["user_id"];
        if (empty($password) === false) {
            $old_password = CIInput()->post("old_password");
            if (empty($rsdb["password"]) === false && password_verify($old_password, $rsdb["password"]) === false) {
                $json["msg"] = "Uw oud wachtwoord is niet corret!";
                $json["status"] = "error";
                exit(json_encode($json));
            }
            AjaxckModel::password($password);
            $data_login["password"] = password_hash($password, PASSWORD_DEFAULT);
        }

        $data_login["with_2fa"] = 0;
        $data_login["redirect_url"] = CIInput()->post("redirect_url");
        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && empty(CIInput()->post("webapp_one_code")) === true) {
            $json["msg"] = "2fa code is leeg!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $resultStatus2fa = LoginModel::check2FAStatus($uid, CIInput()->post("webapp_one_code"));
        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && $resultStatus2fa === false) {
            $json["msg"] = "2fa code is niet juist!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if ($rsdb["with_2fa"] == 0 && CIInput()->post("with_2fa") > 0 && $resultStatus2fa === true) {
            $data_login["with_2fa"] = 1;
            $data_login["2fa_secret"] = $this->session->userdata('2fa_secret');
        }

        if ($rsdb["with_2fa"] > 0 && CIInput()->post("with_2fa") > 0) {
            $data_login["with_2fa"] = 1;
        }

        LoginModel::edit($uid, $data_login);
        UserModel::edit($uid, $data);
        $json["msg"] = "Profiel is bijgewerkt! ";
        $json["status"] = "good";
        $json["type_done"] = "redirect";
        $json["redirect_url"] = site_url($this->controller_url . '/profile');
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    #[DisplayAttribute('Account email wijzigen', 'Email wijzigen')]
    public function edit_email()
    {
        if (CIInput()->post()) {
            $this->edit_email_action();
        }

        $data["title"] = "Email wijzigen";
        $data["rsdb"] = $this->arr_userdb;
        $data['breadcrumb'] = "";
        $this->view_layout("edit_email", $data);
    }

    private function edit_email_action()
    {
        $edit_email_code = CIInput()->post("edit_email_code");
        $data["emailaddress"] = CIInput()->post("emailaddress");
        $password = CIInput()->post("password");
        $rsdb = $this->arr_userdb;
        if (empty($rsdb["password"])) {
            $json["msg"] = "Uw moet eerste het wachtwoord instellen";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        AjaxckModel::value('wachtwoord', $password);
        AjaxckModel::value('email', $data["emailaddress"]);
        if (password_verify($password, $rsdb["password"]) === FALSE) {
            $json["msg"] = "Uw wachtwoord is niet corret!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $check_email = LoginModel::getOneByField('username', $data["emailaddress"]);
        if (empty($check_email) === false) {
            $json["msg"] = "Het emailadres bestaat al!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (empty($edit_email_code) === true) {
            $this->send_edit_email_code($rsdb, $data["emailaddress"]);
        }

        $code = $this->session->userdata('edit_email_code');
        if ($code !== $edit_email_code) {
            $json["msg"] = "Code is niet corret! ";
            $json["status"] = "error";
            exit(json_encode($json));
        }
        UserModel::edit($rsdb["user_id"], $data);
        $data_login["username"] = $data["emailaddress"];
        LoginModel::edit($rsdb["user_id"], $data_login);
        $this->session->unset_userdata('edit_email_code');
        $json["msg"] = "Email is bijgewerkt! ";
        $json["status"] = "good";
        $json["type_done"] = "change_label";
        $json["input_html"] = '<span class="label label-success">Email is bijgewerkt!</span>';
        add_app_log($json["msg"]);
        exit(json_encode($json));
    }

    private function send_edit_email_code($arr_user = [], $email = "")
    {
        if (empty($arr_user) || empty($email)) {
            return;
        }

        SendMailModel::editEmailCode($arr_user, $email);

        $json["msg"] = "Uw email validatiecode is naar uw mailbox gestuurd, Voert uw code in:";
        $json["status"] = "good";
        $json["type_done"] = "show_form_input";  //
        $json["input_html"] = ' <div class="input-group-prepend"><span class="input-group-text">Email validatiecode:</span></div><input type="text" class="form-control" name="edit_email_code" required>';  //style="text-transform: uppercase" 
        exit(json_encode($json));
    }
}
