<?php

class ModuleModel
{
    use BasicModel;
    public static function __constructStatic()
    {

        self::$table = "module";
        self::$primaryKey = "module_id";
        self::$selectOrderBy = [
            'module_id#desc' => 'ID (aflopend)',
            'path_description#desc' => 'Naam (aflopend)',
            'path_description#asc' => 'Naam (oplopend)',
            'sort_list#desc' => 'Volgorde (aflopend)',
            'sort_list#asc' => 'Volgorde (oplopend)',
        ];
        self::$sqlOrderBy = ['sort_list' => "asc"];
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = ''): string
    {
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }
        $listdb = self::getAll();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["path_description"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectMultiple(array $haystack = [], string $name = ''): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($haystack)) {
            $haystack = CIInput()->get(self::$primaryKey) ?? [];
            if (!is_array($haystack)) {
                $haystack = [$haystack];
            }
        }
        $listdb = self::getAll();
        $select = '<select name="' . $select_name . '[]" class="form-control" multiple>';
        foreach ($listdb as $rs) {
            $ckk = (!empty($haystack) && in_array($rs[self::$primaryKey], $haystack)) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["path_description"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function getInfo(string $app_path = ""): array
    {
        $data = [];
        if ($app_path === '_core') {
            require(APPPATH . 'info.php');
        }

        $data_file = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $app_path . DIRECTORY_SEPARATOR . 'info.php';
        if (file_exists($data_file)) {
            require($data_file);
        }
        return $data;
    }

    public static function getPostdata(): array
    {
        $data["is_active"] = CIInput()->post("is_active");
        $data["path_description"] = CIInput()->post("path_description");
        return $data;
    }

    public static function update(array $fromdata = [], string $dir = '')
    {
        $data["path"] = $fromdata["path"];
        $data["use_path"] = intval($fromdata["use_path"]);
        $data["path_description"] = $fromdata["path_description"];
        $rsdb = self::getOneByField('path', $data["path"]);

        if (empty($rsdb)) {
            $data['is_active'] = 1;
            $module_id = self::add($data);
            PermissionModel::update($module_id, $fromdata, $dir);
        } else {
            self::edit($rsdb[self::$primaryKey], $data);
            PermissionModel::update($rsdb[self::$primaryKey], $fromdata, $dir);
        }
    }

    public static function setup(array $fromdata = [])
    {
        if (isset($fromdata['use_path']) && $fromdata['use_path']) {
            $module = $fromdata["path"];
            UploadModel::makeDir($module);
            self::insertSqlFile($module);
        }

        if (isset($fromdata['arrUploadFolder']) && !empty($fromdata['arrUploadFolder'])) {
            foreach ($fromdata['arrUploadFolder'] as $path) {
                UploadModel::makeDir($path);
            }
        }
    }

    public static function copieLiveDataUrl(string $module = "", array $except = []): string
    {
        $domain = CIInput()->get('domain') ?? site_url();
        $fileList = self::sqlFiles($module);
        $result = array_diff($fileList, $except);

        $tables = implode(',', $result);
        $liveUrl = $domain . 'api/Database/copie';
        $url = site_url("su/CopieTest/module/yes?url=$liveUrl&tables=$tables");
        return $url;
    }

    public static function sqlFiles(string $module = "")
    {
        $fileList = [];
        $arrSqlFile = directory_map(APPPATH . 'sql' . DIRECTORY_SEPARATOR);
        if (!empty($module)) {
            $linesFilePath = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
            $arrSqlFile = directory_map($linesFilePath);
        }

        if (!empty($arrSqlFile)) {
            foreach ($arrSqlFile as $name) {
                $fileList[] = basename($name, ".sql");
            }
        }
        return $fileList;
    }

    private static function insertSqlFile(string $module = "")
    {
        $fileList = self::sqlFiles($module);
        if (!empty($fileList)) {
            foreach ($fileList as $tableName) {
                if (CIDb()->table_exists($tableName) === false) {
                    $templine = '';
                    $lines = file(FCPATH . 'modules' . DIRECTORY_SEPARATOR .  $module . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $tableName . '.sql');
                    foreach ($lines as $line) {
                        if (substr($line, 0, 2) == '--' || $line == '') {
                            continue;
                        }
                        $templine .= $line;
                        if (substr(trim($line), -1, 1) == ';') {
                            CIDb()->query($templine);
                            $templine = '';
                        }
                    }
                }
            }
        }
    }

    public static function updateSql(array $sqlList = [])
    {
        if (empty($sqlList)) {
            return;
        }
        foreach ($sqlList as $tableName => $arrValue) {
            if (CIDb()->table_exists($tableName) && !empty($arrValue)) {
                foreach ($arrValue as $val) {
                    if (CIDb()->field_exists($val["field"], $tableName) === $val["exists"]) {
                        CIDb()->query($val["sql"]);
                    }
                }
            }
        }
    }

    public static function navbar(string $moduleName = '', string $filename = '_navbar')
    {
        if (empty($moduleName)) {
            return "";
        }
        $existInstall = self::getOneByField('path', $moduleName);
        $backPath = AccessCheckModel::$backPath;
        $ck_file_exist_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $backPath . DIRECTORY_SEPARATOR . '_global' . DIRECTORY_SEPARATOR . $filename . '.php';
        if (file_exists($ck_file_exist_path) && !empty($existInstall) && $existInstall["is_active"] > 0) {
            $data['path_name'] = $moduleName . '/' . $backPath;
            $data['moduleName'] = $existInstall["path_description"];
            $data['targetName'] = $existInstall["path"];
            $content = GlobalModel::loadView($moduleName . '/' . $backPath . '/_global/' . $filename, $data, true);
            return $content;
        }

        return "";
    }

    public static function autoNavbar(string $filename = '_navbar')
    {
        $content = "";
        $modulesmap = self::getAllByField('is_active', 1);
        $backPath = AccessCheckModel::$backPath;
        foreach ($modulesmap as $module) {
            $moduleName = $module["path"];
            $ck_file_exist_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $backPath . DIRECTORY_SEPARATOR . '_global' . DIRECTORY_SEPARATOR . $filename . '.php';
            if (file_exists($ck_file_exist_path)) {
                $data['path_name'] = $moduleName . '/' . $backPath;
                $data['moduleName'] = $module["path_description"];
                $data['targetName'] = $moduleName;
                $content .= GlobalModel::loadView($moduleName . '/' . $backPath . '/_global/' . $filename, $data, true);
            }
        }
        return $content;
    }

    public static function language(string $lang = "")
    {
        $modulesmapAll = directory_map(FCPATH . 'modules' . DIRECTORY_SEPARATOR, 1);

        foreach ($modulesmapAll as $module) {
            $moduleName = rtrim($module, DIRECTORY_SEPARATOR);
            if (CIRouter()->module == $moduleName) {
                $modulesmap = directory_map(FCPATH  . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $lang, 1);
                if (empty($modulesmap) === false) {
                    foreach ($modulesmap as $filenameLong) {
                        $filename = str_replace('_lang.php', '', rtrim($filenameLong, DIRECTORY_SEPARATOR));
                        $ck_file_default = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR .  $filename . '_lang.php';
                        if (file_exists($ck_file_default)) {
                            CILang()->load($filename, $lang, false, true, FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR);
                        }
                    }
                }
            }
        }
    }

    public static function loadLanguageFile(string $moduleName = "", string $filename = "", string $setLang = "")
    {
        $lang = empty($setLang) === true ? LanguageModel::getLanguage() : $setLang;
        $modulesmap = directory_map(FCPATH  . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $lang, 1);
        if (empty($modulesmap) === false) {
            $ck_file_default = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR .  $filename . '_lang.php';
            if (file_exists($ck_file_default)) {
                CILang()->load($filename, $lang, false, true, FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR);
            }
        }
    }

    public static function selectModule(string $choose_id = ""): string
    {
        $select = '<select name="moduleName" class="form-control">';
        $select .= "<option value=''>Core</option>";
        $listdb = self::getAllByField('is_active', 1);
        foreach ($listdb as $rs) {
            $ckk = $choose_id == $rs['path'] ? "selected" : '';
            $select .= '<option value=' . $rs['path'] . ' ' . $ckk . '>' . $rs["path_description"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function isActive(string $path = ""): bool
    {
        $rsdb = self::getOneByField('path', $path);
        if (empty($rsdb)) {
            return false;
        }
        if ($rsdb['is_active'] > 0) {
            return true;
        }
        return false;
    }
}
