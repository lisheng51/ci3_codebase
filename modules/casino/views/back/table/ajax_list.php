<div class="card">
    <h5 class="card-header">Aantal gevonden: <span class="totalcount"><?= $total ?></span></h5>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Spel</th>
                        <th>Inzet</th>
                        <th>Stoelen</th>
                        <th>Ronde</th>
                        <th>Online</th>
                        <th>Actief</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= (int)$value["casino_table_id"] ?></td>
                            <td><?= $value["name"] ?></td>
                            <td><?= htmlspecialchars(CasinoTableModel::gameName($value["game"])) ?><?= CasinoTableModel::isSolo($value) ? ' <span class="badge bg-warning text-dark">Solo</span>' : '' ?></td>
                            <td><?= (int)$value["min_bet"] ?> - <?= (int)$value["max_bet"] ?></td>
                            <td><?= in_array($value["game"], CasinoTableModel::SEATED_GAMES, true) ? (int)$value["max_seats"] : '-' ?></td>
                            <td><?= (int)$value["round_no"] ?> <small class="text-muted"><?= htmlspecialchars($value["phase"]) ?></small></td>
                            <td><?= (int)$value["online"] ?></td>
                            <td><?= $value["is_active"] ?></td>
                            <td class="text-nowrap">
                                <?= $value["editButton"] ?>
                                <a class="btn btn-sm btn-secondary" href="<?= $value["log_url"] ?>" title="Log"><i class="fa-fw fas fa-list"></i></a>
                                <a class="btn btn-sm btn-success" target="_blank" href="<?= $value["play_url"] ?>" title="Spelen"><i class="fa-fw fas fa-dice"></i></a>
                            </td>
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

<script>
    ajax_inline_edit(module_url + 'back/table/editInline', false);
    ajax_inline_boolean(module_url + 'back/table/editInline', false);
</script>
