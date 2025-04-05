<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($listdb as $value) : ?>
                <div class="col-md-3" id="<?= $value["user_id"]; ?>">
                    <div class="card">
                        <div class="card-body">
                            <h4><?= $value["display_info"]; ?></h4>
                            <p>Email: <?= $value["emailaddress"]; ?></p>
                            <p><?= $value["addButton"] ?></p>
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
