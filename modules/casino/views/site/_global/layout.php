<!DOCTYPE html>
<html lang="<?= CasinoLang::htmlLang() ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="theme-color" content="#0b3d2e">
    <!-- on a tablet "add to home screen" opens the casino full screen -->
    <link rel="manifest" href="<?= site_url($module_path_name . '/auth/manifest') ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(CasinoLang::t('title')) ?>">
    <link rel="apple-touch-icon" href="<?= site_url($module_path_name . '/auth/icon') ?>">
    <title><?= htmlspecialchars((string)$title) ?></title>
    <?= F_asset::favicon() . F_asset::fontawesome() . F_asset::sweetalert2() . F_asset::jquery() .
        F_asset::bootstrap() . F_asset::app($module_url)
        . script_tag(sys_asset_url("js/function.js")) ?>
    <?= link_tag(module_asset_url("css/casino.css")) ?>
</head>

<body class="casino">
    <?= $content; ?>
</body>

</html>
