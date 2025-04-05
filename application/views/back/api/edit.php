<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-3">
                    <label>Naam*</label>
                    <div class="form-group">
                        <input type="text" maxlength="100" class="form-control" name="name" required value="<?= $rsdb["name"] ?? "" ?>">
                    </div>
                </div>
                <div class="col-3">
                    <label>Key*</label>
                    <div class="form-group">

                        <input type="text" maxlength="32" class="form-control" name="secret" required value="<?= $rsdb["secret"] ?? md5(time()) ?>">
                    </div>
                </div>
                <div class="col-2">
                    <label>Token life time(min)*</label>
                    <div class="form-group">
                        <input type="number" min=15 max="1440" class="form-control" name="token_min" required value="<?= $rsdb["token_min"] ?? 30 ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label>Toestemming groep*</label>
                    <div class="form-group">
                        <?= PermissionGroupModel::selectMultiple(explode(',', $rsdb["permission_group_ids"] ?? ""), "", 2); ?>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="<?= ApiModel::$primaryKey ?>" value="<?= $rsdb[ApiModel::$primaryKey] ?? 0; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?= GlobalModel::editTimeInfo($rsdb) ?>
</div>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>