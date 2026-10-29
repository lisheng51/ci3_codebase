<?php
$t = fn(string $key, array $vars = []) => htmlspecialchars(CasinoLang::t($key, $vars));
$icon = fn(string $sym) => '<svg class="sl-icon" viewBox="0 0 100 100" aria-hidden="true"><use href="#sl-' . $sym . '"/></svg>';
$pay = CasinoSlotModel::clientConfig()['paytable'];
?>
<!-- fruit symbols (used by the reels and the paytable) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <radialGradient id="sl-g-red" cx=".35" cy=".3" r=".75"><stop offset="0" stop-color="#ff8a8a"/><stop offset=".45" stop-color="#e0142c"/><stop offset="1" stop-color="#7a0010"/></radialGradient>
        <linearGradient id="sl-g-leaf" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8be26b"/><stop offset="1" stop-color="#1f7a2c"/></linearGradient>
        <radialGradient id="sl-g-yellow" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#fffbd0"/><stop offset=".5" stop-color="#ffe14d"/><stop offset="1" stop-color="#c79a00"/></radialGradient>
        <radialGradient id="sl-g-orange" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#ffe0a6"/><stop offset=".5" stop-color="#ff9a1f"/><stop offset="1" stop-color="#b85400"/></radialGradient>
        <radialGradient id="sl-g-plum" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#e6a6ff"/><stop offset=".5" stop-color="#8e2bbf"/><stop offset="1" stop-color="#3f0b5c"/></radialGradient>
        <linearGradient id="sl-g-gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff2b0"/><stop offset=".35" stop-color="#f5c542"/><stop offset=".7" stop-color="#c48a12"/><stop offset="1" stop-color="#ffe27a"/></linearGradient>
        <linearGradient id="sl-g-bar" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3a3a3a"/><stop offset="1" stop-color="#050505"/></linearGradient>
        <linearGradient id="sl-g-seven" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff5a5a"/><stop offset=".55" stop-color="#d4001c"/><stop offset="1" stop-color="#7d0010"/></linearGradient>
    </defs>
    <symbol id="sl-cherry" viewBox="0 0 100 100">
        <path d="M54 16 C48 34 38 46 32 58" stroke="#2f6b25" stroke-width="5" fill="none" stroke-linecap="round"/>
        <path d="M54 16 C60 34 68 44 71 56" stroke="#2f6b25" stroke-width="5" fill="none" stroke-linecap="round"/>
        <path d="M54 16 C64 4 84 6 90 18 C78 26 62 26 54 16 Z" fill="url(#sl-g-leaf)"/>
        <circle cx="31" cy="70" r="19" fill="url(#sl-g-red)"/>
        <circle cx="70" cy="68" r="19" fill="url(#sl-g-red)"/>
        <ellipse cx="25" cy="62" rx="6" ry="4" fill="#fff" opacity=".75"/>
        <ellipse cx="64" cy="60" rx="6" ry="4" fill="#fff" opacity=".75"/>
    </symbol>
    <symbol id="sl-lemon" viewBox="0 0 100 100">
        <g transform="rotate(-25 50 50)">
            <path d="M6 50 Q10 44 16 42 Q26 20 50 19 Q74 20 84 42 Q90 44 94 50 Q90 56 84 58 Q74 80 50 81 Q26 80 16 58 Q10 56 6 50 Z" fill="url(#sl-g-yellow)" stroke="#b08600" stroke-width="2"/>
            <ellipse cx="38" cy="36" rx="12" ry="6" fill="#fff" opacity=".6"/>
        </g>
    </symbol>
    <symbol id="sl-orange" viewBox="0 0 100 100">
        <circle cx="50" cy="56" r="36" fill="url(#sl-g-orange)"/>
        <g fill="#a34a00" opacity=".25"><circle cx="34" cy="70" r="1.6"/><circle cx="60" cy="78" r="1.6"/><circle cx="72" cy="60" r="1.6"/><circle cx="44" cy="48" r="1.6"/><circle cx="62" cy="44" r="1.6"/></g>
        <path d="M50 22 C58 10 74 10 80 18 C70 26 58 26 50 22 Z" fill="url(#sl-g-leaf)"/>
        <ellipse cx="38" cy="42" rx="9" ry="5" fill="#fff" opacity=".6"/>
    </symbol>
    <symbol id="sl-plum" viewBox="0 0 100 100">
        <path d="M50 20 C76 20 84 44 82 60 C80 80 64 88 50 88 C36 88 20 80 18 60 C16 44 24 20 50 20 Z" fill="url(#sl-g-plum)"/>
        <path d="M50 24 C46 44 46 66 50 86" stroke="#2a0640" stroke-width="2" fill="none" opacity=".45"/>
        <path d="M50 22 C50 14 54 10 58 8" stroke="#4a3010" stroke-width="4" fill="none" stroke-linecap="round"/>
        <ellipse cx="36" cy="40" rx="8" ry="5" fill="#fff" opacity=".55"/>
    </symbol>
    <symbol id="sl-bell" viewBox="0 0 100 100">
        <circle cx="50" cy="13" r="6" fill="url(#sl-g-gold)" stroke="#8a5a00" stroke-width="2"/>
        <path d="M50 17 C30 17 26 36 26 50 C26 62 20 68 13 74 L87 74 C80 68 74 62 74 50 C74 36 70 17 50 17 Z" fill="url(#sl-g-gold)" stroke="#8a5a00" stroke-width="2"/>
        <rect x="11" y="72" width="78" height="9" rx="4.5" fill="url(#sl-g-gold)" stroke="#8a5a00" stroke-width="2"/>
        <circle cx="50" cy="86" r="7" fill="url(#sl-g-gold)" stroke="#8a5a00" stroke-width="2"/>
        <path d="M36 30 C32 38 32 50 33 58" stroke="#fff" stroke-width="4" fill="none" opacity=".6" stroke-linecap="round"/>
    </symbol>
    <symbol id="sl-bar" viewBox="0 0 100 100">
        <rect x="6" y="30" width="88" height="40" rx="9" fill="url(#sl-g-bar)" stroke="url(#sl-g-gold)" stroke-width="5"/>
        <text x="50" y="60" text-anchor="middle" font-family="Arial Black, Arial, sans-serif" font-weight="900" font-size="27" fill="#fff" letter-spacing="2">BAR</text>
    </symbol>
    <symbol id="sl-seven" viewBox="0 0 100 100">
        <text x="50" y="84" text-anchor="middle" font-family="Arial Black, Impact, Arial, sans-serif" font-weight="900" font-size="88" fill="url(#sl-g-seven)" stroke="#ffd34d" stroke-width="5" paint-order="stroke">7</text>
    </symbol>
