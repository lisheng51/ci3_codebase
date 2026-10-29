<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
$game = $table['game'] === 'xiangqi' ? 'xiangqi' : 'chess';
?>
<?= $topbar ?>
<div class="container-lg py-3 cs-boardgame" data-game="<?= $game ?>">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
            <span class="cs-timer js-timer"></span>
        </div>
    </div>

    <div class="bd-layout">
        <div class="cs-felt bd-felt">
            <div class="bd-board bd-<?= $game ?> js-board"></div>
        </div>

        <div class="bd-side">
            <div class="bd-player js-player" data-place="top"></div>
            <div class="bd-status js-bd-status"><?= $t('loading') ?></div>
            <div class="bd-actions js-bd-actions"></div>
            <div class="bd-player js-player" data-place="bottom"></div>
            <div class="cs-card p-2 bd-moves-card">
                <div class="fw-bold small mb-1"><i class="fas fa-list-ol"></i> <?= $t('js_board_moves') ?></div>
                <ol class="bd-moves js-bd-moves"></ol>
            </div>
            <div class="small cs-muted"><?= $t('board_rules_' . $game, ['stake' => $config['minBet']]) ?></div>
        </div>
    </div>

    <div class="cs-card p-2 my-3 cs-records">
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
<?= script_tag(module_asset_url('js/board.js')) ?>
