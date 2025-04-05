<?php

class BreadcrumbModel
{
    private static $arrData = [];
    private static $template = "breadcrumb";

    public static function setData(array $data = [])
    {
        self::$arrData[] = $data;
    }

    public static function emptyData()
    {
        self::$arrData = [];
    }

    public static function setMultidata(array $data = [])
    {
        foreach ($data as $value) {
            self::$arrData[] = $value;
        }
    }

    public static function setTemplate(string $value = "")
    {
        self::$template = $value;
    }

    public static function show(): string
    {
        if (empty(self::$arrData)) {
            return "";
        }

        $viewPath = str_replace("/", DIRECTORY_SEPARATOR, self::$template);
        $defaultCk = APPPATH . 'views' . DIRECTORY_SEPARATOR . $viewPath . '.php';
        if (empty(CIRouter()->module) === false) {
            $modulesName = CIRouter()->module;
            $defaultCk = FCPATH. 'modules' . DIRECTORY_SEPARATOR . $modulesName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewPath . '.php';
            if (file_exists($defaultCk) === false) {
                $defaultCk = APPPATH . 'views' . DIRECTORY_SEPARATOR . $viewPath . '.php';
            }
        }

        if (file_exists($defaultCk) === false) {
            return "";
        }

        $listdb = [];
        foreach (self::$arrData as $value) {
            $value["string"] = $value["string"] = '<li class="breadcrumb-item active">' . $value["name"] . '</li>';
            if (isset($value["url"]) && empty($value["url"]) === false) {
                $value["string"] = '<li class="breadcrumb-item"><a href=' . site_url($value["url"]) . '>' . $value["name"] . '</a></li>';
            }
            if (isset($value["event"]) && empty($value["event"]) === false) {
                $value["string"] = '<li class="breadcrumb-item"><a href=' . $value["event"] . '>' . $value["name"] . '</a></li>';
            }
            $listdb[] = $value;
        }
        $data["listdb"] = $listdb;
        return CILoader()->view(self::$template, $data, true);
    }
}
