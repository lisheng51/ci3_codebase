<div class="card">
    <h5 class="card-header"><?= $title ?>: <?= htmlspecialchars($rsdb['name']) ?></h5>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th>E-mail</th>
                        <td><?= htmlspecialchars($rsdb['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Fiches</th>
                        <td class="fw-bold"><?= number_format((int)$rsdb['balance'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Totaal ingezet / gewonnen</th>
                        <td><?= number_format((int)$rsdb['total_bet'], 0, ',', '.') ?> / <?= number_format((int)$rsdb['total_win'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Aangemaakt</th>
                        <td><?= htmlspecialchars((string)$rsdb['created_at']) ?></td>
                    </tr>
                    <tr>
                        <th>Laatst ingelogd / gezien</th>
                        <td><?= htmlspecialchars((string)$rsdb['last_login_at']) ?> / <?= htmlspecialchars((string)$rsdb['last_seen_at']) ?></td>
                    </tr>
                </table>
                <a href="<?= $logUrl ?>" class="btn btn-sm btn-secondary"><i class="fas fa-list"></i> Log van deze speler</a>
            </div>
        </div>

        <form id="send">
            <div class="row">
                <div class="col-3">
                    <label>Fiches bij (+) of af (-)</label>
                    <div class="mb-3">
                        <input name="delta" type="number" class="form-control" value="0" />
                    </div>
                </div>
                <div class="col-6">
                    <label>Reden (verplicht bij wijziging, komt in de log)</label>
                    <div class="mb-3">
                        <input name="reason" type="text" maxlength="255" class="form-control" />
                    </div>
                </div>
                <div class="col-3">
                    <label>Actief</label>
                    <div class="mb-3">
                        <select name="is_active" class="form-select">
                            <option value="1" <?= (int)$rsdb['is_active'] === 1 ? 'selected' : '' ?>>Ja</option>
                            <option value="0" <?= (int)$rsdb['is_active'] === 0 ? 'selected' : '' ?>>Nee (geblokkeerd)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= add_csrf_value(); ?>
                    <?= add_submit_button($rsdb) ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>
