<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= $title ?></title>
    <?php
    echo F_asset::favicon();
    echo F_asset::fontawesome();
    echo F_asset::axios() . F_asset::jquery();
    echo F_asset::bootstrap();
    echo F_asset::sweetalert2();
    echo script_tag(site_url("asset/index/" . $module_url));
    echo script_tag(sys_asset_url("js/function.js"));
    ?>
</head>

<body>
    <div class="container-fluid">
        <div class="card mt-3">
            <?= $content; ?>
            <div class="text-center">
                <p>&copy; Copyright <?= date('Y') ?></p>
            </div>
        </div>
    </div>
</body>

</html>