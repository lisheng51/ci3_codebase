<?= $topbar ?>
<div class="container-lg py-3 cs-bj">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(CasinoLang::t('lobby')) ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?> <small class="cs-muted js-round"></small></h5>
        <span class="cs-timer js-timer"></span>
    </div>

    <div class="cs-felt cs-felt-bj p-3 mb-3">
        <div class="cs-dealer text-center">
            <div class="cs-label"><?= htmlspecialchars(CasinoLang::t('bj_dealer')) ?> <span class="cs-value js-dealer-value"></span></div>
            <div class="cs-hand js-dealer"></div>
            <div class="cs-rules small cs-muted js-rules"></div>
        </div>
        <div class="cs-status text-center js-status my-2"><?= htmlspecialchars(CasinoLang::t('loading')) ?></div>
        <div class="cs-seats js-seats"></div>
    </div>

    <div class="cs-controls text-center">
        <div class="js-bet-controls d-none">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                <div class="js-chips d-flex gap-1"></div>
                <button class="btn btn-warning js-bet"><i class="fas fa-coins"></i> <?= htmlspecialchars(CasinoLang::t('bj_bet')) ?></button>
                <button class="btn btn-outline-light js-clear"><i class="fas fa-undo"></i> <?= htmlspecialchars(CasinoLang::t('bj_back')) ?></button>
                <button type="button" class="btn btn-lg btn-warning cs-go js-go d-none"><i class="fas fa-layer-group"></i> <?= htmlspecialchars(CasinoLang::t('js_solo_go_bj')) ?></button>
            </div>
        </div>
        <div class="js-play-controls d-none">
            <button class="btn btn-lg btn-success js-act" data-action="hit"><i class="fas fa-plus"></i> <?= htmlspecialchars(CasinoLang::t('bj_hit')) ?></button>
            <button class="btn btn-lg btn-danger js-act" data-action="stand"><i class="fas fa-hand-paper"></i> <?= htmlspecialchars(CasinoLang::t('bj_stand')) ?></button>
            <button class="btn btn-lg btn-warning js-act" data-action="double"><i class="fas fa-angle-double-up"></i> <?= htmlspecialchars(CasinoLang::t('bj_double')) ?></button>
        </div>
        <div class="js-sit-controls d-none">
            <span class="cs-muted"><?= htmlspecialchars(CasinoLang::t('bj_sit_hint')) ?></span>
        </div>
    </div>

    <div class="cs-card p-2 mt-3 mb-3 cs-records">
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

</div>

<script>
    window.CASINO_CONFIG = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?= script_tag(module_asset_url('js/casino.js')) ?>
<?= script_tag(module_asset_url('js/blackjack.js')) ?>
