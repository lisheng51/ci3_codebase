<?php

class User extends API_Controller
{

    public function index()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $dataUser = ApiModel::checkFetchByJWT();
        ApiModel::outOK($dataUser, $apiLogId);
    }

    public function permission()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $userDB = ApiModel::checkFetchByJWT();
        $ids = $userDB["permission_group_ids"];
        $permisson = [];
        if (empty($ids) === false) {
            $listdbGroup = PermissionGroupModel::getAllGroup($ids);
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
                    $path = $rsdb['use_path'] > 0 ? $rsdb['path'] : null;
                    $permissions[] = $path . '.' . $rsdb['object'] . '.' . $rsdb['method'];
                }
            }
            $permisson = array_unique($permissions);
        }
        ApiModel::outOK($permisson, $apiLogId);
    }
}
