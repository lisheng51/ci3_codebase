<?= $topbar ?>
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0"><?= htmlspecialchars($title) ?></h3>
        <a class="btn btn-outline-light" href="<?= $lobbyUrl ?>"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(CasinoLang::t('lobby')) ?></a>
    </div>
    <div class="table-responsive cs-card p-2">
        <table class="table table-sm table-dark table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th><?= CasinoLang::t('col_time') ?></th>
                    <th><?= CasinoLang::t('col_game') ?></th>
                    <th><?= CasinoLang::t('col_action') ?></th>
                    <th class="text-end"><?= CasinoLang::t('col_chips') ?></th>
                    <th class="text-end"><?= CasinoLang::t('col_balance') ?></th>
                    <th><?= CasinoLang::t('col_details') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listdb as $rs) : ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d-m H:i:s', strtotime($rs['created_at'])) ?></td>
                        <td><?= $rs['game'] !== '' ? htmlspecialchars(CasinoTableModel::gameName($rs['game'])) . ' #' . (int)$rs['round_no'] : '' ?></td>
                        <td><?= htmlspecialchars(CasinoLogModel::actionLabel($rs['action'])) ?></td>
                        <td class="text-end <?= $rs['amount'] > 0 ? 'text-success' : ($rs['amount'] < 0 ? 'text-danger' : '') ?>"><?= $rs['amount'] != 0 ? sprintf('%+d', $rs['amount']) : '' ?></td>
                        <td class="text-end"><?= (int)$rs['balance_after'] ?></td>
                        <td class="small"><?= htmlspecialchars($rs['detail']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
