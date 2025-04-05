<?php

class LanguageInfoModel
{

    public static function moduleSave(array $data = [], string $langname = '', string $moduleName = "", string $filename = "")
    {
        if (empty($data)) {
            return false;
        }
        $file_path = self::moduleFile($langname, $moduleName, $filename);
        if (file_exists($file_path)) {
            $writefile = "<?php\r\n";
            foreach ($data as $key => $value) {
                $writefile .= '$lang["' . $key . '"] = "' . addslashes($value) . '";' . PHP_EOL;
            }
            return write_file($file_path, $writefile);
        }

        return false;
    }

    public static function moduleFetch(string $langname = '', string $moduleName = "", string $filename = "")
    {
        $file_path = self::moduleFile($langname, $moduleName, $filename);
        $lang = [];
        if (file_exists($file_path) === false) {
            return $lang;
        }
        include($file_path);
        return $lang;
    }

    private static function moduleFile(string $langname = "", string $moduleName = "", string $filename = "core")
    {
        if (empty($langname)) {
            $langname = LanguageModel::$defaultLang;
        }
        $file = $filename . '_lang.php';
        $file_path = "";
        if (empty($moduleName) === false && empty($langname) === false) {
            $file_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $langname . DIRECTORY_SEPARATOR . $file;
        }
        return $file_path;
    }

    public static function moduleFiles(string $language = "", string $moduleName = "")
    {
        $arrResult = [];
        if (empty($moduleName) === false && empty($language) === false) {
            $modulesmap = directory_map(FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            if (empty($modulesmap) === false) {
                foreach ($modulesmap as $module) {
                    $extension = get_mime_by_extension($module);
                    if ($extension !== false) {
                        $arrResult[] = str_replace('_lang.php', '', $module);
                    }
                }
            }
        }
        return $arrResult;
    }

    private static function file(string $langname = "", string $filename = "core")
    {
        if (empty($langname)) {
            $langname = LanguageModel::$defaultLang;
        }
        $file = $filename . '_lang.php';
        $file_path = APPPATH . 'language' . DIRECTORY_SEPARATOR . $langname . DIRECTORY_SEPARATOR . $file;
        return $file_path;
    }

    public static function files(string $language = "")
    {
        if (empty($language)) {
            $language = LanguageModel::$defaultLang;
        }

        $arrResult = [];
        $modulesmap = directory_map(APPPATH  . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
        if (empty($modulesmap) === false) {
            foreach ($modulesmap as $module) {
                $extension = get_mime_by_extension($module);
                if ($extension !== false) {
                    $arrResult[] = str_replace('_lang.php', '', $module);
                }
            }
        }
        return $arrResult;
    }

    public static function save(array $data = [], string $langname = '', string $filename = "")
    {
        if (empty($data)) {
            return false;
        }
        $file_path = self::file($langname, $filename);
        if (file_exists($file_path)) {
            $writefile = "<?php\r\n";
            foreach ($data as $key => $value) {
                $value = nl2br($value);
                $writefile .= '$lang["' . $key . '"] = "' . addslashes($value) . '";' . PHP_EOL;
            }
            return write_file($file_path, $writefile);
        }

        return false;
    }

    public static function fetch(string $langname = '', string $filename = "")
    {
        $file_path = self::file($langname, $filename);
        $lang = [];
        if (file_exists($file_path) === false) {
            return $lang;
        }
        include($file_path);
        return $lang;
    }
}
