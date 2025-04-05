<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Key</th>
                        <th>Naam</th>
                        <th>Toestemming groep</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= $value["api_id"]; ?></td>
                            <td><?= $value["secret"]; ?></td>
                            <td><?= $value["name"]; ?></td>
                            <td>
                                <?php foreach ($value['permissionGroupDb'] as $rs) : ?>
                                    <span class="badge badge-info"><?= $rs["name"]; ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="text-center">
                                <?= $value["editButton"] ?>
                            </td>
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
<script>
    ajax_inline_edit(site_url + 'back/Api/editInline');
</script>