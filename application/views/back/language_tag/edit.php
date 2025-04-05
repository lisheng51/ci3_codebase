<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-10">
                    <div class="form-group">
                        <label>Tag*</label>
                        <input type="text" maxlength="100" class="form-control" name="tag" required value="<?= $rsdb["tag"] ?? ""; ?>" />
                    </div>
                </div>

                <div class="col-2">
                    <label>Taal*</label>
                    <div class="form-group">
                        <?= LanguageModel::select($rsdb[LanguageModel::$primaryKey] ?? 1); ?>
                    </div>
                </div>

                <div class="col-12">
                    <label>Waarde*</label>
                    <div class="form-group">
                        <textarea class="form-control <?= $showTinymce ?>" name="value"><?= $rsdb["value"] ?? ""; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= LanguageTagModel::$primaryKey ?>" value="<?= $rsdb[LanguageTagModel::$primaryKey] ?? 0; ?>" />
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
        tinyMCE.triggerSave();
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>