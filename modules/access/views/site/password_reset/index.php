<div class="card o-hidden border-0 shadow-lg my-5">
    <div class="card-body p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4"><?= $h4 ?></h1>
                    </div>
                    <form method="POST" id="send" class="user">
                        <div class="mb-3">
                            <input type="email" pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" name="user[username]" autofocus required placeholder="<?= lang('access_emailaddress_text') ?>" class="form-control form-control-user">
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