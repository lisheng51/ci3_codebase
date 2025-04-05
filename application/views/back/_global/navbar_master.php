<ul class="navbar-nav bg-gray-900 sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url(LoginModel::redirectUrl()) ?>">
        <div class="sidebar-brand-text"><?= c_key('webapp_name') ?></div>
    </a>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Logging">
            <i class="fas fa-book fa-fw"></i>
            <span>Logging</span>
        </a>
        <div id="Logging" class="collapse" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?= site_url($path_name . '/api/log') ?>">API</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/sendmail/index') ?>">Sendmail</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/visitor/index') ?>">Bezoeker</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/applog/index') ?>">Systeem</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/history_url/index') ?>">Geschiedenis</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/login_history/index') ?>">Login</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/error_log/index') ?>">Error</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/change_log/index') ?>">Change</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Account">
            <i class="fas fa-user fa-fw"></i>
            <span>Account</span>
        </a>
        <div id="Account" class="collapse" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?= site_url($path_name . '/api/index') ?>">API</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/user/index') ?>">Overzicht</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/file/index') ?>">Bestand</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/bookmark/index') ?>">Bookmark</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/message/index') ?>">Inbox</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/message/my') ?>">Outbox</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Systeem">
            <i class="fas fa-cog fa-fw"></i>
            <span>Systeem</span>
        </a>
        <div id="Systeem" class="collapse" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?= site_url($path_name . '/module/index') ?>">Module</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/permission_group/index') ?>">Groep</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/permission/index') ?>">Toestemming</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/aes/index') ?>">Aes</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/config/index') ?>">Instelling</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/language_tag/index') ?>">Taal tag</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/mail_temp/index') ?>">Mail template</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/spamword/index') ?>">Spamword</a>
                <a class="collapse-item" href="<?= site_url($path_name . '/mail_config/index') ?>">Mail config</a>
            </div>
        </div>
    </li>

    <?= ModuleModel::autoNavbar(); ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>