</svg>

<?= $topbar ?>
<div class="container-lg py-3 cs-slot">
    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
        <a href="#" class="btn btn-sm btn-outline-light js-leave"><i class="fas fa-arrow-left"></i> <?= $t('lobby') ?></a>
        <h5 class="m-0 text-center"><?= htmlspecialchars($table['name']) ?></h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light cs-anim js-anim" aria-pressed="true" title="<?= $t('anim_toggle') ?>"><i class="fas fa-film"></i></button>
            <button type="button" class="btn btn-sm btn-outline-light cs-sound js-sound" aria-pressed="true" title="<?= $t('sound_toggle') ?>"><i class="fas fa-volume-up"></i><i class="fas fa-volume-mute"></i></button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="sl-cabinet">
                <div class="sl-jackpot">
                    <span class="sl-jp-label"><?= $t('sl_jackpot') ?></span>
                    <span class="sl-jp-value js-jackpot">0</span>
                </div>
                <div class="sl-window">
                    <div class="sl-reels js-reels"></div>
                    <svg class="sl-paylines js-paylines" viewBox="0 0 300 300" preserveAspectRatio="none" aria-hidden="true"></svg>
                    <div class="sl-glass"></div>
                </div>
                <div class="sl-display">
                    <span class="js-sl-msg"><?= $t('loading') ?></span>
                    <span class="sl-win js-sl-win"></span>
                </div>
                <div class="sl-controls">
                    <div>
                        <div class="small cs-muted mb-1"><?= $t('sl_line_bet') ?></div>
                        <div class="js-chips d-flex flex-wrap gap-1"></div>
                    </div>
                    <div>
                        <div class="small cs-muted mb-1"><?= $t('sl_lines') ?></div>
                        <div class="sl-lines-pick js-sl-lines">
                            <?php for ($i = 1; $i <= count(CasinoSlotModel::LINES); $i++) : ?>
                                <button type="button" data-lines="<?= $i ?>"><?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="sl-total">
                        <div class="small cs-muted"><?= $t('sl_total_bet') ?></div>
                        <b class="js-sl-total">0</b>
                        <div class="cs-winpill-inline js-sl-maxwin"></div>
                    </div>
                    <button type="button" class="sl-spin js-spin"><?= $t('sl_spin') ?></button>
                </div>
            </div>

            <div class="cs-card p-2 mt-3 cs-records">
                <div class="d-flex justify-content-between align-items-baseline fw-bold mb-1">
                    <span><i class="fas fa-receipt"></i> <?= $t('my_records') ?></span>
                    <span class="small js-records-total"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-dark align-middle mb-0 cs-records-table">
                        <thead>
                            <tr>
                                <th><?= $t('rec_round') ?></th>
                                <th><?= $t('rec_result') ?></th>
                                <th class="text-end"><?= $t('rec_bet') ?></th>
                                <th class="text-end"><?= $t('rec_net') ?></th>
                            </tr>
                        </thead>
                        <tbody class="js-records"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cs-card p-2 mb-3">
                <div class="fw-bold mb-2"><i class="fas fa-list-ol"></i> <?= $t('sl_paytable') ?></div>
                <table class="sl-paytable w-100">
                    <?php foreach (array_reverse(CasinoSlotModel::SYMBOLS) as $sym) : ?>
                        <tr class="<?= $sym === 'seven' ? 'sl-pt-top' : '' ?>">
                            <td><?= str_repeat($icon($sym), 3) ?></td>
                            <td class="text-end">x<?= (float)($pay[$sym] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><?= str_repeat($icon('cherry'), 2) ?> <small class="cs-muted"><?= $t('sl_cherry2') ?></small></td>
                        <td class="text-end">x<?= (float)($pay['cherry2'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td><?= $icon('cherry') ?> <small class="cs-muted"><?= $t('sl_cherry1') ?></small></td>
                        <td class="text-end">x<?= (float)($pay['cherry1'] ?? 0) ?></td>
                    </tr>
                </table>
                <div class="small cs-muted mt-2"><?= $t('sl_rule') ?></div>
            </div>
            <div class="cs-card p-2 mb-3">
                <div class="fw-bold mb-1"><i class="fas fa-trophy"></i> <?= $t('sl_recent') ?></div>
                <ul class="list-unstyled m-0 small js-sl-feed"></ul>
            </div>
            <div class="cs-card p-2">
                <div class="fw-bold mb-1"><i class="fas fa-users"></i> <?= $t('players_at_table') ?></div>
                <ul class="list-unstyled m-0 small js-players"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    window.CASINO_CONFIG = <?= json_encode($config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?= script_tag(module_asset_url('js/casino.js')) ?>
<?= script_tag(module_asset_url('js/casino-sound.js')) ?>
<?= script_tag(module_asset_url('js/slot.js')) ?>
