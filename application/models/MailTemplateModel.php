<?php

class MailTemplateModel
{

    private static $folderName = "mail_template";

    public static function selectFolder(string $moduleName = ''): array
    {
        $folderPath = DIRECTORY_SEPARATOR . self::$folderName . DIRECTORY_SEPARATOR;
        if (empty($moduleName) === false) {
            $folderPath = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . self::$folderName . DIRECTORY_SEPARATOR;
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

    private static function file(string $langname = "", string $filename = "core")
    {
        if (empty($langname) === true) {
            $langname = LanguageModel::$defaultLang;
        }
        $file = $filename . '.php';
        return APPPATH . self::$folderName . DIRECTORY_SEPARATOR . $langname . DIRECTORY_SEPARATOR . $file;
    }

    public static function fetchfolderFile(string $langname = '', string $filename = "")
    {
        $file_path = self::file($langname, $filename);
        $lang = [];
        if (file_exists($file_path) === false) {
            return $lang;
        }
        include($file_path);
        return $lang;
    }

    public static function fetchfolderFiles(string $language = "")
    {
        if (empty($language)) {
            $language = LanguageModel::$defaultLang;
        }

        $arrResult = [];
        $modulesmap = directory_map(APPPATH  . self::$folderName . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
        if (empty($modulesmap) === false) {
            foreach ($modulesmap as $module) {
                $extension = get_mime_by_extension($module);
                if ($extension !== false) {
                    $arrResult[] = str_replace('.php', '', $module);
                }
            }
        }
        return $arrResult;
    }

    public static function saveFolderFile(array $data = [], string $langname = '', string $filename = "")
    {
        if (empty($data)) {
            return false;
        }
        $file_path = self::file($langname, $filename);
        if (file_exists($file_path) === true) {
            $writefile = "<?php\r\n";
            foreach ($data as $key => $value) {
                $value = nl2br($value);
                $writefile .= '$lang["' . $key . '"] = "' . addslashes($value) . '";' . PHP_EOL;
            }
            return write_file($file_path, $writefile);
        }

        return false;
    }

    public static function moduleFetchfolderFiles(string $language = "", string $moduleName = "")
    {
        $arrResult = [];
        if (empty($moduleName) === false && empty($language) === false) {
            $modulesmap = directory_map(FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . self::$folderName . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR, 1);
            if (empty($modulesmap) === false) {
                foreach ($modulesmap as $module) {
                    $extension = get_mime_by_extension($module);
                    if ($extension !== false) {
                        $arrResult[] = str_replace('.php', '', $module);
                    }
                }
            }
        }
        return $arrResult;
    }

    public static function moduleFetchfolderFile(string $langname = '', string $moduleName = "", string $filename = "")
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
        if (empty($langname) === true) {
            $langname = LanguageModel::$defaultLang;
        }

        $file = $filename . '.php';
        $file_path = "";
        if (empty($moduleName) === false && empty($langname) === false) {
            $file_path = FCPATH . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . self::$folderName . DIRECTORY_SEPARATOR . $langname . DIRECTORY_SEPARATOR . $file;
        }
        return $file_path;
    }

    public static function moduleSaveFolderFile(array $data = [], string $langname = '', string $moduleName = "", string $filename = "")
    {
        if (empty($data)) {
            return false;
        }
        $file_path = self::moduleFile($langname, $moduleName, $filename);
        if (file_exists($file_path) === true) {
            $writefile = "<?php\r\n";
            foreach ($data as $key => $value) {
                $writefile .= '$lang["' . $key . '"] = "' . addslashes($value) . '";' . PHP_EOL;
            }
            return write_file($file_path, $writefile);
        }

        return false;
    }

    public static function moduleGetByMethod(string $moduleName = "", string $method = 'contact'): array
    {
        $langname = LanguageModel::getLanguage();
        $configValue = self::moduleFetchfolderFile($langname, $moduleName, $method);
        if (empty($configValue)) {
            $configValue = self::moduleFetchfolderFile(LanguageModel::$defaultLang, $moduleName, $method);
        }
        return $configValue;
    }

    public static function getByMethod(string $method = 'contact'): array
    {
        $langname = LanguageModel::getLanguage();
        $configValue = self::fetchfolderFile($langname, $method);
        if (empty($configValue)) {
            $configValue = self::fetchfolderFile(LanguageModel::$defaultLang, $method);
        }
        return $configValue;
    }

    public static function replaceBody(string $body = '', array $userdb = []): string
    {
        if (isset($userdb["emailaddress"])) {
            $body = str_replace('{emailaddress}', $userdb["emailaddress"], $body);
        }

        if (isset($userdb["email"])) {
            $body = str_replace('{emailaddress}', $userdb["email"], $body);
        }

        if (isset($userdb["phone"])) {
            $body = str_replace('{phone}', $userdb["phone"], $body);
        }

        if (isset($userdb["name"])) {
            $body = str_replace('{sys_name}', $userdb["name"], $body);
        }

        if (isset($userdb["message"])) {
            $body = str_replace('{sys_message}', $userdb["message"], $body);
        }

        return $body;
    }
}
