<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><?= lang('search_box_header_text') ?><div class="float-right"><?= $addButton ?></div>
            </div>
            <div class="card-body">
                <form method="POST" id="form_search">
                    <div class="row">
                        <div class="col-4">
                            <?= labelSelectInput('Naam', 'name') ?>
                        </div>
                        <div class="col-2">
                            <?= labelSelectSelectMulti('Type', 'permission_group_type_id', PermissionGroupTypeModel::selectMultiple()) ?>
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
                    <div class="col-2">
                        <?= select_order_by(PermissionGroupModel::$selectOrderBy, 'sort_list_group#asc'); ?>
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
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>