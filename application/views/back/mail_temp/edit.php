<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Onderwerp*</label>
                        <input type="text" maxlength="100" class="form-control" name="subject" required value="<?= $rsdb["subject"] ?? ""; ?>" />
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label>Trigger*</label>
                        <input type="text" maxlength="50" class="form-control" name="trigger_name" required value="<?= $rsdb["trigger_name"] ?? ""; ?>" />
                    </div>
                </div>

                <div class="col-2">
                    <label>Taal*</label>
                    <div class="form-group">
                        <?= LanguageModel::select($rsdb[LanguageModel::$primaryKey] ?? 1); ?>
                    </div>
                </div>

                <div class="col-12">
                    <label>Inhoud*</label>
                    <div class="form-group">
                        <textarea class="form-control tinymce" name="body"><?= $rsdb["body"] ?? ""; ?></textarea>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Omschrijving</label>
                        <input type="text" maxlength="255" class="form-control" name="description" value="<?= $rsdb["description"] ?? ""; ?>" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= MailTempModel::$primaryKey ?>" value="<?= $rsdb[MailTempModel::$primaryKey] ?? 0; ?>" />
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