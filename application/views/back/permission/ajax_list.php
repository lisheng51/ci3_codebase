<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <span class="totalcount"><?= $total ?></span></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Path</th>
                        <th>Tag</th>
                        <th>Link title</th>
                        <th>Beschrijving</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) :
                        $start = $value['use_path'] > 0 ? $value['path'] : AccessCheckModel::$backPath;
                    ?>
                        <tr>
                            <td><?= $value["path_description"]; ?><input type="hidden" name="sort_list[<?= $value[PermissionModel::$primaryKey] ?>]" value="<?= $value['order_num'] ?>"></td>
                            <td><?= $value["link_dir"] ?></td>
                            <td><?= $start . '.' . $value["object"] . '.' . $value["method"]; ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" id="start_ajax_sort"><?= lang("sort_icon") ?></button>
                                <?= $value["link_title"] ?>
                            </td>
                            <td><?= $value["description"]; ?></td>
                            <td><?= $value["editButton"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    ajax_sort(site_url + "back/Permission/sortList");
    ajax_inline_edit(site_url + 'back/Permission/editInline');
</script>