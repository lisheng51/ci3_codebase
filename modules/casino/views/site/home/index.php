<?= $topbar ?>
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h3 class="m-0"><?= htmlspecialchars(CasinoLang::t('choose_table')) ?></h3>
        <div class="d-flex gap-2">
            <?php if ($bonusWait >= 0) : ?>
                <button class="btn btn-warning js-bonus" <?= $bonusWait > 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-gift"></i>
                    <?= $bonusWait > 0 ? htmlspecialchars(CasinoLang::t('bonus_wait', ['hours' => ceil($bonusWait / 3600)])) : htmlspecialchars(CasinoLang::t('bonus_claim', ['amount' => $bonusAmount])) ?>
                </button>
            <?php endif; ?>
            <a class="btn btn-outline-light" href="<?= $historyUrl ?>"><i class="fas fa-history"></i> <?= htmlspecialchars(CasinoLang::t('history')) ?></a>
        </div>
    </div>

    <?php if (empty($listdb)) : ?>
        <div class="alert alert-info"><?= htmlspecialchars(CasinoLang::t('no_tables')) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($listdb as $rs) : ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="<?= $rs['play_url'] ?>" class="cs-table-card cs-game-<?= htmlspecialchars($rs['game']) ?>">
                    <div class="cs-table-icon"><i class="fas <?= ['blackjack' => 'fa-crown', 'roulette' => 'fa-circle-notch', 'slot' => 'fa-lemon', 'holdem' => 'fa-heart', 'bingo' => 'fa-th', 'doudizhu' => 'fa-hat-cowboy', 'chess' => 'fa-chess-knight', 'xiangqi' => 'fa-chess-board'][$rs['game']] ?? 'fa-dice' ?>"></i></div>
                    <div class="cs-table-name"><?= htmlspecialchars($rs['name']) ?></div>
                    <div class="cs-table-game"><?= htmlspecialchars($rs['game_name']) ?><?= CasinoTableModel::isSolo($rs) ? ' <span class="cs-solo-badge">' . htmlspecialchars(CasinoLang::t('solo')) . '</span>' : '' ?></div>
                    <div class="cs-table-meta">
                        <span><i class="fas fa-coins"></i> <?= (int)$rs['min_bet'] ?> - <?= (int)$rs['max_bet'] ?></span>
                        <span><i class="fas fa-user"></i> <?= htmlspecialchars(CasinoLang::t('online', ['count' => (int)$rs['online']])) ?></span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    $('.js-bonus').on('click', function() {
        let btn = $(this).prop('disabled', true);
        let formData = new FormData();
        formData.set(csrf_token_name, csrf_hash);
        myfetch({
            method: 'POST',
            body: formData
        }, '<?= $bonusUrl ?>').then(function(json) {
            if (!json) return;
            if (json.status === 'good') {
                $('.js-balance').text(Number(json.balance).toLocaleString(document.documentElement.lang));
                btn.html('<i class="fas fa-check"></i> ' + json.msg);
            } else {
                btn.text(json.msg);
            }
        });
    });
</script>
