<?php

class Permission extends Ajax_Controller
{

    public function checkForUser()
    {
        $pathurl = CIInput()->post('pathurl');
        if (LoginModel::userId() <= 0) {
            $json["type_done"] = "redirect";
            $json["redirect_url"] = login_url('?redirect_url=' . site_url($pathurl));
            $json["msg"] = anchor($json["redirect_url"], 'Uw sessie is verlopen!');
            $json["status"] = "error";
            exit(json_encode($json));
        }
        if (!UserModel::isSuperUser()) {
            $arr_userdb = UserModel::getOneById(LoginModel::userId());
            $groupIds = $arr_userdb['permission_group_ids'];
            $listdbGroup = PermissionGroupModel::getAllGroup($groupIds);
            $stringCompares = array_column($listdbGroup, 'permission_ids');

            $permisson = $permissionArray = $all_permissions = $permissions = [];
            foreach ($stringCompares as $stringCompare) {
                $permissionArray[] = explode(',', $stringCompare);
            }
            $all_permissions = array_reduce($permissionArray, 'array_merge', []);
            $all_permissions = array_unique($all_permissions);

            if (!empty($all_permissions)) {
                $listdb = PermissionModel::AllByPermissionIds($all_permissions);
                foreach ($listdb as $rsdb) {
                    $path = $rsdb['use_path'] > 0 ? $rsdb['path'] . '/' : null;
                    $permissions[] = $path . AccessCheckModel::$backPath . '/' . $rsdb['object'] . '/' . $rsdb['method'] . config_item('url_suffix');
                }
            }

            $permisson = array_unique($permissions);

            $ckPermission = in_array($pathurl, $permisson, true);
            if (!$ckPermission) {
                $json["msg"] = 'Niet gemachtigd';
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }

        $json["msg"] = 'Gemachtigd';
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
