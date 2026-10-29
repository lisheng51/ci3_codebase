<div class="card">
    <h5 class="card-header"><?= lang('search_box_header_text') ?></h5>
    <div class="card-body">
        <form id="form_search">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <?= labelSelectInput('Naam', 'name') ?>
                </div>
                <div class="col-12 col-lg-4">
                    <?= labelSelectInput('E-mail', 'email') ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <?= add_csrf_value(); ?>
                    <?= search_button() ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-6 col-md-3 col-lg-2">
                <?= select_order_by(CasinoUserModel::$selectOrderBy) ?>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
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
</script>
