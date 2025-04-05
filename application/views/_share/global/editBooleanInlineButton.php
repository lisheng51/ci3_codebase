<label class="ms-switch">
    <input type="checkbox" <?= $value > 0 ? "checked" : "" ?>>
    <span class="ms-switch-slider ms-switch-<?= $style ?> round inline_boolean" data-field="<?= $field ?>" data-edit-id="<?= $editId; ?>" data-value="<?= $value; ?>"></span>
</label>