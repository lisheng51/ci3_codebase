<?php

class BookMarkModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "bookmark";
        self::$primaryKey = "bookmark_id";
        self::$selectOrderBy = [
            'bookmark_id#desc' => 'ID (aflopend)',
            'path#desc' => 'Path (aflopend)',
            'path#asc' => 'Path (oplopend)',
            'order_list#desc' => 'Volgorde (aflopend)',
            'order_list#asc' => 'Volgorde (oplopend)',
        ];
        self::$usingSoftDel = true;
        self::$sqlOrderBy = ["order_list" => 'asc'];
    }

    public static function selectParent(int $id = 0, int $uid = 0, bool $with_empty = false, string $name = 'parent_bookmark_id'): string
    {
        $userId = $uid > 0 ? $uid : LoginModel::userId();
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if ($id === 0) {
            $id = CIInput()->get($select_name) ?? 0;
        }

        $data[self::$fieldIsDel] = 0;
        $data['is_sort'] = 1;
        $data[UserModel::$primaryKey] = $userId;
        self::$sqlWhere = $data;
        $listdb = self::getAll();
        $select = '<select name=' . $select_name . ' class="form-control">';
        $select .= $with_empty ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }


    public static function getPostdata(): array
    {
        $data["path"] = CIInput()->post("path") ?? "";
        $data["name"] = CIInput()->post("name");
        $data["description"] = CIInput()->post("description");
        $data["icon"] = CIInput()->post("icon");
        $data["open_new"] = CIInput()->post("open_new");
        $data["is_extern"] = CIInput()->post("is_extern");
        $data[UserModel::$primaryKey] = CIInput()->post(UserModel::$primaryKey);
        $data["parent_bookmark_id"] = CIInput()->post("parent_bookmark_id") ?? 0;
        $data["is_sort"]  = CIInput()->post("is_sort");
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }

    private static function autoChildId(string $name = "", string $path = "", int $parentId = 0, int $uid = 0)
    {
        if (empty($name)) {
            return 0;
        }

        $data["is_sort"] = 0;
        $userId = $uid > 0 ? $uid : LoginModel::userId();
        $data[UserModel::$primaryKey] = $userId;
        $data["path"] = $path;
        self::$sqlWhere = $data;
        $exist = self::getOne();
        if (empty($exist)) {
            $data["open_new"] = 0;
            $data["parent_bookmark_id"] = $parentId;
            $data["description"] = "auto update";
            $data["is_extern"] = 0;
            $data["name"] = $name;
            return self::add($data);
        }
        self::edit($exist[self::$primaryKey], $data);
        return $exist[self::$primaryKey];
    }

    private static function autoParentId(string $name = "", int $uid = 0)
    {
        if (empty($name)) {
            return 0;
        }
        $data["name"] = $name;
        $data["is_sort"] = 1;
        $userId = $uid > 0 ? $uid : LoginModel::userId();
        $data[UserModel::$primaryKey] = $userId;
        self::$sqlWhere = $data;
        $exist = self::getOne();
        if (empty($exist)) {
            $data["description"] = "auto update";
            $data["parent_bookmark_id"] = 0;
            $data["is_extern"] = 0;
            $data["open_new"] = 0;
            return self::add($data);
        }
        self::edit($exist[self::$primaryKey], $data);
        return $exist[self::$primaryKey];
    }

    public static function resetAllByPermission(int $id = 0): bool
    {
        $userDB = UserModel::getOneById($id);
        $ids = $userDB["permission_group_ids"];
        $status = false;
        if (empty($ids) === false) {
            $listdbGroup = PermissionGroupModel::getAllGroup($ids);
            $stringCompares = array_column($listdbGroup, 'permission_ids');

            $permissionsNav = $permissionArray = $all_permissions = [];
            foreach ($stringCompares as $stringCompare) {
                $permissionArray[] = explode(',', $stringCompare);
            }
            $all_permissions = array_reduce($permissionArray, 'array_merge', []);
            $all_permissions = array_unique($all_permissions);

            if (empty($all_permissions) === false) {
                $listdb = PermissionModel::AllByPermissionIds($all_permissions);
                foreach ($listdb as $rsdb) {
                    if ($rsdb["has_link"] > 0) {
                        $startPath = $rsdb["use_path"] > 0 ? $rsdb["path"] . '/' : "";
                        $data["url"] = $startPath . $rsdb["link_dir"] . '/' . $rsdb['object'] . '/' . $rsdb['method'];
                        $data["name"] = $rsdb["link_title"];
                        $keyString = $rsdb["path_description"];
                        $permissionsNav[$keyString][] = $data;
                    }
                }
            }

            if (empty($permissionsNav) === false) {
                foreach ($permissionsNav as $name => $arr_menu) :
                    $arr_menu_unique = F_array::unique_multidim($arr_menu, ["name", "url"]);
                    $sortId = self::autoParentId($name, $id);
                    foreach ($arr_menu_unique as $value) :
                        self::autoChildId($value["name"], $value["url"], $sortId, $id);
                    endforeach;

                endforeach;
                $status = true;
            }
        }

        return $status;
    }

    public static function navBarData(int $uid = 0)
    {
        $userId = $uid > 0 ? $uid : LoginModel::userId();
        $data[self::$fieldIsDel] = 0;
        $data[UserModel::$primaryKey] = $userId;
        self::$sqlWhere = $data;
        $arrResult = [];
        $listdb = self::getAll();
        foreach ($listdb as $rs) {
            if ($rs['parent_bookmark_id'] == 0) {
                $rs["navBarDataChild"] = null;
                if ($rs["is_sort"] > 0) {
                    $rs["navBarDataChild"] = self::navBarDataChild($rs[self::$primaryKey], $listdb);
                }
                $arrResult[] = $rs;
            }
        }
        return $arrResult;
    }


    private static function navBarDataChild($parent_id = 0, array $listdb = [])
    {
        $arrResult = [];
        foreach ($listdb as $rs) {
            if ($rs['parent_bookmark_id'] == $parent_id) {
                $rs["target"] = $rs["open_new"] > 0 ? "_blank" : "_self";
                $rs["url"] = $rs["is_extern"] > 0 ? $rs["path"] : site_url($rs["path"]);
                $arrResult[] = $rs;
            }
        }
        return $arrResult;
    }
}
