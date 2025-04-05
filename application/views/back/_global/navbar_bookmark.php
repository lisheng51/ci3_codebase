<ul class="navbar-nav bg-gray-900 sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url(LoginModel::redirectUrl()) ?>">
        <div class="sidebar-brand-text"><?= c_key('webapp_name') ?></div>
    </a>
    <?php foreach (BookMarkModel::navBarData() as $arrMenu) : ?>
        <hr class="sidebar-divider">
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bookmark_<?= $arrMenu["bookmark_id"] ?>">
                <?= $arrMenu["icon"] ?>
                <span><?= $arrMenu["name"] ?></span>
            </a>
            <div id="bookmark_<?= $arrMenu["bookmark_id"] ?>" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <?php foreach ($arrMenu["navBarDataChild"] as $value) : ?>
                        <a class="collapse-item" target="<?= $value["target"] ?>" href="<?= $value["url"] ?>"><?= $value["icon"] ?> <?= $value["name"] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </li>

    <?php endforeach; ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>