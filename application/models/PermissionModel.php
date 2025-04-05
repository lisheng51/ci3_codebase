<?php

class PermissionModel
{
    use BasicModel;
    public static $permisson = [];
    public static $sessionKey = "permission";
    public static $autoMode = false;

    public static function __constructStatic()
    {
        self::$table = "permission";
        self::$primaryKey = "permission_id";
        self::$selectOrderBy = [
            'permission_id#desc' => 'ID (aflopend)',
            'order_num#asc' => 'Volgorde (oplopend)',
            'description#desc' => 'Beschrijving (aflopend)',
            'description#asc' => 'Beschrijving (oplopend)',
        ];
    }


    public static function getPostdata(): array
    {
        $data["description"] = CIInput()->post("description");
        $data["method"] = CIInput()->post("method");
        $data["object"] = CIInput()->post("object");
        $data[ModuleModel::$primaryKey] = CIInput()->post(ModuleModel::$primaryKey);
        $data["link_title"] = CIInput()->post("link_title");
        $data["link_dir"] = CIInput()->post("link_dir");
        $data["order_num"] = CIInput()->post("order_num");
        $data["parent_id"] = CIInput()->post("parent_id");
        $data['has_link'] = empty($data['link_title']) ? 0 : 1;
        if (empty($data["description"])) {
            $data["description"] = $data[ModuleModel::$primaryKey] . '.' . $data["link_dir"] . '.' . $data["object"] . '.' . $data["method"];
        }

        AjaxckModel::value('method', $data["method"]);
        AjaxckModel::value(ModuleModel::$primaryKey, $data[ModuleModel::$primaryKey]);
        AjaxckModel::value('object', $data["object"]);
        AjaxckModel::value('link_dir', $data["link_dir"]);
        return $data;
    }

    public static function fetchExist(array $formData = [], int $editId = 0): array
    {
        $ckData["method"] = $formData["method"];
        $ckData["object"] = $formData["object"];
        $ckData["link_dir"] = $formData["link_dir"];
        $ckData[ModuleModel::$primaryKey] = $formData[ModuleModel::$primaryKey];
        $ckData[self::$primaryKey . ' != '] = $editId;
        self::$sqlWhere = $ckData;
        $find = PermissionModel::getOne();
        return $find;
    }

    public static function checkHas(string $ck_permission = "", string $setPath = ""): bool
    {
        if (!ENVIRONMENT_PERMISSION_CHECK) {
            return true;
        }

        if (UserModel::isSuperUser()) {
            return true;
        }
        $path = AccessCheckModel::$backPath;
        if (!empty(CIRouter()->module) && CIRouter()->module !== $path) {
            $path = CIRouter()->module;
        }

        if (!empty($setPath)) {
            $path = $setPath;
        }

        $ck_permissions = strtolower(trim($path . '.' . $ck_permission));
        $search_this = [$ck_permissions];
        $ck = !array_diff($search_this, self::$permisson);
        return $ck;
    }

    private static function checkMethod()
    {
        $class = CIRouter()->class;
        $mvcMethodNow = CIRouter()->method;
        $reflectionClass = new ReflectionClass($class);
        $method = $reflectionClass->getMethod($mvcMethodNow);

        $attributes = $method->getAttributes(PermissionAttribute::class);
        if (count($attributes) > 0) {
            $attribute = end($attributes);
            $ck_permissions = $attribute->newInstance()->value;
            $search_this = array_map('trim', array_map('strtolower', explode(',', $ck_permissions)));
            $containsAllValues = !array_diff($search_this, self::$permisson);
            return $containsAllValues;
        }
    }

