<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label>Woord*</label>
                        <input type="text" class="form-control" name="word" required value="<?= $rsdb["word"] ?? ""; ?>" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= SpamwordModel::$primaryKey ?>" value="<?= $rsdb[SpamwordModel::$primaryKey] ?? 0; ?>" />
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
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>