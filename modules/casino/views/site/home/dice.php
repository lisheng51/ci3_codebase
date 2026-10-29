<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
$pip = function (int $n): string {
    $on = [1 => [4], 2 => [0, 8], 3 => [0, 4, 8], 4 => [0, 2, 6, 8], 5 => [0, 2, 4, 6, 8], 6 => [0, 2, 3, 5, 6, 8]][$n];
    $html = '<span class="pdie f' . $n . ($n === 1 || $n === 4 ? ' red' : '') . '" aria-hidden="true">';
    for ($i = 0; $i < 9; $i++) {
        $html .= in_array($i, $on, true) ? '<i class="on"></i>' : '<i></i>';
    }
    return $html . '</span>';
};
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ma+Shan+Zheng&display=swap&text=骰宝大小单双围对子点数两组合三军全">
<?= $topbar ?>
<div class="container-lg py-3 cs-dice">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-anim js-anim" aria-pressed="true" title="<?= $t('anim_toggle') ?>"><i class="fas fa-film"></i></button>
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
            <span class="cs-timer js-timer"></span>
        </div>
    </div>

    <div class="cs-felt p-3 mb-3">
        <div class="cs-dice-stage">
            <div class="cs-dice-row js-dice"><?= str_repeat(str_replace('class="pdie', 'class="pdie dim', $pip(1)), 3) ?></div>
            <div class="cs-status js-status"><?= $t('loading') ?></div>
        </div>

        <div class="sb-layout-switch">
            <button type="button" class="js-layout" data-layout="casino"><i class="fas fa-th-large"></i> <?= $t('dice_layout_casino') ?></button>
            <button type="button" class="js-layout" data-layout="list"><i class="fas fa-list"></i> <?= $t('dice_layout_list') ?></button>
        </div>

        <?php
        // casino board (as on the Holland Casino machines): odds shown as "1 pays x" from the current settings
        $pays = fn($x) => $t('dice_pays', ['n' => rtrim(rtrim(number_format((float)$x, 2, '.', ''), '0'), '.')]);
        $odds = [
            'bigsmall' => CasinoConfigModel::float('casino_dice_payout_bigsmall'),
            'double' => CasinoConfigModel::float('casino_dice_payout_double'),
            'specific' => CasinoConfigModel::float('casino_dice_payout_specific_triple'),
            'triple' => CasinoConfigModel::float('casino_dice_payout_triple'),
            'combo' => CasinoConfigModel::float('casino_dice_payout_combo'),
        ];
        $column = function (string $type, array $numbers) use ($t, $pip, $pays, $odds) {
            $html = '<div class="sb-col"><div class="sb-head">' . $pays($type === 'double' ? $odds['double'] : $odds['specific']) . '</div>';
            foreach ($numbers as $n) {
                $html .= '<button class="cs-spot sb-cell" data-spot="' . $type . '_' . $n . '"><span class="sb-label">'
                    . $t($type === 'double' ? 'dice_double_n' : 'dice_triple_n', ['n' => $n]) . '</span><span class="cs-pips' . ($type === 'triple' ? ' cs-pips-3' : '') . '">'
                    . str_repeat($pip($n), $type === 'double' ? 2 : 3) . '</span><b class="js-bet"></b></button>';
            }
            return $html . '</div>';
        };
        ?>
        <div class="sb-casino js-layout-pane" data-layout="casino">
            <div class="sb-top">
                <button class="cs-spot sb-small" data-spot="small">
                    <span class="sb-name"><?= $t('dice_small') ?></span>
                    <small><?= $t('dice_small_desc') ?></small>
                    <span class="sb-pays"><?= $pays($odds['bigsmall']) ?></span><b class="js-bet"></b>
                </button>
                <?= $column('double', [1, 2, 3]) ?>
                <?= $column('triple', [1, 2, 3]) ?>
                <div class="sb-col sb-any-col">
                    <div class="sb-head"><?= $pays($odds['triple']) ?></div>
                    <button class="cs-spot sb-any" data-spot="triple">
                        <span class="sb-label"><?= $t('dice_triple') ?></span>
                        <span class="sb-any-dice"><?php for ($i = 1; $i <= 6; $i++) : ?><span class="cs-pips cs-pips-3"><?= str_repeat($pip($i), 3) ?></span><?php endfor; ?></span>
                        <b class="js-bet"></b>
                    </button>
                </div>
                <?= $column('triple', [4, 5, 6]) ?>
                <?= $column('double', [4, 5, 6]) ?>
                <button class="cs-spot sb-big" data-spot="big">
                    <span class="sb-name"><?= $t('dice_big') ?></span>
                    <small><?= $t('dice_big_desc') ?></small>
                    <span class="sb-pays"><?= $pays($odds['bigsmall']) ?></span><b class="js-bet"></b>
                </button>
            </div>
            <div class="sb-totals">
                <?php foreach (CasinoConfigModel::diceTotals() as $total => $x) : ?>
                    <button class="cs-spot sb-total" data-spot="total_<?= $total ?>"><span class="sb-num"><?= $total ?></span><small><?= $pays($x) ?></small><b class="js-bet"></b></button>
                <?php endforeach; ?>
            </div>
            <div class="sb-combos">
                <?php for ($a = 1; $a <= 6; $a++) : ?>
                    <?php for ($b = $a + 1; $b <= 6; $b++) : ?>
                        <button class="cs-spot sb-cell" data-spot="combo_<?= $a ?>_<?= $b ?>"><span class="cs-pips"><?= $pip($a) . $pip($b) ?></span><b class="js-bet"></b></button>
                    <?php endfor; ?>
                <?php endfor; ?>
            </div>
            <div class="sb-caption"><?= $pays($odds['combo']) ?></div>
            <div class="sb-singles">
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <button class="cs-spot sb-single" data-spot="single_<?= $i ?>"><?= $pip($i) ?><span class="sb-num"><?= $i ?></span><b class="js-bet"></b></button>
                <?php endfor; ?>
            </div>
            <div class="sb-caption"><?= $t('dice_singles_rule', ['two' => CasinoConfigModel::float('casino_dice_payout_single2'), 'three' => CasinoConfigModel::float('casino_dice_payout_single3')]) ?> · <?= $t('dice_triple_rule') ?></div>
        </div>

        <div class="js-layout-pane" data-layout="list">
        <?php $head = fn(string $title, string $odds = '') => '<div class="cs-sec-head"><span class="cs-sec-title">' . $t($title) . '</span>' . ($odds !== '' ? '<span class="cs-sec-odds"><span data-odds="' . $odds . '"></span>:1</span>' : '') . '</div>'; ?>

        <?= $head('dice_sec_main', 'bigsmall') ?>
        <div class="cs-board cs-g4">
            <button class="cs-spot" data-spot="small"><span class="cs-spot-big"><?= $t('dice_small') ?></span><small>4 - 10</small><b class="js-bet"></b></button>
            <button class="cs-spot" data-spot="odd"><span class="cs-spot-big"><?= $t('dice_odd') ?></span><small><span data-odds="oddeven"></span>:1</small><b class="js-bet"></b></button>
            <button class="cs-spot" data-spot="even"><span class="cs-spot-big"><?= $t('dice_even') ?></span><small><span data-odds="oddeven"></span>:1</small><b class="js-bet"></b></button>
            <button class="cs-spot" data-spot="big"><span class="cs-spot-big"><?= $t('dice_big') ?></span><small>11 - 17</small><b class="js-bet"></b></button>
        </div>
        <div class="small cs-muted mb-2"><?= $t('dice_triple_rule') ?></div>

        <?= $head('dice_sec_doubles', 'double') ?>
        <div class="cs-board cs-g6">
            <?php for ($i = 1; $i <= 6; $i++) : ?>
                <button class="cs-spot" data-spot="double_<?= $i ?>"><span class="cs-pips"><?= $pip($i) . $pip($i) ?></span><b class="js-bet"></b></button>
            <?php endfor; ?>
        </div>

        <?= $head('dice_sec_triples', 'specificTriple') ?>
        <div class="cs-board cs-g6">
            <?php for ($i = 1; $i <= 6; $i++) : ?>
                <button class="cs-spot" data-spot="triple_<?= $i ?>"><span class="cs-pips cs-pips-3"><?= $pip($i) . $pip($i) . $pip($i) ?></span><b class="js-bet"></b></button>
            <?php endfor; ?>
        </div>
        <div class="cs-board">
            <button class="cs-spot cs-spot-row" data-spot="triple"><span class="cs-spot-big"><?= $t('dice_triple') ?></span><small><?= $t('dice_triple_hint') ?></small><small class="cs-gold"><span data-odds="triple"></span>:1</small><b class="js-bet"></b></button>
        </div>

        <?= $head('dice_sec_totals') ?>
        <div class="cs-board cs-g7 js-totals"></div>

        <?= $head('dice_sec_combos', 'combo') ?>
        <div class="cs-board cs-g5">
            <?php for ($a = 1; $a <= 6; $a++) : ?>
                <?php for ($b = $a + 1; $b <= 6; $b++) : ?>
                    <button class="cs-spot" data-spot="combo_<?= $a ?>_<?= $b ?>"><span class="cs-pips"><?= $pip($a) . $pip($b) ?></span><b class="js-bet"></b></button>
                <?php endfor; ?>
            <?php endfor; ?>
        </div>

        <?= $head('dice_sec_singles') ?>
        <div class="cs-board cs-g6">
            <?php for ($i = 1; $i <= 6; $i++) : ?>
                <button class="cs-spot cs-spot-single" data-spot="single_<?= $i ?>"><?= $pip($i) ?><b class="js-bet"></b></button>
            <?php endfor; ?>
        </div>
        <div class="small cs-muted"><?= $t('dice_singles_rule', ['two' => CasinoConfigModel::float('casino_dice_payout_single2'), 'three' => CasinoConfigModel::float('casino_dice_payout_single3')]) ?></div>
        </div>
    </div>

    <div class="sb-dock d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">
        <button type="button" class="btn btn-outline-light js-undo"><i class="fas fa-backspace"></i> <?= $t('dice_undo_last') ?></button>
        <div class="js-chips d-flex gap-1"></div>
        <button type="button" class="btn btn-outline-light js-double"><i class="fas fa-times"></i>2 <?= $t('dice_double_bets') ?></button>
        <button type="button" class="btn btn-outline-light js-rebet"><i class="fas fa-redo"></i> <?= $t('dice_rebet') ?></button>
        <button class="btn btn-outline-light js-clear"><i class="fas fa-undo"></i> <?= $t('clear_bet') ?></button>
        <button type="button" class="btn btn-lg btn-warning cs-go js-go d-none"><i class="fas fa-dice"></i> <?= $t('js_solo_go_dice') ?></button>
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
        <div class="col-md-6">
            <div class="cs-card p-2">
                <div class="fw-bold mb-1"><i class="fas fa-history"></i> <?= $t('last_rolls') ?></div>
                <div class="js-history small"></div>
            </div>
        </div>
    </div>
</div>

<script>
    window.CASINO_CONFIG = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?= script_tag(module_asset_url('js/casino.js')) ?>
<?= script_tag(module_asset_url('js/casino-sound.js')) ?>
<?= script_tag(module_asset_url('js/dice-shaker.js')) ?>
<?= script_tag(module_asset_url('js/dice.js')) ?>
