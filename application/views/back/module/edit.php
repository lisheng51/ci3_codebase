<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>Naam</label>
                        <input type="text" class="form-control" maxlength="200" name="path_description" value="<?= $rsdb["path_description"]; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Path*</label>
                        <input type="text" class="form-control" maxlength="50" name="path" readonly value="<?= $rsdb["path"]; ?>" />
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-group">
                        <label>Is actief</label>
                        <?= select_boolean('is_active', intval($rsdb["is_active"] ?? 0)); ?>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= ModuleModel::$primaryKey ?>" value="<?= $rsdb[ModuleModel::$primaryKey]; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                        <?= $delButton; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("form#send").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>