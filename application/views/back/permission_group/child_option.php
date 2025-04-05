<?php foreach ($_child as $value) : ?>
    <tr class="treegrid-<?= $value[PermissionModel::$primaryKey] ?> treegrid-parent-<?= $value["parent_id"] ?>">
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