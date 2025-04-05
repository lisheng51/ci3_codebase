<?php

class AccessCheckModel
{
    public static $backPath = "back";
    public static function ipAllow(array $allowIps = ['::1', '127.0.0.1']): bool
    {
        $ip = CIInput()->ip_address();
        $status = in_array($ip, $allowIps);
        return $status;
    }

    public static function maintenance(string $filename = 'maintenance.txt')
    {
        $defaultpath = self::$backPath;
        $ck_file_exist_path = APPPATH . $filename;
        $out = '';
        if (!empty(CIRouter()->module) && CIRouter()->module !== $defaultpath) {
            $modulesName = CIRouter()->module;
            $ck_file_exist_path = FCPATH. 'modules' . DIRECTORY_SEPARATOR . $modulesName . DIRECTORY_SEPARATOR . $filename;
        }

        if (file_exists($ck_file_exist_path)) {
            $out = CILoader()->file($ck_file_exist_path, true);
        }

        if (!empty($out)) {
            $data["msg"] = $out;
            exit(CILoader()->view('_share/global/maintenance', $data, true));
        }
    }

    public static function permissionGroup(array $ckPermissionGroupIds = [])
    {
        self::authorized();
        $userdb = UserModel::getOneById(LoginModel::userId());
        if (empty($userdb)) {
            showError(404);
        }

        $arrayIds = explode(',', $userdb["permission_group_ids"]);
        if (empty($arrayIds)) {
            showError();
        }

        $result = array_diff($ckPermissionGroupIds, $arrayIds);
        if (!empty($result)) {
            showError();
        }
    }

    public static function authorized()
    {
        if (CIInput()->post() && LoginModel::userId() <= 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = login_url('?redirect_url=' . current_url());
            $json["msg"] = anchor($json["redirect_url"], 'Uw sessie is verlopen!');
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (LoginModel::userId() <= 0) {
            redirect(login_url('?redirect_url=' . current_url()));
        }
    }

    public static function viewFile(string $controller_name = "", string $view_path_name = "", string $filename = "")
    {
        $defaultpath = $view_path_name;
        $controller = lcfirst($controller_name);
        $viewFile = "$controller/$filename";

        $defaultCk1 = APPPATH . 'views' . DIRECTORY_SEPARATOR . $defaultpath;
        if (!empty(CIRouter()->module) && CIRouter()->module !== $defaultpath) {
            $modulesName = CIRouter()->module;
            $defaultCk1 = FCPATH. 'modules' . DIRECTORY_SEPARATOR . $modulesName . 'views' . DIRECTORY_SEPARATOR . $defaultpath;
        }

        if ($defaultpath !== self::$backPath) {
            $modulesName = CIRouter()->module;
            $defaultCk1 = FCPATH. 'modules' . DIRECTORY_SEPARATOR . $modulesName . 'views' . DIRECTORY_SEPARATOR . self::$backPath . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $view_path_name . DIRECTORY_SEPARATOR . "$filename.php";
            if (file_exists($defaultCk1)) {
                return "$controller/$view_path_name/$filename";
            }
        }

        $defaultCk2 = DIRECTORY_SEPARATOR . "$filename.php";
        $defaultCkFolderController = DIRECTORY_SEPARATOR . $controller;
        $ck_file_exist_path = $defaultCk1 . $defaultCkFolderController . $defaultCk2;
        if (file_exists($ck_file_exist_path)) {
            $viewFile = "$controller/$filename";
        }

        return $viewFile;
    }

    public static function redirectUrl(): string
    {
        $redirect_url = CIInput()->get('redirect_url');
        if (!empty($redirect_url)) {
            $parse = parse_url($redirect_url);
            if ($_SERVER['HTTP_HOST'] !== $parse['host']) {
                return site_url();
            }
            return $redirect_url;
        }

        $session_data = CISession()->userdata('redirect_url');
        if (!empty($session_data) && $session_data !== null) {
            CISession()->set_userdata('redirect_url', null);
            $parse = parse_url($session_data);
            if ($_SERVER['HTTP_HOST'] !== $parse['host']) {
                return site_url();
            }
            return $session_data;
        }

        return site_url(LoginModel::redirectUrl());
    }

    public static function backUrl(string $backUrl = "")
    {
        if (!empty($backUrl)) {
            CISession()->set_userdata('back_url', $backUrl);
            return site_url($backUrl);
        }

        $session_data = CISession()->userdata('back_url');
        if (!empty($session_data)) {
            CISession()->unset_userdata('back_url');
            return site_url($session_data);
        }
        return CIAgent()->referrer();
    }

    public static function startRedirect(string $defaultUrl = "")
    {
        if (!empty($defaultUrl)) {
            redirect($defaultUrl);
        }

        CILoader()->database();
        $domainRedirect = c_key('webapp_domain_redirect') ?? "";
        if (!empty($domainRedirect)) {
            $domeinList = explode(',', $domainRedirect);
            if (!empty($domeinList)) {
                $httphost = $_SERVER['HTTP_HOST'];
                foreach ($domeinList as $value) {
                    $domeinRedirectList = explode('@', $value);
                    if (current($domeinRedirectList) === $httphost) {
                        redirect(end($domeinRedirectList));
                    }
                }
            }
        }

        $defaultRedirect = c_key('webapp_default_url') ?? "";
        if (!empty($defaultRedirect)) {
            redirect($defaultRedirect);
        }
    }
}
