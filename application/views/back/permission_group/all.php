<?= link_tag(sys_asset_url("treetable/jquery.treegrid.css")); ?>
<?= script_tag(sys_asset_url("treetable/jquery.treegrid.min.js")); ?>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover tree">
                <thead>
                    <tr>
                        <th></th>
                        <?php foreach ($listdbGroup as $item) : ?>
                            <th><?= $item["name"] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
    
                        <tr class="treegrid-<?= $value[PermissionModel::$primaryKey] ?>">
                            <td>[<?= $value["path_description"] ?>] <?= $value["description"] ?></td>
                            <?php foreach ($value['allids'] as $gid) :
                                $uniqueForId = 'label_pidgid_' . $value[PermissionModel::$primaryKey] . '_' . $gid;
                                $ischecked = in_array($gid, $value['gids']) ? "checked" : "";
                            ?>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input actionpermissionset" data-permissionid="<?= $value[PermissionModel::$primaryKey] ?>" data-groupid="<?= $gid ?>" type="checkbox" <?= $ischecked ?> id="<?= $uniqueForId ?>">
                                        <label class="form-check-label" for="<?= $uniqueForId ?>"></label>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?= $value["show_child"]; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $('.tree').treegrid({
        //initialState: 'collapsed',
        expanderExpandedClass: 'fa-solid fa-minus',
        expanderCollapsedClass: 'fa-solid fa-plus'
    });

    $(".actionpermissionset").change(function() {
        let permissionid = $(this).data('permissionid');
        let groupid = $(this).data('groupid');
        let ajaxurl = site_url + "back/permission_group/update?gid=" + groupid + "&pid=" + permissionid;
        myfetch({}, ajaxurl).then(function(json) {
            console.log(json);
        });
    });
</script>