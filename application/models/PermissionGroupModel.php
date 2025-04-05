<?php

class PermissionGroupModel
{

    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "permission_group";
        self::$primaryKey = "permission_group_id";
        self::$sqlOrderBy = ['sort_list_group' => "asc"];
        self::$selectOrderBy = [
            'permission_group_id#desc' => 'ID (aflopend)',
            'sort_list_group#asc' => 'Volgorde (oplopend)',
            'name#desc' => 'Naam (aflopend)',
            'name#asc' => 'Naam (oplopend)',
            'permission_group_type_id#desc' => 'Type (aflopend)',
            'permission_group_type_id#asc' => 'Type (oplopend)',
        ];
    }

    public static function allByTypeId(int $type_id = 1)
    {
        $where[self::$fieldIsDel] = 0;
        $where[PermissionGroupTypeModel::$primaryKey] = $type_id;
        self::$sqlWhere = $where;
        self::$sqlOrderBy = ['sort_list_group' => 'asc'];
        $listdb = self::getAll();
        return $listdb;
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = '', int $type_id = 1): string
    {
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }
        $listdb = self::allByTypeId($type_id);
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectMultiple(array $haystack = [], string $name = '', int $type_id = 1): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($haystack)) {
            $haystack = CIInput()->get(self::$primaryKey) ?? [];
            if (!is_array($haystack)) {
                $haystack = [$haystack];
            }
        }
        $listdb = self::allByTypeId($type_id);
        $select = '<select name="' . $select_name . '[]" class="form-control" multiple>';
        foreach ($listdb as $rs) {
            $ckk = (!empty($haystack) && in_array($rs[self::$primaryKey], $haystack)) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function getPostdata(): array
    {
        $data["name"] = CIInput()->post("name");
        $data[PermissionGroupTypeModel::$primaryKey] = CIInput()->post(PermissionGroupTypeModel::$primaryKey);
        $permission_ids = CIInput()->post(PermissionModel::$primaryKey);
        if (empty($permission_ids)) {
            $json["msg"] = 'Toestemming is leeg!';
            $json["status"] = "error";
            exit(json_encode($json));
        }
        $data["is_lock"] = CIInput()->post("is_lock") ?? 0;
        $data["permission_ids"] = implode(',', $permission_ids);
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }

    public static function getAllGroup(string $string = ""): array
    {
        if (empty($string)) {
            return [];
        }
        $array_in = explode(',', $string);
        self::$sqlWhereIn = [self::$primaryKey => $array_in];
        $result = self::getAll();
        self::$sqlWhereIn = [];
        return $result;
    }

    public static function allByPermissionId(int $permissionId = 0): array
    {
        $where[self::$fieldIsDel] = 0;
        $where["FIND_IN_SET($permissionId, permission_ids)"] = null;
        self::$sqlWhere = $where;
        $result = self::getAll();
        return $result;
    }

    public static function hasPermission(array $item = [], int $permissionId = 0): bool
    {
        if (empty($item['permission_ids'])) {
            return false;
        }

        $nowValue = explode(',', $item['permission_ids']);
        if (empty($nowValue)) {
            return false;
        }

        $key = array_search($permissionId, $nowValue);
        if (is_numeric($key)) {
            return true;
        }

        return false;
    }

    public static function updateOnePermission(int $gid = 0, int $permissionId = 0): bool
    {
        $item = self::getOneById($gid);
        if (empty($item) || $permissionId <= 0) {
            return false;
        }

        $now = strval($permissionId);
        $source = $item['permission_ids'] ?? '';
        $newValue = self::comparePermissionIds($source, $now);
        $data["permission_ids"] = implode(',', $newValue);
        return self::edit($item[self::$primaryKey], $data);
    }

    public static function comparePermissionIds(string $source = '', string $now = ''): array
    {
        $sourceValue = explode(',', $source);
        $nowValue = explode(',', $now);
        $resultIn = array_diff($nowValue, $sourceValue);
        $resultOut = array_intersect($sourceValue, $nowValue);
        if (!empty($resultOut)) {
            foreach ($resultOut as $int) {
                $key = array_search($int, $sourceValue);
                if (is_numeric($key)) {
                    unset($sourceValue[$key]);
                }
            }
        }

        if (!empty($resultIn)) {
            $sourceValue = array_merge($sourceValue, $resultIn);
        }
        return $sourceValue;
    }
}
