<div class="card">
    <h5 class="card-header">Algemene informatie</h5>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-4">
                    <div class="mb-3">
                        <label>Naam*</label>
                        <input type="text" class="form-control" maxlength="50" ax placeholder="Naam" name="name" required value="<?php echo $rsdb["name"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-8">
                    <div class="mb-3">
                        <label>Omschrijving</label>
                        <textarea class="form-control" maxlength="200" rows="4" name="description"><?php echo $rsdb["description"] ?? ""; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
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
    $("form#send").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>