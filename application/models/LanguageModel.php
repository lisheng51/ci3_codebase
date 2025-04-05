<?php

class LanguageModel
{
    use BasicModel;
    public static $defaultLang = ENVIRONMENT_DEFAULT_LANGUAGE;
    public static function __constructStatic()
    {
        self::$table = "language";
        self::$primaryKey = "language_id";
        self::$selectOrderBy = [
            'language_id#desc' => 'ID (aflopend)',
            'name#desc' => 'Naam (aflopend)',
            'name#asc' => 'Naam (oplopend)',
        ];
    }

    public static function selectFolder(string $folder = "", string $name = ''): string
    {
        $select_name = !empty($name) ? $name : 'defaultLanguage';
        $select = '<select name="' . $select_name . '" class="form-control">';
        $listdb = self::getAll();
        foreach ($listdb as $rs) {
            $ckk = $folder == $rs['folder'] ? "selected" : '';
            $select .= '<option value="' . $rs["folder"] . '" ' . $ckk . '>' . $rs["folder"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }


    public static function selectList(string $moduleName = ''): array
    {
        $folderPath = DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        if (!empty($moduleName)) {
            $folderPath = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        }
        $modulesmap = directory_map(APPPATH . $folderPath, 1);
        $arrResult = [];
        if (empty($modulesmap) === false) {
            foreach ($modulesmap as $module) {
                $arrResult[] = rtrim($module, DIRECTORY_SEPARATOR);
            }
        }
        return $arrResult;
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = '', array $dataWhere = []): string
    {
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }
        $listdb = self::getAllData($dataWhere);
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

    public static function getAllData(array $dataWhere = []): array
    {
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = array_merge($where, $dataWhere);
        self::$sqlOrderBy = ['order_list' => 'asc'];
        $listdb = self::getAll();
        return $listdb;
    }

    public static function selectMultiple(array $haystack = [], string $name = '', array $dataWhere = []): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($haystack)) {
            $haystack = CIInput()->get(self::$primaryKey) ?? [];
            if (!is_array($haystack)) {
                $haystack = [$haystack];
            }
        }
        $listdb = self::getAllData($dataWhere);
        $select = '<select name="' . $select_name . '[]" class="form-control" multiple>';
        foreach ($listdb as $rs) {
            $ckk = (!empty($haystack) && in_array($rs[self::$primaryKey], $haystack)) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function setLanguage()
    {
        $lang = self::getLanguage();

        $arrResult = LanguageInfoModel::files($lang);
        if (empty($arrResult) === false) {
            foreach ($arrResult as $file) {
                CILang()->load($file, $lang);
            }
        }
    }

    public static function getLanguage(): string
    {
        $lang = CIInput()->get(self::$table);
        if (!empty($lang) && !empty(self::getOneByField('folder', $lang))) {
            CISession()->set_userdata(self::$table, $lang);
            return $lang;
        }
        $session_data = CISession()->userdata(self::$table);
        if (!empty($session_data)) {
            $lang = $session_data;
            return $lang;
        }
        return self::$defaultLang;
    }

    public static function getPostdata(): array
    {
        $data["name"] = CIInput()->post("name");
        $data["folder"] = CIInput()->post("folder");
        $data["code"] = CIInput()->post("code");
        $data["icon"] = CIInput()->post("icon");
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }

    public static function getFieldData(string $field = 'code'): array
    {
        $listdb = self::getAll();
        $arrResult = array_column($listdb, $field);
        return $arrResult;
    }

    public static function fetchId(string $setLang = ""): int
    {
        $language = empty($setLang) ? self::getLanguage() : $setLang;
        $arr_data = self::getOneByField('folder', $language);
        if (empty($arr_data)) {
            return 1;
        }
        return $arr_data[self::$primaryKey];
    }

    public static function fieldByLanguage(string $field = "name", string $setLang = "")
    {
        $language = empty($setLang) ? self::getLanguage() : $setLang;
        $fieldName = $field;

        $arr_data = self::getOneByField('folder', $language);
        if (empty($arr_data) === false && $language != self::$defaultLang) {
            $fieldName = $field . '_' . $arr_data["code"];
        }
        return $fieldName;
    }
}
