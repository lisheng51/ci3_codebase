<?php

/**
 * F_asset
 */
class F_asset
{

    public static $nodeModules = 'node_modules';

    /**
     * 
     * @param string $iconName
     * @return string
     */
    public static function favicon(string $iconName = "favicon.ico"): string
    {
        $filenameAdd = str_contains($iconName, "?") ? '&' : '?';
        $iconName .= $filenameAdd . 'v=' . ENVIRONMENT_ASSET_VERSION;
        return link_tag(base_url($iconName), 'rel="shortcut icon" type="image/x-icon"');
    }

    /**
     * 
     * @param string $module
     * @return string
     */
    public static function app(string $module = ''): string
    {
        // inline i.p.v. home/js/: scheelt per bezoeker een extra (niet-cachebare) PHP request
        return '<script>' . self::appVars($module) . '</script>';
    }

    /**
     * Globale JS variabelen voor function.js (ook gebruikt door Home::js)
     *
     * @param string $module
     * @return string
     */
    public static function appVars(string $module = ''): string
    {
        $site_url = site_url('/');
        $module_url = site_url($module . '/');
        $csrf_token_name = CISecurity()->get_csrf_token_name();
        $csrf_hash = CISecurity()->get_csrf_hash();
        $maxUploadByte = maxUploadByte();
        $message_ajax_fail = ENVIRONMENT === 'development' ? '' : 'Er is momenteel storing, probeert u het later nog eens!';
        return "var maxUploadByte = $maxUploadByte; var site_url = '$site_url'; var message_ajax_fail = '$message_ajax_fail'; var module_url = '$module_url'; var csrf_hash = '$csrf_hash';var csrf_token_name = '$csrf_token_name'";
    }

    /**
     * 
     * @param string $pathfile
     * @param string $module
     * @return string
     */
    public static function linkTag(string $pathfile = "", string $module = 'webmaster'): string
    {
        $filenameAdd = str_contains($pathfile, "?") ? '&' : '?';
        $pathfile .= $filenameAdd . 'v=' . ENVIRONMENT_ASSET_VERSION;
        return link_tag(base_url('modules/' . $module . '/' . self::$nodeModules . '/' . $pathfile));
    }

    /**
     * 
     * @param string $pathfile
     * @param string $module
     * @return string
     */
    public static function scriptTag(string $pathfile = "", string $module = 'webmaster'): string
    {
        $filenameAdd = str_contains($pathfile, "?") ? '&' : '?';
        $pathfile .= $filenameAdd . 'v=' . ENVIRONMENT_ASSET_VERSION;
        return script_tag(base_url('modules/' . $module . '/' . self::$nodeModules . '/' . $pathfile));
    }


    /**
     * 
     * @return string
     */
    public static function jquery(): string
    {
        return script_tag(base_url(self::$nodeModules . "/jquery/dist/jquery.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function jqueryUI(): string
    {
        return script_tag(base_url(self::$nodeModules . "/jquery-ui-dist/jquery-ui.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function cropper(): string
    {
        return link_tag(base_url(self::$nodeModules . "/cropper/dist/cropper.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/cropper/dist/cropper.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function sweetalert2(): string
    {
        return link_tag(base_url(self::$nodeModules . "/sweetalert2/dist/sweetalert2.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/sweetalert2/dist/sweetalert2.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function fontawesome(): string
    {
        return link_tag(base_url(self::$nodeModules . "/@fortawesome/fontawesome-free/css/all.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function leaflet(): string
    {
        return link_tag(base_url(self::$nodeModules . "/leaflet/dist/leaflet.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/leaflet/dist/leaflet.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(sys_asset_url("js/leaflet_map.js"));
    }

    /**
     *
     * @return string
     */
    public static function bootstrap(): string
    {
        return link_tag(base_url(self::$nodeModules . "/bootstrap/dist/css/bootstrap.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap/dist/js/bootstrap.bundle.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function selectpicker(): string
    {
        return link_tag(base_url(self::$nodeModules . "/bootstrap-select/dist/css/bootstrap-select.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap-select/dist/js/bootstrap-select.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap-select/dist/js/i18n/defaults-nl_NL.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function select2(): string
    {
        return link_tag(base_url(self::$nodeModules . "/select2/dist/css/select2.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            link_tag(base_url(self::$nodeModules . "/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/select2/dist/js/select2.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/select2/dist/js/i18n/nl.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function tinymce(): string
    {
        return script_tag(base_url(self::$nodeModules . "/tinymce/tinymce.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function moment(): string
    {
        return script_tag(base_url(self::$nodeModules . "/moment/min/moment.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/moment/locale/nl.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function daterangepicker(): string
    {
        return link_tag(base_url(self::$nodeModules . "/daterangepicker/daterangepicker.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/daterangepicker/daterangepicker.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }

    /**
     * 
     * @return string
     */
    public static function fileinput(): string
    {
        return link_tag(base_url(self::$nodeModules . "/bootstrap-fileinput/css/fileinput.min.css" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap-fileinput/js/fileinput.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap-fileinput/js/locales/nl.js" . '?v=' . ENVIRONMENT_ASSET_VERSION)) .
            script_tag(base_url(self::$nodeModules . "/bootstrap-fileinput/themes/fa6/theme.min.js" . '?v=' . ENVIRONMENT_ASSET_VERSION));
    }
}
