<ul class="navbar-nav bg-gray-900 sidebar sidebar-dark accordion hrefPermissionCk" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url(LoginModel::redirectUrl()) ?>">
        <div class="sidebar-brand-text"><?= c_key('webapp_name') ?></div>
    </a>
    <?php
    foreach (PermissionModel::navList() as $key => $arr_menu) :
        list($targetName, $path_description) = explode("#", $key);
        $arr_menu_unique = F_array::unique_multidim($arr_menu, ["name", "url"]);
    ?>
        <hr class="sidebar-divider">
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#<?= $targetName ?>_1">
                <span><?= $path_description ?></span>
            </a>
            <div id="<?= $targetName ?>_1" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <?php foreach ($arr_menu_unique as $value) : ?>
                        <a class="collapse-item" href="<?= site_url($value["url"]) ?>"><?= $value["name"] ?></a>
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