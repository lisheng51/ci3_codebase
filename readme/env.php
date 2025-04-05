<?php
define('ENVIRONMENT', 'development');
date_default_timezone_set('Europe/Amsterdam');
$cookie_secure = false;
$compress_output = true;
$httphost = 'localhost';
$scheme = 'http';
if (isset($_SERVER['HTTP_HOST'])) {
    $httphost = $_SERVER['HTTP_HOST'];
}

if (isset($_SERVER['argv'])) {
    $httphost = end($_SERVER['argv']);
    $scheme = 'https';
}

$posIsNgork = strpos($httphost, 'ngrok-free.app');

if (isset($_SERVER['REQUEST_SCHEME'])) {
    $scheme = $_SERVER['REQUEST_SCHEME'];
    if ($posIsNgork !== false) {
        $scheme = 'https';
    }
    if ($scheme === 'https') {
        $cookie_secure = true;
    }
}

$domainSetting = [];
$domainJsonFile =  dirname(__FILE__) . DIRECTORY_SEPARATOR . 'domain.json';
if (file_exists($domainJsonFile)) {
    $content = file_get_contents($domainJsonFile);
    $domainDataList = json_decode($content, true);
    if (array_key_exists($httphost, $domainDataList) === true) {
        $domainSetting = $domainDataList[$httphost];
    }
}


$base_url = $scheme . '://' . $httphost . '/';
$databaseHost = $domainSetting['databaseHost'] ?? 'localhost';
$databaseName = $domainSetting['databaseName'] ?? 'db';
$databaseUser = $domainSetting['databaseUser'] ?? 'root';
$databasePass = $domainSetting['databasePass'] ?? '1234';
$uploadFolder = $domainSetting['uploadFolder'] ?? 'uploads';
$loginUrl = $domainSetting['loginUrl'] ?? 'access';
$sessionExpiration = intval($domainSetting['sessionExpiration'] ?? 7200);
$sessionUpdate = intval($domainSetting['sessionUpdate'] ?? 7200);
$csrfProtection = boolval($domainSetting['csrfProtection'] ?? 1);
$csrfExpiration = intval($domainSetting['csrfExpiration'] ?? 7200);
$permissionCheck = boolval($domainSetting['permissionCheck'] ?? 1);
$visitorLog = boolval($domainSetting['visitorLog'] ?? 0);
$defaultController = $domainSetting['defaultController'] ?? 'Home';
$defaultLanguage = $domainSetting['defaultLanguage'] ?? 'dutch';
$defaultRoutes = $domainSetting['defaultRoutes'] ?? '';

$ENVIRONMENT_ROUTES = [];
if (empty($defaultRoutes) === false) {
    $defaultRoutes = trim($defaultRoutes);
    $mailList = explode(',', $defaultRoutes);
    if (empty($mailList) === false) {
        foreach ($mailList as $value) {
            $value = trim($value);
            if (empty($value) === false) {
                $mailUserList = explode('=>', $value);
                if (count($mailUserList) === 2) {
                    $viewuri = trim($mailUserList[0]);
                    $sysuri = trim($mailUserList[1]);
                    $ENVIRONMENT_ROUTES[$viewuri] = $sysuri;
                }
            }
        }
    }
}

$assetVersion = '1';
if ($httphost  === 'localhost' || $posIsNgork !== false) {
    $compress_output = false;
    $val = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $base_url = $scheme . '://' . $httphost . '/' . end($val) . '/';
    $assetVersion = time();
}

define('ENVIRONMENT_ASSET_VERSION', $assetVersion);
define('ENVIRONMENT_UPLOAD_PATH', $uploadFolder);
define('ENVIRONMENT_ACCESS_URL', $loginUrl);
define('ENVIRONMENT_COOKIE_SECURE', $cookie_secure);
define('ENVIRONMENT_COMPRESS_OUTPUT', $compress_output);
define('ENVIRONMENT_SESS_COOKIE_NAME', str_replace('.', '_', $httphost));
define('ENVIRONMENT_SESS_EXPIRATION', $sessionExpiration);
define('ENVIRONMENT_SESS_TIME_TO_UPDATE', $sessionUpdate);
define('ENVIRONMENT_CSRF_PROTECTION', $csrfProtection);
define('ENVIRONMENT_CSRF_EXPIRE', $csrfExpiration);
define('ENVIRONMENT_CSRF_EXCLUDE_URIS', ['api/(.+)', '(.+)/api/(.+)']);

define('ENVIRONMENT_BASE_URL', $base_url);
define('ENVIRONMENT_BC_API_ID', "");
define('ENVIRONMENT_BC_API_KEY', "");
define('ENVIRONMENT_BC_API_URL', '');
define('ENVIRONMENT_TELEGRAM_USER', "");
define('ENVIRONMENT_TELEGRAM_GROUP', "");
define('ENVIRONMENT_TELEGRAM_BOT', '');
define('ENVIRONMENT_ENCRYPTION_PASS', '');
define('ENVIRONMENT_ENCRYPTION_KEY', '');
define('ENVIRONMENT_SUPPORT_REWRITE', true);
define('ENVIRONMENT_HOSTNAME', $databaseHost);
define('ENVIRONMENT_DATABASE', $databaseName);
define('ENVIRONMENT_USERNAME', $databaseUser);
define('ENVIRONMENT_PASSWORD', $databasePass);
define('ENVIRONMENT_PERMISSION_CHECK', $permissionCheck);
define('ENVIRONMENT_VISITOR_LOG', $visitorLog);
define('ENVIRONMENT_DEFAULT_CONTROLLER', $defaultController);
define('ENVIRONMENT_DEFAULT_LANGUAGE', $defaultLanguage);
define('ENVIRONMENT_ROUTES', $ENVIRONMENT_ROUTES);
