(function () {
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    const die = DiceShaker.dieHTML;
    let shownRoll = null;   // round whose result is on screen (or being shaken)
    let bettingRound = null; // last round seen in the betting phase: only a roll we watched live is shaken
    let totalsBuilt = false;
    let current = null;
    const lastKey = 'casino-dice-last-' + Casino.config.stateUrl;
    let lastBets = null;
    try { lastBets = JSON.parse(localStorage.getItem(lastKey) || 'null'); } catch (e) { }

    function saveLast(bets) {
        lastBets = Object.assign({}, bets);
        try { localStorage.setItem(lastKey, JSON.stringify(lastBets)); } catch (e) { }
    }

    /** Casino board (as on the Holland Casino machines) or the board in sections; wide screens start with the casino board. */
    function setLayout(layout, save) {
        $('.js-layout-pane').each(function () { this.classList.toggle('d-none', this.dataset.layout !== layout); });
        $('.js-layout').each(function () { this.classList.toggle('active', this.dataset.layout === layout); });
        if (save) { try { localStorage.setItem('casino-dice-layout', layout); } catch (e) { } }
        fit();
    }

    // wide screens: the casino board fills the screen (not the board in sections)
    const fit = Casino.fitBoard('.cs-dice .cs-felt', '.cs-dice .sb-dock', () => !$('.sb-casino').hasClass('d-none'));
    let savedLayout = null;
    try { savedLayout = localStorage.getItem('casino-dice-layout'); } catch (e) { }
    setLayout(savedLayout === 'casino' || savedLayout === 'list' ? savedLayout : (window.innerWidth >= 900 ? 'casino' : 'list'), false);

    function buildTotals(s) {
        if (totalsBuilt) return;
        totalsBuilt = true;
        $('.js-totals').html(s.payouts.totals.map(x =>
            `<button class="cs-spot cs-spot-total" data-spot="total_${x.total}"><span class="cs-spot-num">${x.total}</span><small class="cs-gold">${x.odds}:1</small><b class="js-bet"></b></button>`
        ).join(''));
    }

    /** Winnings per chip (x : 1) of a spot; a single number shows its best case (on all three dice). */
    function oddsOf(spot, p) {
        const simple = { big: p.bigsmall, small: p.bigsmall, odd: p.oddeven, even: p.oddeven, triple: p.triple };
        if (spot in simple) return simple[spot];
        const [type, value] = String(spot).split('_');
        if (type === 'double') return p.double;
        if (type === 'triple') return p.specificTriple;
        if (type === 'combo') return p.combo;
        if (type === 'single') return Math.max(1, p.single2 || 2, p.single3 || 3); // best case: on all three dice
        if (type === 'total') return (p.totals.find(x => x.total === Number(value)) || { odds: 0 }).odds;
        return 0;
    }

    function category(dice, total) {
        if (dice[0] === dice[1] && dice[1] === dice[2]) return 'triple';
        return total >= 11 ? 'big' : 'small';
    }

    function render(s) {
        buildTotals(s);
        $('.js-round').text(t('round_n', { n: s.round }));
        $('[data-odds]').each(function () {
            $(this).text(s.payouts[$(this).data('odds')]);
        });

        // my bets on the board, with what each one wins
        $('.cs-spot').each(function () {
            const spot = $(this).data('spot');
            const amount = s.myBets[spot] || 0;
            $(this).toggleClass('has-bet', amount > 0);
            const odds = oddsOf(spot, s.payouts);
            const win = Math.floor(amount * odds), upTo = spot.startsWith('single_');
            Casino.betChip(this, amount, win, upTo);
            Casino.winPill(this, win, upTo);
        });

        const betting = s.phase === 'betting';
        const hasBets = Object.keys(s.myBets).length > 0;
        // solo: after a result the next bet starts a new round right away; the player starts the roll
        const canBet = betting || (s.solo && s.phase === 'result' && !DiceShaker.isRunning());
        $('.cs-spot').prop('disabled', !canBet);
        $('.js-clear').prop('disabled', !betting || !hasBets);
        $('.js-go').toggleClass('d-none', !s.solo).prop('disabled', !betting || !hasBets || DiceShaker.isRunning());
        $('.js-timer').toggleClass('d-none', !!s.solo);

        // undo the last chip, double all bets, or bet the same as the last rolled round
        current = s;
        if (s.phase === 'result' && hasBets) saveLast(s.myBets);
        $('.js-undo').prop('disabled', !s.canUndo);
        $('.js-double').prop('disabled', !betting || !hasBets);
        $('.js-rebet').prop('disabled', !canBet || (betting && hasBets) || !lastBets);

        if (betting) {
            bettingRound = s.round;
            if (shownRoll !== null && !DiceShaker.isRunning()) {
                shownRoll = null;
                $('.cs-spot').removeClass('won');
            }
            if (shownRoll === null) {
                const last = s.history[0];
                $('.js-dice').html((last ? last.dice : [1, 1, 1]).map(d => die(d).replace('class="pdie', 'class="pdie dim')).join(''));
                $('.js-status').text(s.solo ? t(hasBets ? 'solo_ready' : 'solo_hint') : (s.remaining === null ? t('place_bet_start') : t('place_bet')));
            }
        } else if (s.phase === 'result' && s.round !== shownRoll) {
            shownRoll = s.round;
            const live = bettingRound === s.round; // we saw this round being played (not opened during its result)
            if (live && Casino.animOn() && !document.hidden) {
                $('.js-status').text(t('rolling'));
                const cat = category(s.dice, s.total);
                DiceShaker.run(s.dice, {
                    title: t('shaker_title'),
                    tapSkip: t('tap_skip'),
                    tapContinue: t('tap_continue'),
                    label: t(cat),
                    labelClass: cat,
                }).then(() => reveal(s, true));
            } else {
                reveal(s, live); // animations off, tab in the background, or opened during the result
            }
        }

        // players
        $('.js-players').html(s.players.length ? s.players.map(p => {
            let res = '';
            if (p.ret !== null && p.ret !== undefined && !DiceShaker.isRunning()) {
                const diff = p.ret - p.bet;
                res = `<span class="${diff > 0 ? 'text-success' : diff < 0 ? 'text-danger' : ''}">${diff > 0 ? '+' : ''}${fmt(diff)}</span>`;
            }
            return `<li class="d-flex justify-content-between ${p.me ? 'fw-bold' : ''}"><span><i class="fas fa-user-circle"></i> ${esc(p.name)}${p.me ? ' (' + esc(t('you')) + ')' : ''}</span><span>${p.bet ? '<i class="fas fa-coins"></i> ' + fmt(p.bet) : ''} ${res}</span></li>`;
        }).join('') : `<li class="cs-muted">${esc(t('nobody'))}</li>`);

        records(s);

        // history (a roll that is still being shaken is not given away)
        const history = DiceShaker.isRunning() ? s.history.filter(h => h.round !== shownRoll) : s.history;
        $('.js-history').html(history.map(h => {
            const cat = category(h.dice, h.total);
            return `<span class="cs-hist ${cat}" title="${esc(t('round_n', { n: h.round }))}">${h.dice.map(die).join('')} ${h.total} <em>${esc(t(cat))}</em></span>`;
        }).join('') || `<span class="cs-muted">${esc(t('no_rolls'))}</span>`);
    }

    // my last rounds (a roll that is still being shaken is not shown yet)
    function records(s) {
        Casino.renderRecords(s, { hide: DiceShaker.isRunning() ? shownRoll : null });
    }

    function reveal(s, animate) {
        records(s);
        $('.js-balance').text(fmt(Casino.balance()));
        $('.js-dice').html(s.dice.map(d => die(d).replace('class="pdie', animate ? 'class="pdie land' : 'class="pdie')).join(''));
        let text = t('total', { total: s.total });
        if (s.myResult) {
            const diff = s.myResult.ret - s.myResult.bet;
            text += ' · ' + (diff > 0 ? t('you_win', { amount: fmt(diff) }) : diff < 0 ? t('you_lose', { amount: fmt(-diff) }) : t('push'));
            if (s.myResult.ret > 0 && animate) Casino.winShow('.cs-felt', s.myResult.ret);
        }
        $('.js-status').text(text);
        markWinners(s);
    }

    function markWinners(s) {
        const d = s.dice;
        const triple = d[0] === d[1] && d[1] === d[2];
        const won = triple ? ['triple'] : [s.total >= 11 ? 'big' : 'small', s.total % 2 ? 'odd' : 'even'];
        won.push('total_' + s.total);
        const count = n => d.filter(v => v === n).length;
        for (let n = 1; n <= 6; n++) {
            if (count(n) > 0) won.push('single_' + n);
            if (count(n) >= 2) won.push('double_' + n);
            if (count(n) === 3) won.push('triple_' + n);
            for (let m = n + 1; m <= 6; m++) {
                if (count(n) > 0 && count(m) > 0) won.push('combo_' + n + '_' + m);
            }
        }
        $('.cs-spot').each(function () {
            $(this).toggleClass('won', won.includes($(this).data('spot')));
        });
    }

    $(document).on('click', '.cs-spot', function () {
        Casino.act('bet', { spot: $(this).data('spot'), amount: Casino.chip() });
    });
    $(document).on('click', '.js-clear', function () {
        Casino.act('clear');
    });
    $('.js-sound').attr('aria-pressed', String(DiceShaker.soundOn())).on('click', function () {
        const on = !DiceShaker.soundOn();
        DiceShaker.setSound(on);
        $(this).attr('aria-pressed', String(on));
    });

    $(document).on('click', '.js-layout', function () { setLayout(this.dataset.layout, true); });
    $(document).on('click', '.js-undo', () => Casino.act('undo'));
    $(document).on('click', '.js-double', () => {
        Object.entries(current ? current.myBets : {}).forEach(([spot, amount]) => Casino.act('bet', { spot: spot, amount: amount }));
    });
    $(document).on('click', '.js-rebet', () => {
        Object.entries(lastBets || {}).forEach(([spot, amount]) => Casino.act('bet', { spot: spot, amount: amount }));
    });

    Casino.holdBalance(() => DiceShaker.isRunning());
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

    Casino.start(render);
})();
