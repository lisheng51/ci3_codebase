<div class="card">
    <div class="card-header"><?= $title ?></div>
    <div class="card-body">
        <form method="POST" id="send">
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP gebruikersnaam*</label>
                        <input type="email" class="form-control" name="user" required value="<?= $rsdb["user"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP afzender naam*</label>
                        <input type="text" class="form-control" name="name" required value="<?= $rsdb["name"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP server*</label>
                        <input type="text" class="form-control" name="host" required value="<?= $rsdb["host"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP wachtwoord</label>
                        <input type="password" class="form-control" name="pass" value="<?= $rsdb["pass"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP poort*</label>
                        <input type="text" class="form-control" name="port" required value="<?= $rsdb["port"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>SMTP crypto</label>
                        <input type="text" class="form-control" name="crypto" value="<?= $rsdb["crypto"] ?? ""; ?>" />
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label>Client ID</label>
                        <input type="text" class="form-control" name="client_id" value="<?= $rsdb["client_id"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Client Secret</label>
                        <input type="text" class="form-control" name="client_secret" value="<?= $rsdb["client_secret"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Tenant ID</label>
                        <input type="text" class="form-control" name="tenant_id" value="<?= $rsdb["tenant_id"] ?? ""; ?>" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Refresh token</label>
                        <input type="text" class="form-control" name="refresh_token" value="<?= $rsdb["refresh_token"] ?? ""; ?>" />
                    </div>
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
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <input type="hidden" name="mail_config_id" value="<?= $rsdb["mail_config_id"] ?? 0; ?>" />
                        <?= add_csrf_value(); ?>
                        <?= add_submit_button($rsdb) ?>
                        <?= add_reset_button() ?>
                        <?= $delButton; ?>
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

    $("button#send_mail_test").click(function() {
        let host = $('input[name=host]').val();
        let user = $('input[name=user]').val();
        let name = $('input[name=name]').val();
        let port = $('input[name=port]').val();
        let pass = $('input[name=pass]').val();
        let crypto = $('input[name=crypto]').val();
        let to_email = $('input#to_email').val();
        if (to_email.length === 0) {
            $('input#to_email').focus();
            return;
        }

        let client_id = $('input[name=client_id]').val();
        let client_secret = $('input[name=client_secret]').val();
        let tenant_id = $('input[name=tenant_id]').val();
        let refresh_token = $('input[name=refresh_token]').val();

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