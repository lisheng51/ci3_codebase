<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <?= $total ?></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Taal</th>
                        <th>Tag</th>
                        <th>Waarde</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= $value["name"] ?></td>
                            <td><?= $value["tag"]; ?></td>
                            <td><?= word_limiter($value["value"]) ?></td>
                            <td><?= $value["editButton"] ?> <?= $value["editButtonTinymce"] ?></td>
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
    ajax_inline_edit(site_url + 'back/language_tag/editinline');
</script>