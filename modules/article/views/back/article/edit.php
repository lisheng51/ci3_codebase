<div class="card">
    <h5 class="card-header">Algemene informatie</h5>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">

                <div class="col-12 col-sm-6 col-lg-2">
                    <label>Categorie*</label>
                    <div class="mb-3">
                        <?php echo ArticleCateModel::select($rsdb["cate_id"] ?? 0); ?>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label>Datum*</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="created_at" maxlength="19" value="<?php echo $created_at; ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <label>Titel*</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="title" required maxlength="80" value="<?php echo $rsdb["title"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-12">
                    <label>Omschrijving</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="description" maxlength="255" value="<?php echo $rsdb["description"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <textarea name="content" class="form-control tinymce_noxss_clean"><?php echo $rsdb["content"] ?? ""; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <?php echo add_csrf_value(); ?>
                        <?php echo add_submit_button($rsdb) ?>
                        <?php echo add_reset_button() ?>
                        <?php echo $delButton ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php echo GlobalModel::editTimeInfo($rsdb) ?>
</div>

<script>
    setup_tinymce_noxss_clean('.tinymce_noxss_clean', window.innerWidth < 768 ? 400 : 600);
    $("form#send").submit(function(e) {
        tinyMCE.triggerSave();
        e.preventDefault();
        ajax_form_search($(this));
    });
    input_datewithtime('input[name="created_at"]');
</script>