<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
[$minBuy, $maxBuy] = CasinoDoudizhuModel::buyinRange($table);
?>
<?= $topbar ?>
<div class="container-lg py-3 cs-ddz">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
            <span class="cs-timer js-timer"></span>
        </div>
    </div>

    <div class="cs-felt dz-felt p-3 mb-2">
        <!-- the bottom cards and the score of this hand -->
        <div class="dz-top">
            <div class="dz-bottom js-dz-bottom"></div>
            <div class="dz-score">
                <span><?= $t('ddz_base') ?> <b class="js-dz-base"></b></span>
                <span><?= $t('ddz_bid') ?> <b class="js-dz-bid">-</b></span>
                <span><?= $t('ddz_mult') ?> <b class="js-dz-mult">x1</b></span>
            </div>
        </div>

        <!-- the two other players: left and right of me -->
        <div class="dz-table">
            <div class="dz-side dz-left js-dz-seat" data-place="left"></div>
            <div class="dz-center">
                <div class="dz-status js-dz-status"><?= $t('loading') ?></div>
            </div>
            <div class="dz-side dz-right js-dz-seat" data-place="right"></div>
        </div>

        <!-- me: what I played, my cards, my buttons -->
        <div class="dz-me js-dz-seat" data-place="me"></div>
        <div class="dz-hand js-dz-hand"></div>
        <div class="dz-actions js-dz-actions"></div>
    </div>

    <div class="small cs-muted text-center mb-3 dz-rules"><?= $t('ddz_rules', ['min' => $minBuy, 'max' => $maxBuy, 'bomb' => CasinoConfigModel::int('casino_ddz_bomb_mult', 1, 100), 'rocket' => CasinoConfigModel::int('casino_ddz_rocket_mult', 1, 100)]) ?></div>

    <div class="cs-card p-2 mb-3 cs-records">
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
<?= script_tag(module_asset_url('js/doudizhu.js')) ?>
