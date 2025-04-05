<?php

class UserModel
{

    use BasicModel;
    public static $uploadFolder = 'images' . DIRECTORY_SEPARATOR . 'user';
    public static $secureId = 1;

    public static function __constructStatic()
    {
        self::$table = "user";
        self::$primaryKey = "user_id";
        self::$usingSoftDel = true;
        self::$selectOrderBy = [
            'user_id#desc' => 'ID (aflopend)',
            'display_info#desc' => 'Naam (aflopend)',
            'display_info#asc' => 'Naam (oplopend)',
        ];
    }

    public static function isSuperUser(array $userdb = []): bool
    {
        $loginUserId = LoginModel::userId();
        $checkUserId = $loginUserId === self::$secureId;
        if ($checkUserId) {
            return true;
        }

        $arr_userdb = self::getOneById($loginUserId);
        if (!empty($userdb)) {
            $arr_userdb = $userdb;
            $checkUserId = intval($arr_userdb[self::$primaryKey]) === self::$secureId;
            if ($checkUserId) {
                return true;
            }
        }

        $groupIds = $arr_userdb['permission_group_ids'];
        $groupIdsConfig = c_key('_core_default_admin_group_ids');
        if (!empty($groupIdsConfig)) {
            $listOfgroupIds = explode(',', $groupIds);
            $listOfgroupIdsConfig = explode(',', $groupIdsConfig);
            if (count($listOfgroupIdsConfig) > 0) {
                foreach ($listOfgroupIdsConfig as $groupid) {
                    $findStatus = in_array($groupid, $listOfgroupIds);
                    if ($findStatus) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = ''): string
    {
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = $where;
        self::joinLogin();
        $listdb = self::getAll();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["username"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }


    public static function makeDir()
    {
        $path = FCPATH . UploadModel::$rootFolder . DIRECTORY_SEPARATOR . self::$uploadFolder;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function getPostdata(): array
    {
        $data["display_info"] = CIInput()->post("display_info");
        $data["phone"] = CIInput()->post("phone");
        $data["nav_bookmark"] = CIInput()->post("nav_bookmark");
        return $data;
    }

    public static function navbarViewFile(array $userDB = [])
    {
        $viewFile = 'navbar';
        if (self::isSuperUser($userDB)) {
            $viewFile = "navbar_master";
        }

        if ($userDB["nav_bookmark"] > 0) {
            $viewFile = "navbar_bookmark";
        }

        return $viewFile;
    }

    public static function secureId(int $id = 1)
    {
        if (LoginModel::userId() !== self::$secureId && $id === self::$secureId) {
            showError();
        }
    }

    public static function joinLogin()
    {
        self::$sqlSelect =
            [
                [LoginModel::$table => 'username'],
                [LoginModel::$table => 'password_date'],
                [LoginModel::$table => 'password'],
                [LoginModel::$table => 'with_access_code'],
                [LoginModel::$table => 'access_code'],
                [LoginModel::$table => 'access_code_date'],
                [LoginModel::$table => 'password_reset_date'],
                [LoginModel::$table => 'redirect_url'],
                [LoginModel::$table => 'with_2fa'],
                [LoginModel::$table => '2fa_secret']
            ];

        self::$sqlJoin =
            [
                [LoginModel::$table => LoginModel::$primaryKey]
            ];
    }

    public static function display(int $uid = 0): string
    {
        $arr_user = self::getOneById($uid);
        if (empty($arr_user)) {
            return "Systeem";
        }
        return $arr_user["display_info"];
    }
}