    public static function checkForUser(array $userdb = [])
    {
        if (!ENVIRONMENT_PERMISSION_CHECK) {
            return;
        }

        if (UserModel::isSuperUser()) {
            return;
        }

        self::setForUser($userdb['permission_group_ids'] ?? '');
        $bycheckAuto = true;
        if (self::$autoMode) {
            $path = AccessCheckModel::$backPath;
            if (!empty(CIRouter()->module) && CIRouter()->module !== $path) {
                $path = CIRouter()->module;
            }
            $class = CIRouter()->class;
            $method = CIRouter()->method;
            $ck_permissions = strtolower(trim($path . '.' . $class . '.' . $method));
            $search_this = [$ck_permissions];
            $bycheckAuto = !array_diff($search_this, self::$permisson);
        }

        $bycheckMethod = true;
        if (!self::$autoMode) {
            $bycheckMethod = self::checkMethod();
        }

        if (!$bycheckAuto || !$bycheckMethod) {
            if (CIInput()->post()) {
                $json["msg"] = 'U bent niet gemachtigd om hier te komen!';
                $json["status"] = "error";
                exit(json_encode($json));
            }
            showError();
        }
    }

    public static function joinModule()
    {
        self::$sqlSelect =
            [
                [ModuleModel::$table => '*']
            ];
        self::$sqlJoin =
            [
                [ModuleModel::$table => ModuleModel::$primaryKey]
            ];
    }

    public static function AllByPermissionIds(array $all_permissions = []): array
    {
        self::joinModule();
        self::$sqlWhere = [ModuleModel::$table . ".is_active" => 1];
        self::$sqlWhereIn = [self::$table . "." . self::$primaryKey => $all_permissions];
        $listdb = self::getAll();
        return $listdb;
    }

