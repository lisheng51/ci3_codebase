<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= html_escape((string)$title) ?> | <?= html_escape(c_key('_core_name')) ?></title>
    <?php if (!empty($description)) : ?>
        <meta name="description" content="<?= html_escape($description) ?>">
    <?php endif; ?>
    <?php
    echo F_asset::favicon();
    echo F_asset::fontawesome();
    echo F_asset::jquery();
    echo F_asset::bootstrap();
    echo F_asset::app($module_url);
    echo script_tag(sys_asset_url("js/function.js"));
    echo link_tag(module_asset_url("css/article.css"));
    ?>
</head>

<body class="article-site">
    <header class="article-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="article-brand" href="<?= site_url() ?>">
                <span class="article-brand-icon"><i class="fa-solid fa-feather-pointed"></i></span>
                <?= html_escape(c_key('_core_name')) ?>
            </a>
            <a class="article-nav-link" href="<?= site_url($controller_url) ?>">
                <i class="fa-regular fa-newspaper me-1"></i><?= html_escape($title_module) ?>
            </a>
        </div>
    </header>

    <?= $content; ?>

    <footer class="article-footer">
        <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2">
            <span>&copy; <?= date('Y') ?> <?= html_escape(c_key('_core_name')) ?></span>
            <a href="<?= site_url($controller_url) ?>"><?= html_escape($title_module) ?></a>
        </div>
    </footer>
</body>

</html>
