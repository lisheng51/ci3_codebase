<div class="card o-hidden border-0 shadow-lg my-5">
    <div class="card-body p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4"><?= lang('access_login_title') ?></h1>
                    </div>
                    <form id="send" class="user">
                        <div class="mb-3">
                            <input type="email" pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" name="user[username]" autofocus required placeholder="<?= lang('access_username_label_text') ?>" class="form-control form-control-user">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="user[password]" required placeholder="<?= lang('access_password_label_text') ?>" class="form-control form-control-user">
                        </div>
                        <div class="mb-3 d-none" id="2fa_block">
                            <input type="text" name="_core_one_code" maxlength="6" class="form-control form-control-user" value="" placeholder="<?= lang('access_2facode_label_text') ?>" />
                        </div>
                        <div class="mb-3">
                            <div class="show_access_code_input"></div>
                        </div>
                        <?= add_csrf_value(); ?>
                        <button type="submit" class="btn btn-primary btn-user w-100"><?= lang('access_submit_button_text') ?></button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a href="<?= site_url("access/password_reset") ?>" class="small">Wachtwoord vergeten ?</a>
                    </div>

                    <?php if (AccessCheckModel::ipAllow()) : ?>
                        <div class="text-center">
                            <a href="<?= login_url("/index_dev") ?>" class="small">Webmaster</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div class="text-center">
            <span>Copyright <?= date('Y') ?> <?= ENVIRONMENT !== 'development' ? '' : '(Omgeving: ' . ENVIRONMENT . ')' . span_tooltip('Geen mailverkeer') ?></span>
        </div>
    </div>
</div>