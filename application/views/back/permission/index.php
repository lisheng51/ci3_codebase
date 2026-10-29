<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><?= lang('search_box_header_text') ?>
                <div class="flex-shrink-0"><?= addButton($controller_url . '.add', $controller_url . '/add') ?></div>
            </div>
            <div class="card-body">
                <form method="POST" id="form_search">
                    <div class="row">
                        <div class="col-12 col-lg-3">
                            <?= labelSelectSelectMulti('Module', ModuleModel::$primaryKey, ModuleModel::selectMultiple()) ?>
                        </div>
                        <div class="col-12 col-lg-2">
                            <label>Path</label>
                            <div class="mb-3">
                                <?= PermissionGroupTypeModel::selectPath("", true) ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <?= labelSelectInput('Beschrijving', 'description') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?= add_csrf_value(); ?>
                            <?= search_button() ?>
                            <?= reset_button() ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6 col-md-3 col-lg-2">
                        <?= select_order_by(PermissionModel::$selectOrderBy, 'order_num#asc') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12" id="ajax_search_content">
        <?= $result; ?>
    </div>
</div>

<script>
    $("form#form_search").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>