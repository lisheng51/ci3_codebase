<form method="POST" id="form_search">
    <div class="card">
        <div class="card-header"><?= lang('search_box_header_text') ?><div class="float-right"><?= $addButton ?></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <?= labelSelectInput('Email', 'search_email', loadPostGet('search_email'), loadPostGet('search_email_operator')) ?>
                </div>
                <div class="col-2">
                    <label>Is active</label>
                    <div class="form-group">
                        <?= select_boolean("is_active", 2, true); ?>
                    </div>
                </div>
                <div class="col-6">
                    <?= labelSelectSelectMulti('Toestemming groep', 'permission_group_id', PermissionGroupModel::selectMultiple(loadPostGet('permission_group_id', 'array')), loadPostGet('permission_group_id_operator')) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= add_csrf_value(); ?>
                    <?= search_button() ?>
                    <?= reset_button() ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-2">
                    <?= select_order_by(UserModel::$selectOrderBy); ?>
                </div>
                <div class="col-2">
                    <?= select_page_limit() ?>
                </div>
            </div>
        </div>
    </div>
</form>

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