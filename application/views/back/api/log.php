<div class="card">
    <div class="card-header">Selectie</div>
    <div class="card-body">
        <form method="POST" id="form_search">
            <div class="row">
                <div class="col-3">
                    <?= labelSelectSelectMulti('API naam', 'api_id', ApiModel::selectMultiple(loadPostGet('api_id', 'array')), loadPostGet('api_id_operator')) ?>
                </div>
                <div class="col-3">
                    <?= labelSelectInput('IP', 'ip_address', loadPostGet('ip_address'), loadPostGet('ip_address_operator')) ?>
                </div>
                <div class="col-3">
                    <?= labelSelectInput('POST', 'post_value', loadPostGet('post_value'), loadPostGet('post_value_operator')) ?>
                </div>
                <div class="col-3">
                    <?= labelSelectInput('GET', 'get_value', loadPostGet('get_value'), loadPostGet('get_value_operator')) ?>
                </div>
                <div class="col-3">
                    <?= labelSelectInput('Header', 'header_value', loadPostGet('header_value'), loadPostGet('header_value_operator')) ?>
                </div>
                <!--                <div class="col-3">
                    <div class="form-group">
                        <label>Datum</label>
                        <input type="text" name="reportrange" class="form-control" />
                    </div> 
                </div>-->
                <div class="col-3">
                    <?= labelSelectInput('Path/url', 'path', loadPostGet('path'), loadPostGet('path_operator')) ?>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Datum&tijd begin</label>
                        <input type="text" name="reportrange_start" class="form-control" value="<?= loadPostGet('reportrange_start') ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Datum&tijd eind</label>
                        <input type="text" name="reportrange_end" class="form-control" value="<?= loadPostGet('reportrange_end') ?>" />
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
                <?= select_order_by(ApiLogModel::$selectOrderBy); ?>
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
    //input_reportrange('input[name="reportrange"]');
    input_datewithtime('input[name="reportrange_start"]');
    input_datewithtime('input[name="reportrange_end"]');
</script>