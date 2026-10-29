<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="mb-3">
                        <label>Domein*</label>
                        <input type="url" maxlength="100" class="form-control" name="domain" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button() ?>
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