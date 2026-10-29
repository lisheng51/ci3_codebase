<div class="text-end p-2"><?= CasinoLang::switcherHtml() ?></div>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
    <div class="card cs-card shadow text-center" style="max-width: 380px; width: 100%;">
        <div class="card-body p-4">
            <div class="cs-logo mb-2"><i class="fas fa-dice"></i> <i class="fas fa-crown"></i></div>
            <h4 class="mb-1"><?= htmlspecialchars(CasinoLang::t('title')) ?></h4>
            <p class="small mb-4 cs-muted"><?= htmlspecialchars(CasinoLang::t('login_intro')) ?></p>
            <?php if (empty($clientId)) : ?>
                <div class="alert alert-warning small mb-0"><?= htmlspecialchars(CasinoLang::t('login_not_configured')) ?></div>
            <?php else : ?>
                <div id="g_id_onload" data-client_id="<?= htmlspecialchars($clientId) ?>" data-callback="onGoogleCredential" data-auto_prompt="false"></div>
                <div class="g_id_signin d-flex justify-content-center" data-type="standard" data-size="large" data-theme="filled_black" data-text="signin_with" data-shape="pill" data-logo_alignment="left" data-locale="<?= CasinoLang::htmlLang() ?>"></div>
            <?php endif; ?>
            <?php if (!empty($backOfficeUrl)) : ?>
                <div class="mt-3">
                    <a href="<?= $backOfficeUrl ?>" class="btn btn-warning w-100"><i class="fas fa-user-shield"></i> <?= htmlspecialchars(CasinoLang::t('login_backoffice', ['name' => $backOfficeName])) ?></a>
                </div>
            <?php endif; ?>
            <div id="login_error" class="text-warning small mt-3"><?= !empty($blocked) ? htmlspecialchars(CasinoLang::t('login_blocked')) : '' ?></div>
        </div>
    </div>
</div>

<?php if (!empty($clientId)) : ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function onGoogleCredential(response) {
            let formData = new FormData();
            formData.set('credential', response.credential);
            formData.set(csrf_token_name, csrf_hash);
            myfetch({
                method: 'POST',
                body: formData
            }, '<?= $googleUrl ?>').then(function(json) {
                if (json && json.status === 'good') {
                    window.location.href = json.redirect;
                } else if (json) {
                    $('#login_error').text(json.msg);
                }
            });
        }
    </script>
<?php endif; ?>
