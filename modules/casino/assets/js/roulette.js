(function () {
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    let shownSpin = null;    // round whose result is on screen (or spinning)
    let bettingRound = null; // only a spin we watched live gets the wheel
    let announced = null;

    const num = n => `<span class="wheel-num ${RouletteWheel.colorOf(n)}">${n}</span>`;

    function winningSpots(n) {
        const won = ['n_' + n];
        if (n === 0) return won;
        won.push(RouletteWheel.colorOf(n), n % 2 ? 'odd' : 'even', n <= 18 ? 'low' : 'high');
        won.push('dozen_' + Math.ceil(n / 12), 'column_' + (((n - 1) % 3) + 1));
        return won;
    }

    function render(s) {
        $('.js-round').text(t('round_n', { n: s.round }));

        $('.rl-cell').each(function () {
            const spot = String($(this).data('spot'));
            const amount = s.myBets[spot] || 0;
            $(this).toggleClass('has-bet', amount > 0);
            // a number pays 35:1, dozens and columns 2:1, the rest 1:1
            const odds = spot.startsWith('n_') ? 35 : (/^(dozen|column)_/.test(spot) ? 2 : 1);
            Casino.betChip(this, amount, amount * odds);
            Casino.winPill(this, amount * odds);
        });

        const betting = s.phase === 'betting';
        const hasBets = Object.keys(s.myBets).length > 0;
        // solo: after a result the next bet starts a new round right away; the player starts the roll
        const canBet = betting || (s.solo && s.phase === 'result' && !RouletteWheel.isRunning());
        $('.rl-cell').prop('disabled', !canBet);
        $('.js-clear').prop('disabled', !betting || !hasBets);
        $('.js-go').toggleClass('d-none', !s.solo).prop('disabled', !betting || !hasBets || RouletteWheel.isRunning());
        $('.js-timer').toggleClass('d-none', !!s.solo);

        if (betting) {
            bettingRound = s.round;
            if (shownSpin !== null && !RouletteWheel.isRunning()) {
                shownSpin = null;
                $('.rl-cell').removeClass('won');
            }
            if (shownSpin === null) {
                const last = s.history[0];
                $('.js-last').html(last ? num(last.number) : num(0)).toggleClass('dim', true);
                $('.js-status').text(s.solo ? t(hasBets ? 'solo_ready' : 'solo_hint') : (s.remaining === null ? t('place_bet_start') : t('place_bet')));
            }
        } else if (s.phase === 'result' && s.round !== shownSpin) {
            shownSpin = s.round;
            const live = bettingRound === s.round; // we saw this round being played (not opened during its result)
            if (live && Casino.animOn() && !document.hidden) {
                $('.js-status').text(t('no_more_bets') + ' · ' + t('spinning'));
                RouletteWheel.spin(s.number, {
                    title: t('wheel_title'),
                    tapSkip: t('tap_skip'),
                    tapContinue: t('tap_continue'),
                    label: describe(s.number),
                    liveLabel: t('no_more_bets'),
                    history: s.history.filter(h => h.round !== s.round).map(h => h.number),
                }).then(() => reveal(s, true));
            } else {
                reveal(s, live); // animations off, tab in the background, or opened during the result
            }
        }

        $('.js-players').html(s.players.length ? s.players.map(p => {
            let res = '';
            if (p.ret !== null && p.ret !== undefined && !RouletteWheel.isRunning()) {
                const diff = p.ret - p.bet;
                res = `<span class="${diff > 0 ? 'text-success' : diff < 0 ? 'text-danger' : ''}">${diff > 0 ? '+' : ''}${fmt(diff)}</span>`;
            }
            return `<li class="d-flex justify-content-between ${p.me ? 'fw-bold' : ''}"><span><i class="fas fa-user-circle"></i> ${esc(p.name)}${p.me ? ' (' + esc(t('you')) + ')' : ''}</span><span>${p.bet ? '<i class="fas fa-coins"></i> ' + fmt(p.bet) : ''} ${res}</span></li>`;
        }).join('') : `<li class="cs-muted">${esc(t('nobody'))}</li>`);

        records(s);

        // recent numbers (a spin that is still turning is not given away)
        const history = RouletteWheel.isRunning() ? s.history.filter(h => h.round !== shownSpin) : s.history;
        $('.js-history').html(history.map(h => num(h.number)).join('') || `<span class="cs-muted small">${esc(t('no_rolls'))}</span>`);
    }

    function describe(n) {
        if (n === 0) return t('green');
        return t(RouletteWheel.colorOf(n)) + ' · ' + t(n % 2 ? 'odd' : 'even') + ' · ' + (n <= 18 ? '1-18' : '19-36');
    }

    // my last rounds (a spin that is still turning is not shown yet)
    function records(s) {
        Casino.renderRecords(s, { hide: RouletteWheel.isRunning() ? shownSpin : null });
    }

    function reveal(s, animate) {
        records(s);
        $('.js-balance').text(fmt(Casino.balance()));
        $('.js-last').html(num(s.number)).removeClass('dim').toggleClass('pop', animate);
        let text = describe(s.number);
        if (s.myResult) {
            const diff = s.myResult.ret - s.myResult.bet;
            text += ' · ' + (diff > 0 ? t('you_win', { amount: fmt(diff) }) : diff < 0 ? t('you_lose', { amount: fmt(-diff) }) : t('push'));
            if (s.myResult.ret > 0 && animate && announced !== s.round) {
                announced = s.round;
                Casino.winShow('.rl-felt', s.myResult.ret);
            }
        }
        $('.js-status').text(text);
        const won = winningSpots(s.number);
        $('.rl-cell').each(function () {
            $(this).toggleClass('won', won.includes($(this).data('spot')));
        });
    }

    $(document).on('click', '.rl-cell', function () {
        Casino.act('bet', { spot: $(this).data('spot'), amount: Casino.chip() });
    });
    $(document).on('click', '.js-clear', function () {
        Casino.act('clear');
    });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    Casino.holdBalance(() => RouletteWheel.isRunning());
    // solo: the player starts the roll themselves (also with the space bar)
    $(document).on('click', '.js-go', function () {
        Casino.act('go');
    });
    $(document).on('keydown', e => {
        if (e.code === 'Space' && !$(e.target).is('input, textarea, select, button, a') && $('.js-go').is(':visible:enabled')) {
            e.preventDefault();
            Casino.act('go');
        }
    });

    // wide screens: the table fills the screen
    Casino.fitBoard('.cs-roulette .rl-felt', '.cs-roulette .rl-dock');

    Casino.start(render);
})();
