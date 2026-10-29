<div class="card">
    <h5 class="card-header">Aantal gevonden: <span class="totalcount"><?= $total ?></span></h5>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tijd</th>
                        <th>Speler</th>
                        <th>Tafel</th>
                        <th>Ronde</th>
                        <th>Actie</th>
                        <th class="text-end">Fiches</th>
                        <th class="text-end">Saldo na</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= (int)$value["casino_log_id"] ?></td>
                            <td class="text-nowrap"><?= date('d-m-Y H:i:s', strtotime($value["created_at"])) ?></td>
                            <td>
                                <?php if ($value['player_url'] !== '') : ?>
                                    <a href="<?= $value['player_url'] ?>"><?= htmlspecialchars((string)($value["name"] ?? '')) ?></a>
                                    <br><small class="text-muted"><?= htmlspecialchars((string)($value["email"] ?? '')) ?></small>
                                <?php else : ?>
                                    <span class="text-muted">Tafel</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($value['table_name']) ?><?= $value['game'] !== '' ? '<br><small class="text-muted">' . htmlspecialchars(CasinoTableModel::gameName($value['game'])) . '</small>' : '' ?></td>
                            <td><?= (int)$value["round_no"] ?: '' ?></td>
                            <td><?= htmlspecialchars(CasinoLogModel::actionLabel($value["action"])) ?></td>
                            <td class="text-end <?= $value['amount'] > 0 ? 'text-success' : ($value['amount'] < 0 ? 'text-danger' : '') ?>"><?= $value['amount'] != 0 ? sprintf('%+d', $value['amount']) : '' ?></td>
                            <td class="text-end"><?= (int)$value["casino_user_id"] > 0 ? number_format((int)$value["balance_after"], 0, ',', '.') : '' ?></td>
                            <td class="small"><?= htmlspecialchars($value["detail"]) ?></td>
                            <td class="small"><?= htmlspecialchars($value["ip"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <?= GlobalModel::showPage($total) ?>
    </div>
</div>
