<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" href="#default" data-toggle="tab">Algemeen</a></li>
    <li class="nav-item"><a class="nav-link" href="#email" data-toggle="tab">Email</a></li>
    <li class="nav-item"><a class="nav-link" href="#notify" data-toggle="tab">Notificatie</a></li>
</ul>

<form method="POST" id="send">
    <div class="tab-content">
        <div id="default" class="tab-pane active">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_name', 'Applicatie naam') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_title', 'Applicatie titel') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_keywords', 'Applicatie keywoord') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_description', 'Applicatie beschrijving') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_ck_pass_unuse_day', 'Wachtwoord verloop aantal dagen', 'number') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_ck_pass_notify_day', 'Waarschuwing wachtwoord verloop aantal dagen', 'number') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_ck_pass_reset_hour', 'Wachtwoord herstellink verloop aantal uur', 'number') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_fail_limit_num', 'Na aantal poging login blokkeren', 'number') ?>
                        </div>

                        <div class="col-2">
                            <?= ConfigModel::input('webapp_default_url', 'Standaard pagina') ?>
                        </div>

                        <div class="col-2">
                            <?= ConfigModel::input('webapp_default_show_per_page', 'Aantal items per pagina', 'number') ?>
                        </div>

                        <div class="col-2">
                            <label>Donkere modus</label>
                            <?= select_boolean('webapp_option_dark_mode', intval(ConfigModel::$configNow["webapp_option_dark_mode"] ?? 0)) ?>
                        </div>

                        <div class="col-6">
                            <label>Super user groep</label>
                            <div class="form-group">
                                <?= PermissionGroupModel::selectMultiple(explode(',', ConfigModel::$configNow["_core_default_admin_group_ids"] ?? ""), "_core_default_admin_group_ids"); ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <?= ConfigModel::textarea('webapp_domain_redirect', 'Domein redirect (www.company.com@access,sub.company.com@web)') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-2">
                            <label>Site url</label>
                            <div class="form-group">
                                <input type="text" class="form-control" disabled value="<?= ENVIRONMENT_BASE_URL ?>">
                            </div>
                        </div>
                        <div class="col-2">
                            <label>Database server</label>
                            <div class="form-group">
                                <input type="text" class="form-control" disabled value="<?= ENVIRONMENT_HOSTNAME ?>">
                            </div>
                        </div>
                        <div class="col-2">
                            <label>Database gebruiker</label>
                            <div class="form-group">
                                <input type="text" class="form-control" disabled value="<?= ENVIRONMENT_USERNAME ?>">
                            </div>
                        </div>
                        <div class="col-2">
                            <label>Database naam</label>
                            <div class="form-group">
                                <input type="text" class="form-control" disabled value="<?= ENVIRONMENT_DATABASE ?>">
                            </div>
                        </div>

                        <div class="col-2">
                            <label>Asset versie</label>
                            <div class="form-group">
                                <input type="text" class="form-control" disabled value="<?= ENVIRONMENT_ASSET_VERSION ?>">
                            </div>
                        </div>

                        <div class="col-2">
                            <label>Versienummer</label>
                            <div class="form-group">
                                <input type="text" name='_core_app_buildnr' class="form-control" readonly value="<?= c_key('_core_app_buildnr') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="email" class="tab-pane">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_noreply_email_address', 'Noreply email') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_noreply_email_name', 'Noreply email naam') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_master_email_address', 'Web master email') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_host', 'SMTP server') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_port', 'SMTP poort') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_crypto', 'SMTP crypto') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_user_name', 'SMTP afzender naam') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_user', 'SMTP gebruikersnaam') ?>
                        </div>

                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_client_id', 'Client ID') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_client_secret', 'Client Secret') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_tenant_id', 'Tenant ID') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_smtp_user_refresh_token', 'Refresh token') ?>
                        </div>

                        <div class="col-2">
                            <label>SMTP wachtwoord</label>
                            <div class="form-group">
                                <input type="password" class="form-control" name="webapp_smtp_pass" value="<?= $webapp_smtp_pass ?>">
                            </div>
                        </div>
                        <div class="col-2">
                            <label>Met webversie link</label>
                            <?= select_boolean('webapp_sendmail_with_webversionurl', intval(ConfigModel::$configNow["webapp_sendmail_with_webversionurl"] ?? 0)) ?>
                        </div>
                        <div class="col-2">
                            <label>Met check open image</label>
                            <?= select_boolean('webapp_sendmail_with_check_open_img', intval(ConfigModel::$configNow["webapp_sendmail_with_check_open_img"] ?? 0)) ?>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Test instelling</label>
                                <div class="input-group">
                                    <input type="text" id="to_email" placeholder="..@.." class="form-control">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="send_mail_test">Verzenden</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="notify" class="tab-pane">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_messagebird_originator', 'Messagebird nummer') ?>
                        </div>
                        <div class="col-3">
                            <?= ConfigModel::input('webapp_messagebird_api_key', 'Messagebird key') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group">
                <?= add_csrf_value(); ?>
                <?= add_submit_button('config') ?>
            </div>
        </div>
    </div>
</form>

<script>
    $("form#send").submit(function(e) {
        ajax_form_search($(this));
        e.preventDefault();
    });

    $("button#send_mail_test").click(function() {
        let host = $('input[name=webapp_smtp_host]').val();
        let user = $('input[name=webapp_smtp_user]').val();
        let name = $('input[name=webapp_smtp_user_name]').val();
        let port = $('input[name=webapp_smtp_port]').val();
        let pass = $('input[name=webapp_smtp_pass]').val();
        let crypto = $('input[name=webapp_smtp_crypto]').val();
        let to_email = $('input#to_email').val();
        if (to_email.length === 0) {
            $('input#to_email').focus();
            return;
        }

        let client_id = $('input[name=webapp_smtp_client_id]').val();
        let client_secret = $('input[name=webapp_smtp_client_secret]').val();
        let tenant_id = $('input[name=webapp_smtp_tenant_id]').val();
        let refresh_token = $('input[name=webapp_smtp_user_refresh_token]').val();

        let ajaxurl = site_url + "back/Config/sendMailTest";
        let senddata = {
            host: host,
            user: user,
            name: name,
            refresh_token: refresh_token,
            port: port,
            pass: pass,
            crypto: crypto,
            to_email: to_email,
            client_id: client_id,
            client_secret: client_secret,
            tenant_id: tenant_id
        };
        axios_search(ajaxurl, senddata, $(this));
    });
</script>