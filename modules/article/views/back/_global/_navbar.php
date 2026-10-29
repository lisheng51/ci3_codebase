<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
    <?php echo $moduleName ?>
</div>

<!-- Nav Item - Pages Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#<?php echo $targetName ?>_1">
        <span>Artikel</span>
    </a>
    <div id="<?php echo $targetName ?>_1" class="collapse" data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?php echo site_url($path_name . '/Article/index') ?>">Overzicht</a>
            <a class="collapse-item" href="<?php echo site_url($path_name . '/Article_cate/index') ?>">Catergorie</a>
        </div>
    </div>
</li>