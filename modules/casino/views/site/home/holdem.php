<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
[$sb, $bb] = CasinoHoldemModel::blinds($table);
[$minBuy, $maxBuy] = CasinoHoldemModel::buyinRange($table);
?>
<?= $topbar ?>
<div class="container-lg py-3 cs-holdem">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
    </div>

    <div class="pk-table-wrap">
        <div class="pk-table js-pk-table">
            <div class="pk-felt"></div>
            <div class="pk-center">
                <div class="pk-pot js-pot"></div>
                <div class="pk-board js-board"></div>
                <div class="pk-result js-pk-result"></div>
            </div>
            <div class="js-seats"></div>
        </div>
    </div>

    <div class="pk-controls">
        <div class="pk-info js-pk-info"><?= $t('loading') ?></div>
        <div class="pk-actions js-pk-actions d-none">
            <button type="button" class="btn btn-lg btn-danger js-pk-act" data-action="fold"></button>
            <button type="button" class="btn btn-lg btn-light js-pk-act" data-action="check"></button>
            <button type="button" class="btn btn-lg btn-success js-pk-act" data-action="call"></button>
            <div class="pk-raise js-pk-raise">
                <div class="pk-raise-row">
                    <input type="range" class="form-range js-raise-range" min="0" max="0" step="1">
                    <input type="number" class="form-control form-control-sm js-raise-amount" min="0" step="1">
                </div>
                <div class="pk-raise-row">
                    <button type="button" class="btn btn-sm btn-outline-light js-raise-preset" data-preset="min"></button>
                    <button type="button" class="btn btn-sm btn-outline-light js-raise-preset" data-preset="half"></button>
                    <button type="button" class="btn btn-sm btn-outline-light js-raise-preset" data-preset="pot"></button>
                    <button type="button" class="btn btn-warning js-pk-act" data-action="raise"></button>
                </div>
            </div>
            <button type="button" class="btn btn-lg btn-outline-warning js-pk-act" data-action="allin"></button>
        </div>
        <div class="small cs-muted text-center mt-2"><?= $t('pk_rules', ['sb' => $sb, 'bb' => $bb, 'min' => $minBuy, 'max' => $maxBuy]) ?></div>
    </div>

    <div class="cs-card p-2 mt-3 cs-records">
        <div class="d-flex justify-content-between align-items-baseline fw-bold mb-1">
            <span><i class="fas fa-receipt"></i> <?= $t('my_records') ?></span>
            <span class="small js-records-total"></span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-dark align-middle mb-0 cs-records-table">
                <thead>
                    <tr>
                        <th><?= $t('rec_round') ?></th>
                        <th><?= $t('rec_result') ?></th>
                        <th class="text-end"><?= $t('rec_bet') ?></th>
                        <th class="text-end"><?= $t('rec_net') ?></th>
                    </tr>
                </thead>
                <tbody class="js-records"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.CASINO_CONFIG = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?= script_tag(module_asset_url('js/casino.js')) ?>
<?= script_tag(module_asset_url('js/casino-sound.js')) ?>
<?= script_tag(module_asset_url('js/holdem.js')) ?>
