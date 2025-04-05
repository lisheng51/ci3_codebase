<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <?= $total ?></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Taal</th>
                        <th>Onderwerp</th>
                        <th>Trigger</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= $value["name"] ?></td>
                            <td><?= $value["subject"]; ?></td>
                            <td><?= $value["trigger_name"]; ?></td>
                            <td><?= $value["editButton"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <?= $pagination; ?>
    </div>
</div>