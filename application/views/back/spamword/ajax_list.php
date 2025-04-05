<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= $value["word"]; ?></td>
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

<script>
    ajax_inline_edit(site_url + 'back/spamword/editinline');
</script>