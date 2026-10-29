<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
$cell = function (string $spot, string $label, string $class = '', string $style = '') {
    return '<button class="rl-cell ' . $class . '" data-spot="' . $spot . '"' . ($style !== '' ? ' style="' . $style . '"' : '') . '><span>' . $label . '</span><b class="js-bet"></b></button>';
};
?>
<?= $topbar ?>
<div class="container-lg py-3 cs-roulette">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-anim js-anim" aria-pressed="true" title="<?= $t('anim_toggle') ?>"><i class="fas fa-film"></i></button>
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
            <span class="cs-timer js-timer"></span>
        </div>
    </div>

    <div class="cs-felt rl-felt p-3 mb-3">
        <div class="rl-top">
            <div class="rl-last js-last"><span class="wheel-num green">0</span></div>
            <div class="flex-grow-1">
                <div class="cs-status js-status"><?= $t('loading') ?></div>
                <div class="rl-history js-history"></div>
            </div>
        </div>

        <div class="rl-board-wrap">
            <div class="rl-board">
                <?= $cell('n_0', '0', 'green zero', 'grid-row: 1 / 4; grid-column: 1;') ?>
                <?php for ($row = 0; $row < 3; $row++) : ?>
                    <?php for ($col = 0; $col < 12; $col++) :
                        $n = $col * 3 + (3 - $row); ?>
                        <?= $cell('n_' . $n, (string)$n, CasinoRouletteModel::color($n), 'grid-row: ' . ($row + 1) . '; grid-column: ' . ($col + 2) . ';') ?>
                    <?php endfor; ?>
                    <?= $cell('column_' . (3 - $row), '2:1', 'outside', 'grid-row: ' . ($row + 1) . '; grid-column: 14;') ?>
                <?php endfor; ?>
                <?php for ($d = 1; $d <= 3; $d++) : ?>
                    <?= $cell('dozen_' . $d, $t('rl_dozen_' . $d), 'outside', 'grid-row: 4; grid-column: ' . (2 + ($d - 1) * 4) . ' / span 4;') ?>
                <?php endfor; ?>
                <?= $cell('low', $t('rl_low'), 'outside', 'grid-row: 5; grid-column: 2 / span 2;') ?>
                <?= $cell('even', $t('js_even'), 'outside', 'grid-row: 5; grid-column: 4 / span 2;') ?>
                <?= $cell('red', '<i class="rl-diamond red"></i>', 'outside', 'grid-row: 5; grid-column: 6 / span 2;') ?>
                <?= $cell('black', '<i class="rl-diamond black"></i>', 'outside', 'grid-row: 5; grid-column: 8 / span 2;') ?>
                <?= $cell('odd', $t('js_odd'), 'outside', 'grid-row: 5; grid-column: 10 / span 2;') ?>
                <?= $cell('high', $t('rl_high'), 'outside', 'grid-row: 5; grid-column: 12 / span 2;') ?>
            </div>
        </div>
        <div class="small cs-muted text-center mt-2"><?= $t('rl_zero_rule') ?></div>
    </div>

    <div class="rl-dock d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">
        <div class="js-chips d-flex gap-1"></div>
        <button class="btn btn-outline-light js-clear"><i class="fas fa-undo"></i> <?= $t('clear_bet') ?></button>
        <button type="button" class="btn btn-lg btn-warning cs-go js-go d-none"><i class="fas fa-circle-notch"></i> <?= $t('js_solo_go_roulette') ?></button>
    </div>

    <div class="cs-card p-2 mb-3 cs-records">
        <div class="d-flex justify-content-between align-items-baseline fw-bold mb-1">
            <span><i class="fas fa-receipt"></i> <?= htmlspecialchars(CasinoLang::t('my_records')) ?></span>
            <span class="small js-records-total"></span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-dark align-middle mb-0 cs-records-table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars(CasinoLang::t('rec_round')) ?></th>
                        <th><?= htmlspecialchars(CasinoLang::t('rec_result')) ?></th>
                        <th class="text-end"><?= htmlspecialchars(CasinoLang::t('rec_bet')) ?></th>
                        <th class="text-end"><?= htmlspecialchars(CasinoLang::t('rec_net')) ?></th>
                    </tr>
                </thead>
                <tbody class="js-records"></tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="cs-card p-2">
                <div class="fw-bold mb-1"><i class="fas fa-users"></i> <?= $t('players_at_table') ?></div>
                <ul class="list-unstyled m-0 js-players small"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    window.CASINO_CONFIG = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?= script_tag(module_asset_url('js/casino.js')) ?>
<?= script_tag(module_asset_url('js/casino-sound.js')) ?>
<?= script_tag(module_asset_url('js/roulette-wheel.js')) ?>
<?= script_tag(module_asset_url('js/roulette.js')) ?>
