<div class="card">
    <div class="card-header"><?= lang('search_box_header_text') ?><div class="float-right"><?= $addButton ?></div>
    </div>
    <div class="card-body">
        <form id="form_search" method="POST">
            <div class="row">
                <div class="col-3">
                    <?= labelSelectInput('Titel', 'title') ?>
                </div>
                <div class="col-3">
                    <?= labelSelectSelectMulti('Type', UploadTypeModel::$primaryKey, UploadTypeModel::selectMultiple()) ?>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Datum</label>
                        <input type="text" name="reportrange" class="form-control" />
                    </div>
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
                <?= select_order_by(UploadModel::$selectOrderBy); ?>
            </div>
            <div class="col-2">
                <?= select_page_limit() ?>
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
    input_reportrange('input[name="reportrange"]');
</script>