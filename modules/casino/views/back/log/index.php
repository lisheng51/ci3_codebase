<?php
$get = fn(string $key) => htmlspecialchars((string)(CIInput()->get($key) ?? ''));
?>
<div class="card">
    <h5 class="card-header"><?= lang('search_box_header_text') ?></h5>
    <div class="card-body">
        <form id="form_search">
            <div class="row">
                <div class="col-12 col-lg-3">
                    <?= labelSelectInput('E-mail speler', 'email') ?>
                </div>
                <div class="col-12 col-lg-2">
                    <label>Speler ID</label>
                    <input type="number" name="casino_user_id" class="form-control" value="<?= $get('casino_user_id') ?>">
                </div>
                <div class="col-12 col-lg-3">
                    <label>Tafel</label>
                    <select name="casino_table_id" class="form-select">
                        <option value="">Alle</option>
                        <?php foreach ($tables as $t) : ?>
                            <option value="<?= (int)$t['casino_table_id'] ?>" <?= (string)$t['casino_table_id'] === (string)CIInput()->get('casino_table_id') ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <label>Spel</label>
                    <select name="game" class="form-select">
                        <option value="">Alle</option>
                        <?php foreach (CasinoTableModel::GAMES as $key => $name) : ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-2">
                    <label>Actie</label>
                    <select name="action" class="form-select">
                        <option value="">Alle</option>
                        <?php foreach (CasinoLogModel::ACTIONS as $key => $name) : ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 col-lg-2">
                    <label>Ronde</label>
                    <input type="number" name="round_no" class="form-control">
                </div>
                <div class="col-12 col-lg-2">
                    <label>Van datum</label>
                    <input type="date" name="date_from" class="form-control">
                </div>
                <div class="col-12 col-lg-2">
                    <label>Tot en met datum</label>
                    <input type="date" name="date_to" class="form-control">
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
                <?= select_order_by(CasinoLogModel::$selectOrderBy) ?>
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
