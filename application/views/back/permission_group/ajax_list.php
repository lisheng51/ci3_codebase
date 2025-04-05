<div class="card">
    <div class="card-header">Resultaten - Totaal gevonden: <?= $total ?></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Info</th>
                        <th>Naam</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><button class="btn btn-info btn-sm" data-toggle="collapse" data-target="#Info_<?= $value[PermissionGroupModel::$primaryKey]; ?>">Info</button> </td>
                            <td>
                                <input type="hidden" name="sort_list[<?= $value[PermissionGroupModel::$primaryKey] ?>]" value="<?= $value['sort_list_group'] ?>">
                                <button type="button" class="btn btn-info btn-sm" id="start_ajax_sort"><?= lang("sort_icon") ?></button>
                                <?= $value["name"] ?>
                            </td>
                            <td><?= $value["type_name"]; ?></td>
                            <td><?= $value["editButton"] ?></td>
                        </tr>

                        <tr>
                            <td colspan="4">
                                <ul class="list-group collapse" id="Info_<?= $value[PermissionGroupModel::$primaryKey]; ?>">
                                    <?php foreach ($value["listdbPermission"] as $module => $listdb2) : list($name, $toggleId) = explode("#_#", $module); ?>
                                        <li class="list-group-item"><?= $name ?>:<br>
                                            <?php foreach ($listdb2 as $value2) : ?>
                                                <span class="badge badge-info"><?= $value2['checkbox_label'] ?></span>
                                            <?php endforeach; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    ajax_sort(site_url + "back/Permission_group/sortList");
    ajax_inline_edit(site_url + 'back/Permission_group/editInline');
</script>