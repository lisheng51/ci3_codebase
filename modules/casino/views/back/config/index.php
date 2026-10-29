<?php $tab = 0; ?>
<ul class="nav nav-tabs">
    <?php foreach (array_keys(CasinoConfigModel::LABELS) as $i => $group) : ?>
        <li class="nav-item"><a class="nav-link <?= $i === 0 ? 'active' : '' ?>" href="#cfg<?= $i ?>" data-bs-toggle="tab"><?= htmlspecialchars($group) ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="card-body">
        <form id="send">
            <div class="tab-content">
                <?php foreach (CasinoConfigModel::LABELS as $group => $fields) : ?>
                    <div id="cfg<?= $tab ?>" class="tab-pane <?= $tab === 0 ? 'active' : '' ?>">
                        <div class="row">
                            <?php foreach ($fields as $key => $label) : ?>
                                <div class="<?= in_array($key, ['casino_dice_payout_totals', 'casino_google_client_id', 'casino_slot_paytable'], true) ? 'col-12' : 'col-6' ?>">
                                    <div class="mb-3">
                                        <label><?= htmlspecialchars($label) ?></label>
                                        <input type="text" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($values[$key]) ?>" placeholder="<?= htmlspecialchars(CasinoConfigModel::DEFAULTS[$key]) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($group === 'Algemeen') : ?>
                            <div class="small text-muted">
                                Google Cloud Console &gt; Credentials &gt; OAuth client ID (Web application). Voeg bij "Authorized JavaScript origins" toe: <code><?= htmlspecialchars(rtrim(base_url(), '/')) ?></code><br>
                                Inlogpagina voor spelers: <a href="<?= site_url('casino/site/auth/login') ?>" target="_blank"><?= site_url('casino/site/auth/login') ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if ($group === 'Dobbelstenen') : ?>
                            <div class="small text-muted">Uitbetaling x : 1 betekent: bij winst krijgt de speler zijn inzet terug plus x keer de inzet. Een totaal dat niet in de lijst staat kan niet ingezet worden. Getal 1-6 betaalt altijd 1:1 per steen.</div>
                        <?php endif; ?>
                        <?php if ($group === 'Blackjack') : ?>
                            <div class="small text-muted">Een nieuwe schoen wordt geschud als er minder dan een kwart over is. Geen splitsen of verzekering.</div>
                        <?php endif; ?>
                        <?php if ($group === 'Fruitautomaat') : ?>
                            <div class="small text-muted">
                                Met deze uitbetalingstabel keren de lijnen in theorie <b><?= number_format($slotRtp, 2, ',', '.') ?>%</b> van de inzet uit
                                (exact berekend over alle standen van de rollen), plus het deel dat via de jackpot terugkomt.
                                777 op de middelste lijn valt gemiddeld 1 keer per <?= number_format($slotJackpotOdds, 0, ',', '.') ?> spins.
                            </div>
                        <?php endif; ?>
                        <?php if ($group === 'Poker') : ?>
                            <div class="small text-muted">Big blind en maximale inleg stel je per tafel in. Spelers spelen tegen elkaar; het huis verdient alleen aan de rake.</div>
                        <?php endif; ?>
                        <?php if ($group === 'Dou Dizhu') : ?>
                            <div class="small text-muted">Basisinzet (min. inzet) en maximale inleg stel je per tafel in. Spelers spelen tegen elkaar; op een solotafel speelt de speler tegen twee computerspelers van het huis.</div>
                        <?php endif; ?>
                    </div>
                    <?php $tab++; ?>
                <?php endforeach; ?>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="mb-3">
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button('config') ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("form#send").submit(function(e) {
        e.preventDefault();
        ajax_form_search($(this));
    });
</script>
