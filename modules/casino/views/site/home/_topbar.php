<nav class="cs-topbar">
    <a href="<?= site_url($module_path_name . '/home') ?>" class="cs-brand"><i class="fas fa-dice"></i> <?= htmlspecialchars(CasinoLang::t('title')) ?></a>
    <div class="cs-topbar-right">
        <span class="cs-chips" title="<?= htmlspecialchars(CasinoLang::t('chips')) ?>"><i class="fas fa-coins"></i> <span class="js-balance"><?= number_format((int)$user['balance'], 0, ',', '.') ?></span></span>
        <span class="cs-user d-none d-sm-inline">
            <?php if (!empty($user['picture'])) : ?><img src="<?= htmlspecialchars($user['picture']) ?>" alt="" referrerpolicy="no-referrer"><?php endif; ?>
            <?= htmlspecialchars($user['name']) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-light cs-fullscreen js-fullscreen d-none" title="<?= htmlspecialchars(CasinoLang::t('fullscreen')) ?>" aria-pressed="false"><i class="fas fa-expand"></i><i class="fas fa-compress"></i></button>
        <?= CasinoLang::switcherHtml() ?>
        <a href="<?= site_url($module_path_name . '/auth/logout') ?>" class="btn btn-sm btn-outline-light js-logout" title="<?= htmlspecialchars(CasinoLang::t('logout')) ?>"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>
<script>
    /** Asks before something the player might do by accident (sign out, leave a table). Uses SweetAlert2 when it is there. */
    window.casinoConfirm = function(text, hint) {
        const yes = <?= json_encode(CasinoLang::t('js_yes'), JSON_UNESCAPED_UNICODE) ?>;
        const no = <?= json_encode(CasinoLang::t('js_cancel'), JSON_UNESCAPED_UNICODE) ?>;
        if (window.Swal && Swal.fire) {
            return Swal.fire({
                title: text,
                text: hint || '',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: yes,
                cancelButtonText: no,
                reverseButtons: true,
                focusCancel: true,
                background: '#0f3d2e',
                color: '#f1f5f2',
                confirmButtonColor: '#c0392b',
                cancelButtonColor: '#555',
            }).then(r => r.isConfirmed);
        }
        return Promise.resolve(window.confirm(text + (hint ? '\n\n' + hint : '')));
    };

    $(document).on('click', '.js-logout', function(e) {
        e.preventDefault();
        const href = this.href;
        casinoConfirm(<?= json_encode(CasinoLang::t('js_confirm_logout'), JSON_UNESCAPED_UNICODE) ?>).then(ok => {
            if (ok) {
                window.casinoLeaving = true;
                window.location.href = href;
            }
        });
    });
    /*
     * Full screen (tablets): the button shows where the browser can do it. Opening another page leaves full screen,
     * so after a page change the first tap brings it back when the player had it on.
     */
    (function() {
        const root = document.documentElement;
        const request = root.requestFullscreen || root.webkitRequestFullscreen;
        const exit = document.exitFullscreen || document.webkitExitFullscreen;
        const current = () => document.fullscreenElement || document.webkitFullscreenElement;
        const standalone = window.matchMedia('(display-mode: fullscreen), (display-mode: standalone)').matches || navigator.standalone;
        if (!request || standalone) return; // started from the home screen: already full screen
        const remember = on => {
            try { sessionStorage.setItem('casino-fullscreen', on ? '1' : ''); } catch (e) {}
        };
        const enter = () => Promise.resolve(request.call(root, { navigationUI: 'hide' })).catch(() => {});
        const btn = $('.js-fullscreen').removeClass('d-none');
        const sync = () => btn.attr('aria-pressed', String(!!current()));
        document.addEventListener('fullscreenchange', sync);
        document.addEventListener('webkitfullscreenchange', sync);
        btn.on('click', function() {
            const on = !current();
            remember(on);
            on ? enter() : exit.call(document);
        });
        let wanted = false;
        try { wanted = sessionStorage.getItem('casino-fullscreen') === '1'; } catch (e) {}
        if (wanted) {
            document.addEventListener('click', function again(e) {
                document.removeEventListener('click', again, true);
                if (!current() && !e.target.closest('.js-fullscreen')) enter();
            }, true);
        }
    })();

</script>
