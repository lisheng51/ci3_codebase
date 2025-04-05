<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($listdb as $value) : ?>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h4><?= $value["display_info"]; ?></h4>
                            <p><?= $value["editButton"]; ?> <?= $value["emailaddress"]; ?></p>
                            <?php foreach ($value['permissionGroupDb'] as $rs) : ?>
                                <span class="badge badge-info"><?= $rs["name"]; ?></span>
                                <!--                                <a href="<?= site_url($path_name . "/User/index?permission_group_id[]=" . $rs["permission_group_id"]); ?>" class="badge badge-info"><?= $rs["name"]; ?></a>-->
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-footer">
        <?= $pagination; ?>
    </div>
</div>