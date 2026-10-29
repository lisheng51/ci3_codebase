<div class="card">
    <h5 class="card-header"><?= $title ?></h5>
    <div class="card-body">
        <form id="send">
            <div class="row">
                <div class="col-12 col-lg-5">
                    <label>Naam*</label>
                    <div class="mb-3">
                        <input name="name" type="text" maxlength="100" required class="form-control" value="<?= htmlspecialchars($rsdb["name"] ?? "") ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <label>Spel</label>
                    <div class="mb-3">
                        <select name="game" class="form-select" id="game">
                            <?php foreach (CasinoTableModel::GAMES as $key => $name) : ?>
                                <option value="<?= $key ?>" <?= ($rsdb['game'] ?? 'dice') === $key ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($rsdb)) : ?>
                            <small class="text-muted">Ander spel kiezen reset de tafel (open inzetten gaan terug).</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <label>Volgorde</label>
                    <div class="mb-3">
                        <input name="order_num" type="number" min="0" class="form-control" value="<?= (int)($rsdb["order_num"] ?? 0) ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-2">
                    <label>Actief</label>
                    <div class="mb-3">
                        <select name="is_active" class="form-select">
                            <option value="1" <?= (int)($rsdb['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Ja</option>
                            <option value="0" <?= (int)($rsdb['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nee</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-3">
                    <label id="min-label">Minimale inzet</label>
                    <div class="mb-3">
                        <input name="min_bet" type="number" min="1" required class="form-control" value="<?= (int)($rsdb["min_bet"] ?? 10) ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <label id="max-label">Maximale inzet</label>
                    <div class="mb-3">
                        <input name="max_bet" type="number" min="1" required class="form-control" value="<?= (int)($rsdb["max_bet"] ?? 1000) ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-3" id="seats-box">
                    <label>Stoelen (max <?= CasinoTableModel::MAX_SEATS ?>, poker minimaal 2)</label>
                    <div class="mb-3">
                        <input name="max_seats" type="number" min="1" max="<?= CasinoTableModel::MAX_SEATS ?>" class="form-control" value="<?= (int)($rsdb["max_seats"] ?? 5) ?>" />
                    </div>
                </div>
                <div class="col-12 col-lg-3" id="solo-box">
                    <label>Solo</label>
                    <div class="mb-3">
                        <select name="solo" class="form-select">
                            <option value="0" <?= empty($rsdb['solo']) ? 'selected' : '' ?>>Nee, samen spelen (rondes met timer)</option>
                            <option value="1" <?= !empty($rsdb['solo']) ? 'selected' : '' ?>>Ja, elke speler start zelf (geen wachttijd)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="<?= CasinoTableModel::$primaryKey ?>" value="<?= $rsdb[CasinoTableModel::$primaryKey] ?? 0; ?>" />
                    <?= add_csrf_value(); ?>
                    <?= add_submit_button($rsdb) ?>
                    <?php if (!empty($resetUrl)) : ?>
                        <button type="button" class="btn btn-warning" id="table-reset"><i class="fas fa-redo"></i> Tafel resetten</button>
                    <?php endif; ?>
                    <?= $delButton ?>
                </div>
            </div>
        </form>
    </div>
    <?= !empty($rsdb) ? GlobalModel::editTimeInfo($rsdb) : '' ?>
</div>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });

    // what min_bet / max_bet mean differs per game
    const betLabels = {
        dice: ['Minimale inzet', 'Maximale inzet per vak'],
        roulette: ['Minimale inzet', 'Maximale inzet per vak'],
        blackjack: ['Minimale inzet', 'Maximale inzet'],
        slot: ['Minimale inzet per lijn', 'Maximale inzet per lijn (geeft recht op de hele jackpot)'],
        holdem: ['Big blind (small blind = de helft)', 'Maximale inleg (buy-in)'],
        bingo: ['Prijs per kaart', 'Maximaal aantal kaarten per speler per ronde (max 12)'],
        doudizhu: ['Basisinzet (x bod x bommen)', 'Maximale inleg (buy-in)'],
        chess: ['Inzet per partij (de winnaar krijgt beide inzetten)', 'Niet gebruikt (gelijk aan de inzet)'],
        xiangqi: ['Inzet per partij (de winnaar krijgt beide inzetten)', 'Niet gebruikt (gelijk aan de inzet)'],
    };
    $("#game").on("change", function() {
        const labels = betLabels[this.value] || betLabels.dice;
        $("#min-label").text(labels[0]);
        $("#max-label").text(labels[1]);
        $("#seats-box").toggle(<?= json_encode(CasinoTableModel::SEATED_GAMES) ?>.includes(this.value));
        $("#solo-box").toggle(<?= json_encode(CasinoTableModel::SOLO_GAMES) ?>.includes(this.value));
    }).trigger("change");

    $("#table-reset").on("click", function() {
        if (!confirm("De lopende ronde stoppen en alle open inzetten teruggeven?")) return;
        let formData = new FormData();
        formData.set(csrf_token_name, csrf_hash);
        myfetch({
            method: 'POST',
            body: formData
        }, '<?= $resetUrl ?>').then(function(json) {
            if (json) handle_info_box(json.status, json.msg);
        });
    });
</script>
