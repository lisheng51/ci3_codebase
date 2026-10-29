<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
    <?= $moduleName ?>
</div>

<!-- Nav Item - Pages Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#<?= $targetName ?>_1">
        <span>Webmaster</span>
    </a>
    <div id="<?= $targetName ?>_1" class="collapse" data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?= site_url($path_name . '/Home') ?>">Change</a>
        </div>
    </div>
</li>