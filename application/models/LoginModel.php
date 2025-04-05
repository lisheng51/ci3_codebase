<?php

class LoginModel
{
    use BasicModel;

    public static $webappAccessCode = "";
    public static $loginUserData = [];
    public static $sessionKey = "login";

    public static function __constructStatic()
    {
        self::$primaryKey = "user_id";
        self::$table = 'login';
    }

    public static function userId(): int
    {
        $session_data = CISession()->userdata(self::$sessionKey);
        if (isset($session_data[self::$primaryKey])) {
            return intval($session_data[self::$primaryKey]);
        }
        return 0;
    }

    public static function permission(): array
    {
        $session_data = CISession()->userdata(self::$sessionKey);
        if (isset($session_data['permission'])) {
            return $session_data['permission'];
        }
        return [];
    }

    public static function redirectUrl(): string
    {
        $session_data = CISession()->userdata(self::$sessionKey);
        if (!empty($session_data['redirect_url'])) {
            return $session_data['redirect_url'];
        }
        return AccessCheckModel::$backPath . '/home';
    }

    public static function joinUser()
    {
        self::$sqlSelect =
            [
                [UserModel::$table => 'display_info'],
                [UserModel::$table => 'emailaddress'],
                [UserModel::$table => 'phone'],
                [UserModel::$table => 'permission_group_ids'],
                [UserModel::$table => 'is_active'],
                [UserModel::$table => 'is_del'],
                [UserModel::$table => 'nav_bookmark']
            ];

        self::$sqlJoin =
            [
                [UserModel::$table => UserModel::$primaryKey]
            ];
    }

    public static function find(string $username = "", string $password = ""): array
    {
        self::joinUser();
        $where['username'] = $username;
        self::$sqlWhere = $where;

        $rs = self::getOne();
        if (empty($rs)  || $rs["is_active"] == 0 || $rs["is_del"] == 1) {
            return [];
        }

        if (password_verify($password, $rs["password"])) {
            return $rs;
        }
        return [];
    }

    public static function check2FA(array $arr_rs = [], string $oneCode = ""): bool
    {
        if (empty($arr_rs)) {
            return false;
        }

        if ($arr_rs["with_2fa"] > 0 && empty($arr_rs["2fa_secret"]) === false) {
            $checkResult = TwoFactorAuthenticator::verifyCode($arr_rs["2fa_secret"], $oneCode);
            return $checkResult;
        }

        return true;
    }

    public static function show2FAQrcode(string $string_2fa_secret = ""): string
    {
        $secret = TwoFactorAuthenticator::createSecret();
        if (!empty($string_2fa_secret)) {
            $secret = $string_2fa_secret;
        }

        $qrCodeUrl = TwoFactorAuthenticator::getQRCodeGoogleUrl(site_url(), $secret, c_key('webapp_title'));
        CISession()->set_userdata('2fa_secret', $secret);
        return $qrCodeUrl;
    }

    public static function check2FAStatus(int $uid = 0, string $oneCode = ""): bool
    {
        if (empty($oneCode) === false && $uid > 0) {
            $secret = CISession()->userdata('2fa_secret');
            $checkResult = TwoFactorAuthenticator::verifyCode($secret, $oneCode);
            return $checkResult;
        }

        return false;
    }

    public static function SetLoginUserData(string $username = "", string $password = ""): bool
    {
        $arr_rs = self::find($username, $password);
        if (empty($arr_rs)) {
            return false;
        }

        if (!empty($arr_rs["access_code_date"]) && date("Y-m-d H:i:s") > $arr_rs["access_code_date"]) {
            return false;
        }

        if (!empty($arr_rs["access_code"]) && self::$webappAccessCode !== $arr_rs["access_code"]) {
            return false;
        }
        self::$loginUserData = $arr_rs;
        return true;
    }

    public static function logout()
    {
        if (self::userId() > 0) {
            CISession()->sess_destroy();
        }
    }

    public static function addSession()
    {
        $arr_rs = self::$loginUserData;
        if (!empty($arr_rs)) {
            $sess_array[UserModel::$primaryKey] = $arr_rs[UserModel::$primaryKey];
            $sess_array['redirect_url'] = $arr_rs["redirect_url"];
            $sess_array['permission'] = self::setPermission($arr_rs["permission_group_ids"] ?? '');
            CISession()->set_userdata(self::$sessionKey, $sess_array);

            $data["access_code"] = null;
            $data["access_code_date"] = null;
            self::edit($arr_rs[UserModel::$primaryKey], $data);
            LoginHistoryModel::insert($arr_rs[UserModel::$primaryKey]);
        }
    }

    private static function setPermission(string  $permission_group_ids = ''): array
    {
        if (empty($permission_group_ids)) {
            return [];
        }

        $listdbGroup = PermissionGroupModel::getAllGroup($permission_group_ids);
        $stringCompares = array_column($listdbGroup, 'permission_ids');

        $permissionArray = $all_permissions = $permissions = [];
        foreach ($stringCompares as $stringCompare) {
            $permissionArray[] = explode(',', $stringCompare);
        }
        $all_permissions = array_reduce($permissionArray, 'array_merge', []);
        $all_permissions = array_unique($all_permissions);

        if (!empty($all_permissions)) {
            $listdb = PermissionModel::AllByPermissionIds($all_permissions);
            foreach ($listdb as $rsdb) {
                $path = $rsdb['use_path'] > 0 ? $rsdb['path'] : AccessCheckModel::$backPath;
                $permissions[] = strtolower(trim($path . '.' . $rsdb['object'] . '.' . $rsdb['method']));
            }
        }
        return $permissions;
    }

    public static function updateAccessCodeData(int $user_id = 0, int $min = 15): array
    {
        if ($user_id <= 0) {
            return [];
        }
        $data["access_code"] = F_string::random(6);
        $data["access_code_date"] = date('Y-m-d H:i:s', strtotime('+' . $min . ' minutes'));
        self::edit($user_id, $data);
        return $data;
    }

    public static function checkPasswordDate(string $password_date = ""): bool
    {
        if (intval(c_key('webapp_ck_pass_unuse_day')) > 0) {
            $webapp_ck_pass_unuse_day = c_key('webapp_ck_pass_unuse_day');
            $webapp_ck_pass_notify_day = c_key("webapp_ck_pass_notify_day") > 0 ? c_key("webapp_ck_pass_notify_day") : 10;
            $day_msg = $webapp_ck_pass_unuse_day - $webapp_ck_pass_notify_day;
            $days_ago_no_login = date('Y-m-d H:i:s', strtotime("-$webapp_ck_pass_unuse_day days"));
            $days_ago_msg = date('Y-m-d H:i:s', strtotime("-$day_msg days"));
            if ($password_date <= $days_ago_msg || $password_date <= $days_ago_no_login) {
                return false;
            }
        }

        return true;
    }

    public static function updatePasswordResetDate(int $user_id = 0): array
    {
        $data["password_reset_date"] = date('Y-m-d H:i:s');
        if ($user_id > 0) {
            $webapp_ck_pass_reset_hour = c_key("webapp_ck_pass_reset_hour") > 0 ? c_key("webapp_ck_pass_reset_hour") : 8;
            $much_strtotime = '+' . $webapp_ck_pass_reset_hour . ' hours';
            $data["password_reset_date"] = date('Y-m-d H:i:s', strtotime($much_strtotime));
            self::edit($user_id, $data);
        }
        return $data;
    }
}
