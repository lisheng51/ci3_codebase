<div class="card">
    <div class="card-header"><?= lang('search_box_header_text') ?><div class="float-right"><?= $addButton ?></div>
    </div>
    <div class="card-body">
        <form method="POST" id="form_search">
            <div class="row">
                <div class="col-3">
                    <?= labelSelectInput('Onderwerp', 'title') ?>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Datum</label>
                        <input type="text" name="reportrange" class="form-control" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Status</label>
                        <?= $select_open_status ?>
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
                <?= select_order_by(MessageModel::$selectOrderBy); ?>
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
        e.preventDefault();
        ajax_form_search($(this));
    });
    input_reportrange('input[name="reportrange"]');
</script>