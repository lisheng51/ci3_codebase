<div class="text-end p-2"><?= CasinoLang::switcherHtml() ?></div>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
    <div class="card cs-card shadow text-center" style="max-width: 380px; width: 100%;">
        <div class="card-body p-4">
            <div class="cs-logo mb-2"><i class="fas fa-dice"></i> <i class="fas fa-crown"></i></div>
            <h4 class="mb-1"><?= htmlspecialchars(CasinoLang::t('title')) ?></h4>
            <p class="small mb-4 cs-muted"><?= htmlspecialchars(CasinoLang::t('login_intro')) ?></p>
            <?php if (empty($clientId) && empty($cardLogin)) : ?>
                <div class="alert alert-warning small mb-0"><?= htmlspecialchars(CasinoLang::t('login_not_configured')) ?></div>
            <?php elseif (!empty($clientId)) : ?>
                <div id="g_id_onload" data-client_id="<?= htmlspecialchars($clientId) ?>" data-callback="onGoogleCredential" data-auto_prompt="false"></div>
                <div class="g_id_signin d-flex justify-content-center" data-type="standard" data-size="large" data-theme="filled_black" data-text="signin_with" data-shape="pill" data-logo_alignment="left" data-locale="<?= CasinoLang::htmlLang() ?>"></div>
            <?php endif; ?>
            <?php if (!empty($cardLogin)) : ?>
                <?php if (!empty($clientId)) : ?>
                    <div class="d-flex align-items-center my-3 cs-muted small"><hr class="flex-grow-1 my-0"><span class="px-2"><?= htmlspecialchars(CasinoLang::t('card_or')) ?></span><hr class="flex-grow-1 my-0"></div>
                <?php endif; ?>
                <button type="button" id="card_scan" class="btn btn-light w-100 rounded-pill"><i class="fas fa-qrcode"></i> <?= htmlspecialchars(CasinoLang::t('card_login')) ?></button>
                <form id="card_form" class="input-group input-group-sm mt-2" autocomplete="off">
                    <input type="text" id="card_number" class="form-control" inputmode="numeric" maxlength="40" placeholder="<?= htmlspecialchars(CasinoLang::t('card_manual')) ?>" aria-label="<?= htmlspecialchars(CasinoLang::t('card_number')) ?>">
                    <button type="submit" class="btn btn-outline-light"><?= htmlspecialchars(CasinoLang::t('card_go')) ?></button>
                </form>
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

<?php if (!empty($cardLogin)) : ?>
    <div class="modal fade" id="card_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="fas fa-qrcode"></i> <?= htmlspecialchars(CasinoLang::t('card_login')) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars(CasinoLang::t('card_close')) ?>"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="small mb-2"><?= htmlspecialchars(CasinoLang::t('card_scan_hint')) ?></p>
                    <div id="card_reader" class="w-100 rounded overflow-hidden"></div>
                    <div id="card_scan_error" class="text-warning small mt-2"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const LIB = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
            const modalEl = document.getElementById('card_modal');
            const modal = new bootstrap.Modal(modalEl);
            let scanner = null;
            let busy = false;

            function cardLogin(number) {
                number = String(number || '').trim();
                if (!number || busy) return;
                busy = true;
                let formData = new FormData();
                formData.set('card', number);
                formData.set(csrf_token_name, csrf_hash);
                myfetch({
                    method: 'POST',
                    body: formData
                }, '<?= $cardUrl ?>').then(function(json) {
                    if (json && json.status === 'good') {
                        window.location.href = json.redirect;
                        return;
                    }
                    busy = false;
                    modal.hide();
                    $('#login_error').text(json && json.msg ? json.msg : '');
                }).catch(function() {
                    busy = false;
                });
            }

            function loadLib() {
                if (window.Html5Qrcode) return Promise.resolve();
                return new Promise(function(resolve, reject) {
                    const s = document.createElement('script');
                    s.src = LIB;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            function stopScanner() {
                if (scanner && scanner.isScanning) {
                    scanner.stop().catch(function() {});
                }
            }

            function noCamera() {
                $('#card_scan_error').text(<?= json_encode(CasinoLang::t('card_no_camera')) ?>);
                setTimeout(function() {
                    modal.hide();
                    $('#card_number').trigger('focus');
                }, 1500);
            }

            $('#card_scan').on('click', function() {
                $('#login_error').text('');
                $('#card_scan_error').text('');
                modal.show();
            });

            modalEl.addEventListener('shown.bs.modal', function() {
                loadLib().then(function() {
                    scanner = scanner || new Html5Qrcode('card_reader');
                    // barcode on the card (1D) and QR code in the app: a wide box fits both
                    return scanner.start({
                        facingMode: 'environment'
                    }, {
                        fps: 10,
                        qrbox: function(w, h) {
                            return {
                                width: Math.floor(w * 0.85),
                                height: Math.floor(Math.min(h, w) * 0.5)
                            };
                        }
                    }, function(text) {
                        stopScanner();
                        cardLogin(text);
                    });
                }).catch(noCamera);
            });
            modalEl.addEventListener('hidden.bs.modal', stopScanner);

            // a hand scanner types the number into the field and presses Enter
            $('#card_form').on('submit', function(e) {
                e.preventDefault();
                cardLogin($('#card_number').val());
            });
        })();
    </script>
<?php endif; ?>
