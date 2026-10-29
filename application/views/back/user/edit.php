<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" href="#profile" data-bs-toggle="tab">Algemeen</a></li>
    <li class="nav-item"><a class="nav-link" href="#logindata" data-bs-toggle="tab">Account</a></li>
</ul>
<form id="send" method="POST">
    <div class="tab-content">
        <div id="logindata" class="tab-pane">
            <div class="card">
                <div class="card-header">Account</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Email / Gebruikersnaam*</label>
                                <input type="email" required name="emailaddress" class="form-control" value="<?= $rsdb["emailaddress"] ?? "" ?>" placeholder="Emailadres" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Wachtwoord</label>
                                <div class="input-group">
                                    <input type="password" id="password1" name="password" placeholder="*********" class="form-control" disabled>
                                    <div class="input-group-append">
                                        <button id="ena_change_pass" class="btn btn-dark" type="button">Wijzigen</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>Redirect url</label>
                                <input type="text" name="redirect_url" class="form-control" value="<?= $rsdb["redirect_url"] ?? "back/home" ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Is actief*</label>
                                <?= select_boolean('is_active', intval($rsdb["is_active"] ?? 0)); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>Bookmark als navbar*</label> <button type="button" data-user-id="<?= $rsdb[UserModel::$primaryKey] ?? 0; ?>" class="btn btn-dark btn-sm" id="updateBypermissionGroup"><?= lang('reset_icon') ?></button>
                            <div class="mb-3">
                                <?= select_boolean('nav_bookmark', intval($rsdb["nav_bookmark"] ?? 0)); ?>
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
                            <div class="mb-3">
                                <?= $select_2fa_status; ?>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="_core_one_code" maxlength="6" class="form-control" value="" placeholder="" />
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
                            <div class="mb-3">
                                <label>Naam*</label>
                                <input type="text" required maxlength="50" name="display_info" class="form-control" value="<?= $rsdb["display_info"] ?? "" ?>" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Telefoonnummer</label>
                                <input type="text" maxlength="10" name="phone" pattern="[0-9]{10}" class="form-control" value="<?= $rsdb["phone"] ?? "" ?>" placeholder="Telefoonnummer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Toestemming groep*</label>
                                <?= $select_multiple_permissionGroup ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="mb-3">
                <input type="hidden" name="<?= UserModel::$primaryKey ?>" value="<?= $rsdb[UserModel::$primaryKey] ?? 0; ?>" />
                <?= add_csrf_value(); ?>
                <?= add_submit_button($rsdb) ?>
                <?= add_reset_button() ?>
                <?= $delButton ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });
    $("#ena_change_pass").click(function() {
        if ($("#password1").attr("disabled") === "disabled") {
            $('input#password1').prop("disabled", false);
            $('input#password1').attr("required", "required");
            $('input#password1').focus();
        } else {
            $('input#password1').val('');
            $('input#password1').attr("disabled", true);
        }
    });

    $("#updateBypermissionGroup").click(function() {
        let submit_button = $(this);
        let submit_button_text = submit_button.html();

        submit_button.attr("disabled", "disabled");
        submit_button.html('<i class="fa fa-spinner fa-pulse"></i> ');

        let formData = new FormData();
        formData.set([csrf_token_name], csrf_hash);
        formData.set('user_id', submit_button.data("user-id"));

        let url = site_url + 'back/User/updateBypermissionGroup';
        myfetch({
                method: 'POST',
                body: formData
            }, url).then(json => {
                handle_info_box(json.status, json.msg);
            })
            .finally(() => {
                submit_button.html(submit_button_text);
                submit_button.removeAttr("disabled");
            });
    });
</script>