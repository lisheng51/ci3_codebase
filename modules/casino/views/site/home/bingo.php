<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
?>
<?= $topbar ?>
<div class="container-lg py-3 cs-bingo">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-anim js-anim" aria-pressed="true" title="<?= $t('anim_toggle') ?>"><i class="fas fa-film"></i></button>
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
            <span class="cs-timer js-timer"></span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="cs-felt bg-stage p-3">
                <div class="bg-draw">
                    <div class="bg-ball-big js-bg-ball"></div>
                    <div class="bg-recent js-bg-recent"></div>
                </div>
                <div class="cs-status text-center js-status mt-2"><?= $t('loading') ?></div>
                <div class="bg-prizes">
                    <div class="bg-prize"><small><?= $t('bg_pot') ?></small><b class="js-bg-pot">0</b></div>
                    <div class="bg-prize"><small><?= $t('bg_line_prize') ?></small><b class="js-bg-line">0</b><span class="js-bg-line-by"></span></div>
                    <div class="bg-prize"><small><?= $t('bg_full_prize') ?></small><b class="js-bg-full">0</b><span class="js-bg-full-by"></span></div>
                </div>
            </div>

            <div class="bg-buy">
                <div class="js-bg-buy-buttons d-flex flex-wrap gap-2"></div>
                <button type="button" class="btn btn-outline-light js-clear"><i class="fas fa-undo"></i> <?= $t('bg_cancel') ?></button>
            </div>

            <div class="cs-card p-2 mt-3">
                <div class="d-flex justify-content-between fw-bold mb-2">
                    <span><i class="fas fa-th"></i> <?= $t('bg_my_cards') ?></span>
                    <span class="small js-bg-my-count"></span>
                </div>
                <div class="bg-cards js-bg-cards"></div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cs-card p-2 mb-3">
                <div class="fw-bold mb-2"><i class="fas fa-list-ol"></i> <?= $t('bg_board') ?></div>
                <div class="bg-board js-bg-board"></div>
            </div>
            <div class="cs-card p-2 mb-3">
                <div class="fw-bold mb-1"><i class="fas fa-users"></i> <?= $t('players_at_table') ?></div>
                <ul class="list-unstyled m-0 small js-players"></ul>
            </div>
            <div class="cs-card p-2">
                <div class="fw-bold mb-1"><i class="fas fa-history"></i> <?= $t('bg_history') ?></div>
                <ul class="list-unstyled m-0 small js-bg-history"></ul>
            </div>
        </div>
    </div>

    <div class="small cs-muted text-center mt-3"><?= $t('bg_rules', [
        'price' => (int)$table['min_bet'],
        'line' => CasinoConfigModel::int('casino_bingo_line_percent', 0, 100),
        'payout' => CasinoConfigModel::int('casino_bingo_payout_percent', 0, 100),
    ]) ?></div>

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
<?= script_tag(module_asset_url('js/bingo.js')) ?>
