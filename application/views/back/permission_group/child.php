<?php foreach ($_child as $value) : ?>
    <li style="list-style-type: none;">
        <div class="form-check mb-3">
            <input class="form-check-input label_module_id_<?= $value["toggleId"] ?>" type="checkbox" <?= $value['checkbox_value'] ?> name="permission_id[]" value="<?= $value['permission_id'] ?>" id="label_permission_id_<?= $value['permission_id'] ?>">
            <label style="padding-right: 30px"><?= $value["subject_with_level"] ?></label><label class="form-check-label" for="label_permission_id_<?= $value['permission_id'] ?>"> <?= $value['checkbox_label'] ?><a href="<?= site_url($value['debug_link']) ?>" class="badge text-bg-primary"><?= $value['debug_link'] ?></a></label>
        </div>
        <ul style="padding-left: 0;">
            <?= $value["show_child"]; ?>
        </ul>
    </li>
<?php endforeach; ?>