<form id="send">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <?php foreach ($listdb as $rs) : ?>
                    <div class="col-12 col-lg-3">
                        <label><?= $rs['tag'] ?></label>
                        <div class="mb-3">
                            <textarea class="form-control" rows="4" name="view[<?= $rs[LanguageTagModel::$primaryKey] ?>]"><?= $rs['value'] ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="mb-3 d-flex flex-wrap gap-2">
                <?= add_csrf_value(); ?>
                <?= add_submit_button('lang') ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>