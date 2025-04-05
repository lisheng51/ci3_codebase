<form enctype="multipart/form-data" method="POST" id="send_multipart">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">Algemeen</div>
                <div class="card-body">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type*</label>
                            <?= $select_type ?>
                        </div>
                    </div>

                    <div class="col-md-8 <?= $title_status ?>">
                        <div class="form-group">
                            <label>Title*</label>
                            <input type="text" class="form-control" placeholder="Naam" name="title" value="<?= $rsdb["title"] ?? ""; ?>" />
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Bestand</label>
                            <?= $input_file ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <input type="hidden" name="upload_id" value="<?= $rsdb["upload_id"] ?? 0; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                        <?= $delButton ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<script>
    $("form#send_multipart").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>