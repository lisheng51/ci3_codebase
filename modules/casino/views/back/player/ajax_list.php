<div class="card">
    <h5 class="card-header">Aantal gevonden: <span class="totalcount"><?= $total ?></span></h5>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th class="text-end">Fiches</th>
                        <th class="text-end">Totaal ingezet</th>
                        <th class="text-end">Totaal gewonnen</th>
                        <th>Laatst gezien</th>
                        <th>Actief</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemContainer">
                    <?php foreach ($listdb as $value) : ?>
                        <tr>
                            <td><?= (int)$value["casino_user_id"] ?></td>
                            <td>
                                <?php if (!empty($value['picture'])) : ?><img src="<?= htmlspecialchars($value['picture']) ?>" alt="" style="height: 24px; border-radius: 50%;" referrerpolicy="no-referrer"><?php endif; ?>
                                <?= htmlspecialchars($value["name"]) ?>
                            </td>
                            <td><?= htmlspecialchars($value["email"]) ?></td>
                            <td class="text-end fw-bold"><?= number_format((int)$value["balance"], 0, ',', '.') ?></td>
                            <td class="text-end"><?= number_format((int)$value["total_bet"], 0, ',', '.') ?></td>
                            <td class="text-end"><?= number_format((int)$value["total_win"], 0, ',', '.') ?></td>
                            <td><?= $value["last_seen_at"] ? date('d-m-Y H:i', strtotime($value["last_seen_at"])) : '' ?></td>
                            <td><?= $value["is_active"] ?></td>
                            <td class="text-nowrap">
                                <?= $value["editButton"] ?>
                                <a class="btn btn-sm btn-secondary" href="<?= $value["log_url"] ?>" title="Log"><i class="fa-fw fas fa-list"></i></a>
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
    ajax_inline_boolean(module_url + 'back/player/editInline', false);
</script>
