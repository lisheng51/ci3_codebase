<?= script_tag(module_asset_url("js/password_check.js")) ?>
<div class="card o-hidden border-0 shadow-lg my-5">
    <div class="card-body p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4"><?= $h4 ?></h1>
                    </div>
                    <div class="mb-3">
                        <label>Wachtwoord eisen:</label>
                        <div id="lower" class="alert alert-danger">Ten minste 5 kleinletters!</div>
                        <div id="upper" class="alert alert-danger">Ten minste 1 hoofdletter!</div>
                        <div id="number" class="alert alert-danger">Ten minste 1 nummer!</div>
                        <div id="length" class="alert alert-danger">Tussen 8 en 32 karakters!</div>
                        <div id="special" class="alert alert-danger">Ten minste 1 speciale teken (@, $, *, &, +, -, #)!</div>
                    </div>
                    <form method="POST" id="send" class="user">
                        <div class="mb-3">
                            <div class="input-group mb-3">
                                <input type="password" name="password1" maxlength="32" autofocus id="password1" required placeholder="Uw wachtwoord" class="form-control form-control-user">
                                <button id="show_password_string" class="btn btn-primary" type="button">Bekijken</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password2" maxlength="32" required placeholder="Herhaal uw wachtwoord" class="form-control form-control-user">
                        </div>
                        <?= add_csrf_value(); ?>
                        <button type="submit" class="btn btn-primary btn-user w-100" id="submit_button"><?= lang('submit_button_text') ?></button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a href="<?= login_url() ?>" class="small"><?= lang('access_login_title') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="text-center">
            <span>Copyright <?= date('Y') ?> <?= ENVIRONMENT === 'production' ? '' : '(Omgeving: ' . ENVIRONMENT . ')' . span_tooltip('Geen mailverkeer') ?></span>
        </div>
    </div>
</div>