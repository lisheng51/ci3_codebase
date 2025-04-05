<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Algemeen</a></li>
    <li class="nav-item"><a class="nav-link" href="#logindata" data-toggle="tab">Account</a></li>
</ul>
<form id="send" method="POST">
    <div class="tab-content">
        <div id="logindata" class="tab-pane">
            <div class="card">
                <div class="card-header">Account</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Emailadres</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" disabled value="<?= $rsdb["emailaddress"] ?>">
                                    <span class="input-group-append">
                                        <a href="<?= $edit_email_url ?>" class="btn btn-dark">Wijzigen</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Wachtwoord oud*</label>
                                <input type="password" name="old_password" class="form-control" placeholder="*********">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Wachtwoord nieuw*</label>
                                <input type="password" name="password" class="form-control" placeholder="*********">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Two-factor (2FA) authentication</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="<?= LoginModel::show2FAQrcode($rsdb["2fa_secret"] ?? "") ?>" />
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= $select_2fa_status; ?>
                            </div>
                            <div class="form-group">
                                <input type="text" name="webapp_one_code" maxlength="6" class="form-control" value="" placeholder="" />
                            </div>

                        </div>
                        <div class="col-md-6">
                            <p>Authenticator op telefoon installeren.</p>
                            <p>Open de Authenticator, Controleer of de optie Scan QR-codes aan staat.</p>
                            <p>Zorg ervoor dat de app op de stand Foto of Vierkant staat.</p>
                            <p>Richt de camera op de QR-code.</p>
                            <p>Bovenin verschijnt vanzelf een melding in beeld.</p>
                            <p>Vul de 2FA code in om activeren.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="profile" class="tab-pane active">
            <div class="card">
                <div class="card-header">Algemeen</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Naam*</label>
                                <input type="text" required maxlength="50" name="display_info" class="form-control" value="<?= $rsdb["display_info"] ?>" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Telefoonnummer</label>
                                <input type="text" maxlength="10" name="phone" pattern="[0-9]{10}" class="form-control" value="<?= $rsdb["phone"] ?>" placeholder="Telefoonnummer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-2">
            <div class="form-group">
                <?= add_csrf_value(); ?>
                <input type="hidden" name="nav_bookmark" value="<?= $rsdb["nav_bookmark"] ?? 0 ?>" />
                <input type="hidden" name="redirect_url" value="<?= $rsdb["redirect_url"] ?? "back/home" ?>" />
                <?= add_submit_button($rsdb) ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
</script>