    public static function selectLink(string $path = '', string $name = 'path', bool $with_empty = false): string
    {
        self::joinModule();
        self::$sqlWhere = [ModuleModel::$table . ".is_active" => 1, self::$table . '.has_link' => 1];
        $listdb = self::getAll();
        $select = '<select name="' . $name . '" class="form-control">';
        $select .= $with_empty ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rsdb) {
            $startPath = $rsdb["use_path"] > 0 ? $rsdb["path"] . '/' : "";
            $value  = $startPath . $rsdb["link_dir"] . '/' . $rsdb['object'] . '/' . $rsdb['method'];
            $label = $value . ' (' . $rsdb["link_title"] . ')';
            $ckk = $path == $value ? "selected" : '';
            $select .= '<option value="' . $value . '" ' . $ckk . '>' . $label . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function navList(int $userid = 0)
    {
        if ($userid <= 0) {
            $userid = LoginModel::userId();
        }
        $user = UserModel::getOneById($userid);
        if (empty($user)) {
            return [];
        }

        $listdbGroup = PermissionGroupModel::getAllGroup($user['permission_group_ids']);
        $stringCompares = array_column($listdbGroup, 'permission_ids');

        $permissionArray = $all_permissions = $permissionsNav = [];
        foreach ($stringCompares as $stringCompare) {
            $permissionArray[] = explode(',', $stringCompare);
        }
        $all_permissions = array_reduce($permissionArray, 'array_merge', []);
        $all_permissions = array_unique($all_permissions);

        if (!empty($all_permissions)) {
            self::joinModule();
            self::$sqlWhere = [ModuleModel::$table . ".is_active" => 1, self::$table . '.has_link' => 1];
            self::$sqlWhereIn = [self::$table . "." . self::$primaryKey => $all_permissions];
            $listdb = self::getAll();
            foreach ($listdb as $rsdb) {
                $startPath = $rsdb["use_path"] > 0 ? $rsdb["path"] . '/' : "";
                $data["url"] = $startPath . $rsdb["link_dir"] . '/' . $rsdb['object'] . '/' . $rsdb['method'];
                $data["name"] = $rsdb["link_title"];
                $keyString = $rsdb["path"] . '#' . $rsdb["path_description"];
                $permissionsNav[$keyString][] = $data;
            }
        }

        return $permissionsNav;
    }

    private static function setForUser(string $permission_group_ids = ''): array
    {
        if (empty($permission_group_ids)) {
            return [];
        }

        $session_data = CISession()->userdata(self::$sessionKey);
        if (!empty($session_data) && ENVIRONMENT !== 'development') {
            self::$permisson = $session_data["permisson"];
            return $session_data;
        }

        $listdbGroup = PermissionGroupModel::getAllGroup($permission_group_ids);
        $stringCompares = array_column($listdbGroup, 'permission_ids');

        $permissionArray = $all_permissions = $permissions = $permissionsNav = [];
        foreach ($stringCompares as $stringCompare) {
            $permissionArray[] = explode(',', $stringCompare);
        }
        $all_permissions = array_reduce($permissionArray, 'array_merge', []);
        $all_permissions = array_unique($all_permissions);

        if (!empty($all_permissions)) {
            $listdb = self::AllByPermissionIds($all_permissions);
            foreach ($listdb as $rsdb) {
                $path = $rsdb['use_path'] > 0 ? $rsdb['path'] : AccessCheckModel::$backPath;
                $permissions[] = strtolower(trim($path . '.' . $rsdb['object'] . '.' . $rsdb['method']));
                if ($rsdb["has_link"] > 0) {
                    $startPath = $rsdb["use_path"] > 0 ? $rsdb["path"] . '/' : "";
                    $data["url"] = $startPath . $rsdb["link_dir"] . '/' . $rsdb['object'] . '/' . $rsdb['method'];
                    $data["name"] = $rsdb["link_title"];
                    $keyString = $rsdb["path"] . '#' . $rsdb["path_description"];
                    $permissionsNav[$keyString][] = $data;
                }
            }
        }

        $dataSession["permisson"] = array_unique($permissions);
        self::$permisson = $dataSession["permisson"];
        CISession()->set_userdata(self::$sessionKey, $dataSession);
        return $dataSession;
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = '', array $dataWhere = []): string
    {
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }

        self::joinModule();
        $where = [ModuleModel::$table . ".is_active" => 1];
        self::$sqlWhere = array_merge($where, $dataWhere);
        $listdb = self::getAll();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["description"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }


    private static function listBy(string $filename = '', int $module_id = 0, string $link_dir = ''): array
    {
        $objectClass = basename($filename, '.php');
        include_once $filename;
        $reflector = new \ReflectionClass($objectClass);
        $methods  = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $arrResult = [];
        if (count($methods) <= 0) {
            return $arrResult;
        }

        $removeMethods = ["__construct", "_remap", "__get"];
        foreach ($methods as $method) {
            $methodNameReflecting = $method->getName();

            if (in_array($methodNameReflecting, $removeMethods, true)) {
                continue;
            }

            $item["description"] = $link_dir . '.' . $objectClass . '.' . $methodNameReflecting;
            $item['object'] = $objectClass;
            $item["module_id"] = $module_id;
            $item['method'] = $methodNameReflecting;
            $item["link_dir"] = $link_dir;
            $item["parent_function"] = '';

            $attributes = $method->getAttributes(DisplayAttribute::class);
            if (count($attributes) > 0) {
                $attribute = end($attributes);
                $item['link_title'] = $attribute->newInstance()->title;
                $item["description"] = $attribute->newInstance()->description;
                $item['has_link'] = empty($item['link_title']) ? 0 : 1;
                $item["order_num"] = $attribute->newInstance()->order_num;
                $item["parent_function"] = $attribute->newInstance()->action;
            }

            $arrResult[] = $item;
        }
        return $arrResult;
    }


    public static function update(int $module_id = 0, array $moduleInfoData = [], string $link_dir = '')
    {
        if ($module_id <= 0) {
            return;
        }

        $app_path = 'controllers' . DIRECTORY_SEPARATOR . $link_dir . DIRECTORY_SEPARATOR;
        if (isset($moduleInfoData['use_path']) && $moduleInfoData['use_path'] === true) {
            $app_path = "modules" . DIRECTORY_SEPARATOR . $moduleInfoData['path'] . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $link_dir . DIRECTORY_SEPARATOR;
        }

        $modulesmap = directory_map(APPPATH . $app_path, 1);

        if (empty($modulesmap)) {
            return;
        }

        $arraysToMerge = [];
        foreach ($modulesmap as $filenameLong) {
            if (str_contains($filenameLong, '.php')) {
                $filename = APPPATH . $app_path . $filenameLong;
                $arraysToMerge[] = self::listBy($filename, $module_id, $link_dir);
            }
        }

        $arrResult = array_merge(...$arraysToMerge);

        if (!empty($arrResult)) {
            foreach ($arrResult as $data) {
                unset($data['parent_function']);
                $ckData["method"] = $data["method"];
                $ckData["object"] = $data["object"];
                $ckData["module_id"] = $data["module_id"];
                $ckData["link_dir"] = $data["link_dir"];
                self::$sqlWhere = $ckData;
                $rsdb = self::getOne();
                if (empty($rsdb)) {
                    self::add($data);
                } else {
                    self::edit($rsdb[self::$primaryKey], $data);
                }
            }
            self::cleanData($arrResult, $module_id, $link_dir);
            self::autoParentId($arrResult);
        }
    }

    private static function cleanData(array $arrResult = [], int $module_id = 0, string $link_dir = '')
    {
        $ckData["module_id"] = $module_id;
        $ckData["link_dir"] = $link_dir;
        self::$sqlWhere = $ckData;
        $listdb = self::getAll();

        $defaultids = $newids = [];
        foreach ($listdb as $rs) {
            $defaultids[] = $rs['object'] . '.' . $rs["method"];
        }

        foreach ($arrResult as $rs) {
            $newids[] = $rs['object'] . '.' . $rs["method"];
        }

        $result = array_diff($defaultids, $newids);
        if (count($result) > 0) {
            foreach ($result as $parent_function) {
                $multi = explode('.',  $parent_function);
                $method = $multi[1];
                $object = $multi[0];
                $ckData["method"] = $method;
                $ckData["object"] = $object;
                self::$sqlWhere = $ckData;
                $rsdb = self::getOne();
                if (!empty($rsdb)) {
                    self::del($rsdb[self::$primaryKey]);
                }
            }
        }
    }

    private static function autoParentId(array $arrResult = [])
    {
        foreach ($arrResult as $data) {
            if (!empty($data['parent_function'])) {
                $method = $data["parent_function"];
                $object = $data["object"];

                $multi = explode('.', $data['parent_function']);
                if (count($multi) == 2) {
                    $method = $multi[1];
                    $object = $multi[0];
                }

                $ckData["method"] = $method;
                $ckData["object"] = $object;
                $ckData["module_id"] = $data["module_id"];
                $ckData["link_dir"] = $data["link_dir"];
                self::$sqlWhere = $ckData;
                $rsdbParent = self::getOne();
                if (!empty($rsdbParent)) {
                    $ckData["method"] = $data["method"];
                    $ckData["object"] = $data["object"];
                    self::$sqlWhere = $ckData;
                    $rsdb = self::getOne();
                    if (!empty($rsdb)) {
                        self::edit($rsdb[self::$primaryKey], ['parent_id' => $rsdbParent[self::$primaryKey]]);
                    }
                }
            }
        }
    }

    public static function getTree($listdb = [], $parent_id = 0, int $level = 0): array
    {
        $data = [];
        foreach ($listdb as $v) {
            if ($v['parent_id'] == $parent_id) {
                $v['level'] =  $level;
                $v['subject_with_level'] =  str_repeat('- ', $level);
                $v['_child'] = self::getTree($listdb, $v[self::$primaryKey], $level + 1);
                $data[] = $v;
            }
        }
        return $data;
    }
}
