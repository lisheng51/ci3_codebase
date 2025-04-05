<div v-html="result">
    <div class="card">
        <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Datum & Tijd</th>
                            <th>Wie</th>
                            <th>IP</th>
                            <th>Path</th>
                            <th>Browser</th>
                            <th>Platform</th>
                        </tr>
                    </thead>
                    <tbody id="itemContainer">
                        <?php foreach ($listdb as $value) : ?>
                            <tr>
                                <td><?= $value["datetime"] ?></td>
                                <td><?= $value["username"] ?></td>
                                <td><?= $value["ip_address"] ?></td>
                                <td><?= $value["path"] ?></td>
                                <td><?= $value["browser"] ?></td>
                                <td><?= $value["platform"] ?></td>
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
</div>