<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?></title>
    <?php
    echo F_asset::fontawesome();
    echo F_asset::axios() . F_asset::jquery();
    echo F_asset::bootstrap();
    ?>
</head>

<body>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Datum & Tijd</th>
                    <th>Gebruikersnaam</th>
                    <th>Beschrijving</th>
                    <th>Path/url</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listdb as $value) : ?>
                    <tr>
                        <td><?= F_datetime::convert_datetime($value["date"]) ?></td>
                        <td><?= $value["emailaddress"] ?></td>
                        <td><?= $value["description"] ?></td>
                        <td><?= $value["path"] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= link_tag(sys_asset_url("css/darktheme.css"));
    echo script_tag(sys_asset_url("js/darktheme.js")); ?>
</body>

</html>