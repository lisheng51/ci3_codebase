<div class="card">
    <h5 class="card-header"><?= lang('search_box_header_text') ?><div class="float-end"><?= $addButton ?></div>
    </h5>
    <div class="card-body">
        <form id="form_search">
            <div class="row">
                <div class="col-4">
                    <?= labelSelectInput('Naam', 'name') ?>
                </div>
                <div class="col-3">
                    <label>Spel</label>
                    <select name="game" class="form-select">
                        <option value="">Alle</option>
                        <?php foreach (CasinoTableModel::GAMES as $key => $name) : ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
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
            <div class="col-2">
                <?= select_order_by(CasinoTableModel::$selectOrderBy) ?>
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
</script>
