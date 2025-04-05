<div class="card">
    <div class="card-header">Algemeen</div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-2">
                    <label>Module*</label>
                    <div class="form-group">
                        <?= ModuleModel::select($rsdb[ModuleModel::$primaryKey] ?? 0) ?>
                    </div>
                </div>
                <div class="col-2">
                    <label>Path*</label>
                    <div class="form-group">
                        <?= PermissionGroupTypeModel::selectPath($rsdb["link_dir"] ?? "extra") ?>
                    </div>
                </div>
                <div class="col-2">
                    <label>Object*</label>
                    <div class="form-group">
                        <input type="text" required class="form-control" name="object" value="<?= $rsdb["object"] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-2">
                    <label>Method*</label>
                    <div class="form-group">
                        <input type="text" required class="form-control" name="method" value="<?= $rsdb["method"] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-2">
                    <label>Parent</label>
                    <div class="form-group">
                        <input type="number" class="form-control" name="parent_id" value="<?= $rsdb["parent_id"] ?? 0 ?>" />
                    </div>
                </div>
                <div class="col-2">
                    <label>Order nummer</label>
                    <div class="form-group">
                        <input type="number" class="form-control" name="order_num" value="<?= $rsdb["order_num"] ?? 0 ?>" />
                    </div>
                </div>
                <div class="col-4">
                    <label>Link titel</label>
                    <div class="form-group">
                        <input type="text" class="form-control" maxlength="50" name="link_title" value="<?= $rsdb["link_title"] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-8">
                    <label>Beschrijving</label>
                    <div class="form-group">
                        <input type="text" class="form-control" maxlength="200" name="description" value="<?= $rsdb["description"] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="permission_id" value="<?= $rsdb["permission_id"] ?? 0 ?>" />
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
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>