<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-12">
                    <label>JSON</label>
                    <div class="form-group">
                        <textarea class="form-control" rows="10" required name="content"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <?= add_csrf_value(); ?>
                        <button class="btn btn-info" type="submit">Importeren</button>
                        <?= add_reset_button() ?>
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