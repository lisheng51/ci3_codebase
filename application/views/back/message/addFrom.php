<div class="card">
    <div class="card-header">Aan: <?= $to_user_name ?></div>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-md-12">
                    <label>Onderwerp*</label>
                    <div class="form-group">
                        <input type="text" class="form-control" required name="title" />
                    </div>
                </div>

                <div class="col-md-12">
                    <label>Inhoud*</label>
                    <div class="form-group">
                        <textarea class="form-control tinymce_noxss_clean" name="content"></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <input type="hidden" name="to_user_id" value="<?= $to_user_id; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button() ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    setup_tinymce_noxss_clean();
    $("form#send").submit(function(e) {
        tinymce.triggerSave();
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>