<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?></title>
    <?php
    echo F_asset::favicon();
    echo F_asset::fontawesome();
    echo F_asset::jquery();
    echo F_asset::bootstrap();
    echo F_asset::sweetalert2();
    echo F_asset::app($module_url);
    echo script_tag(sys_asset_url("js/function.js"));
    echo link_tag(sys_asset_url("css/sb-admin-2.css"));
    echo script_tag(module_asset_url("js/access.js"));
    ?>
</head>

<body class="bg-gradient-dark">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <?= $content; ?>
            </div>
        </div>
    </div>
</body>

</html>