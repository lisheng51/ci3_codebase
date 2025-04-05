<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Reinstall</th>
                        <th>Actief</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><button type="button" class="btn btn-info btn-sm" id="start_ajax_sort"><?= lang("sort_icon") ?></button> <?= $value["path_description"]; ?> <input type="hidden" name="sort_list[<?= $value['module_id'] ?>]" value="<?= $value['sort_list'] ?>"></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm updatemodule" data-search_data="<?= $value["module_id"]; ?>"><?= lang("reset_icon") ?></button>
                            </td>
                            <td><?= $value["is_active"] ?></td>
                            <td>
                                <?= $value["editButton"] ?>
                                <a href="<?= $value["changelog_url"] ?>" class="btn btn-info btn-sm"><?= lang("info_icon") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    ajax_sort(site_url + "back/module/sortList");
    ajax_inline_boolean(module_url + 'back/module/editInline', false);
    $('.updatemodule').click(function() {
        let moduleID = $(this).data('search_data');
        let button = $(this);
        let data = {
            id: moduleID,
            [csrf_token_name]: csrf_hash
        };
        let submit_button_text = button.html();
        loadPermissionsort(button, data, submit_button_text, 'update/back');
    });
</script>