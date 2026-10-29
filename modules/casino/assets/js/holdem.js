(function () {
    const SUITS = { S: '♠', H: '♥', D: '♦', C: '♣' };
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    let state = null;
    let lastRound = null, lastBoard = 0, lastMyTurn = false, announced = null;
    let turnEndsAt = 0, raiseKey = '', raiseTo = 0;

    function card(c, cls) {
        if (c === '??') return `<span class="cs-card-face back ${cls || ''}"></span>`;
        const suit = c.slice(-1), rank = c.slice(0, -1);
        return `<span class="cs-card-face ${suit === 'H' || suit === 'D' ? 'red' : ''} ${cls || ''}"><b>${rank}</b><i>${SUITS[suit]}</i></span>`;
    }

    /** "call:40" -> "Call 40" */
    function lastText(code) {
        const [what, amount] = String(code).split(':');
        const label = { sb: 'SB', bb: 'BB', fold: t('pk_fold'), check: t('pk_check'), call: t('pk_last_call'), raise: t('pk_last_raise'), allin: t('pk_allin') }[what] || what;
        return label + (amount ? ' ' + fmt(amount) : '');
    }

    /** Position (percent of the table) of display place k of n around the oval; place 0 is at the bottom. */
    function position(k, n, rx, ry) {
        const a = Math.PI / 2 + k * 2 * Math.PI / n;
        return { left: 50 + rx * Math.cos(a), top: 50 + ry * Math.sin(a) };
    }

    function render(s) {
        state = s;
        if (s.round !== lastRound) {
            lastRound = s.round;
            lastBoard = 0;
        }
        $('.js-round').text(s.round ? t('round_n', { n: s.round }) : '');

        // seats around the table; the player always sits at the bottom
        const n = s.seats.length;
        const offset = s.mySeat ? s.mySeat - 1 : 0;
        const showdown = s.phase === 'showdown';
        let html = '';
        s.seats.forEach(seat => {
            const k = (seat.no - 1 - offset + n) % n;
            const p = position(k, n, 45, 42);
            const style = `left:${p.left.toFixed(2)}%;top:${p.top.toFixed(2)}%`;
            if (seat.empty) {
                html += `<div class="pk-seat empty" style="${style}">` + (s.can.sit
                    ? `<button type="button" class="btn btn-sm btn-outline-warning js-sit" data-seat="${seat.no}"><i class="fas fa-chair"></i> ${esc(t('pk_sit'))}</button>`
                    : `<span class="pk-empty-no">${seat.no}</span>`) + '</div>';
                return;
            }
            const cls = ['pk-seat', seat.me ? 'me' : '', seat.turn ? 'turn' : '', seat.folded ? 'folded' : '', seat.waiting ? 'waiting' : '', showdown && seat.won > 0 ? 'winner' : ''].join(' ');
            html += `<div class="${cls}" style="${style}">
                <div class="pk-cards">${seat.cards.map(c => card(c, k === 0 && lastBoard === 0 && s.phase === 'preflop' ? 'deal' : '')).join('')}</div>
                <div class="pk-plate">
                    <div class="pk-name">${esc(seat.name)}${seat.me ? ' (' + esc(t('you')) + ')' : ''}</div>
                    <div class="pk-stack">${seat.allin ? '<b class="pk-allin">ALL-IN</b>' : '<i class="fas fa-coins"></i> ' + fmt(seat.stack)}</div>
                    ${seat.turn ? '<div class="pk-timer"><i></i></div>' : ''}
                </div>
                ${seat.last && !showdown && !seat.folded ? `<div class="pk-tag">${esc(lastText(seat.last))}</div>` : ''}
                ${seat.folded && !showdown ? `<div class="pk-tag">${esc(t('pk_fold'))}</div>` : ''}
                ${seat.hand ? `<div class="pk-tag pk-hand">${esc(t('hand_' + seat.hand))}</div>` : ''}
                ${showdown && seat.won > 0 ? `<div class="pk-tag pk-won">+${fmt(seat.won)}</div>` : ''}
                ${seat.waiting ? `<div class="pk-tag">${esc(t('pk_sitting_out'))}</div>` : ''}
                ${seat.leaving ? `<div class="pk-tag">${esc(t('pk_leaving'))}</div>` : ''}
            </div>`;
            if (seat.bet > 0) {
                const b = position(k, n, 29, 25);
                html += `<div class="pk-bet" style="left:${b.left.toFixed(2)}%;top:${b.top.toFixed(2)}%"><i></i>${fmt(seat.bet)}</div>`;
            }
            if (seat.dealer) {
                const d = position(k + 0.3, n, 34, 30);
                html += `<div class="pk-dealer" style="left:${d.left.toFixed(2)}%;top:${d.top.toFixed(2)}%">D</div>`;
            }
        });
        $('.js-seats').html(html);

        // community cards and pot
        $('.js-board').html([0, 1, 2, 3, 4].map(i => s.board[i] ? card(s.board[i], i >= lastBoard ? 'deal' : '') : '<span class="pk-slot"></span>').join(''));
        if (s.board.length > lastBoard) CasinoSound.knock(0.35, 1300);
        lastBoard = s.board.length;
        $('.js-pot').text(s.pot ? t('pk_pot_total', { amount: fmt(s.pot) }) : '');

        // result of the hand
        const me = s.seats.find(x => !x.empty && x.me);
        if (showdown) {
            $('.js-pk-result').html(s.pots.map(p => esc(t('pk_wins', { name: p.winners.join(', '), amount: fmt(p.amount) })) +
                (p.hand ? ' ' + esc(t('pk_with', { hand: t('hand_' + p.hand) })) : '')).join('<br>'));
            if (announced !== s.round) {
                announced = s.round;
                if (me && me.won > 0) {
                    CasinoSound.chime([659, 784, 1047], 0.09);
                    Casino.toast(t('pk_wins', { name: me.name, amount: fmt(me.won) }), 'good');
                }
            }
        } else {
            $('.js-pk-result').html('');
        }

        // what the player sees under the table
        const info = [];
        if (!s.mySeat) {
            info.push(t('pk_sit_hint'));
        } else if (s.phase === 'waiting') {
            info.push(s.seats.filter(x => !x.empty).length < 2 ? t('pk_waiting') : t('pk_next_hand'));
        } else if (s.can.fold) {
            info.push(t('your_turn'));
        }
        if (s.myHand) info.push(t('pk_your_hand', { hand: t('hand_' + s.myHand) }));
        info.push(t('pk_blinds', { sb: fmt(s.blinds[0]), bb: fmt(s.blinds[1]) }));
        $('.js-pk-info').text(info.join(' · '));

        renderActions(s);

        // my turn: a sound, and the timer bar of whoever is to act
        if (s.can.fold && !lastMyTurn) CasinoSound.chime([880, 1175], 0.12, 0.14);
        lastMyTurn = s.can.fold;
        turnEndsAt = s.remaining !== null ? Date.now() + s.remaining * 1000 : 0;
        tickTimer();

        Casino.renderRecords(s);
    }

    function renderActions(s) {
        const c = s.can;
        $('.js-pk-actions').toggleClass('d-none', !c.fold);
        if (!c.fold) return;
        $('.js-pk-act[data-action=fold]').text(t('pk_fold'));
        $('.js-pk-act[data-action=check]').text(t('pk_check')).toggleClass('d-none', !c.check);
        $('.js-pk-act[data-action=call]').text(t('pk_call', { amount: fmt(s.toCall) })).toggleClass('d-none', !c.call);
        $('.js-pk-act[data-action=allin]').text(t('pk_allin') + ' ' + fmt(s.maxRaiseTo)).toggleClass('d-none', !c.allin);
        $('.js-pk-raise').toggleClass('d-none', !c.raise);
        if (!c.raise) return;
        // a new decision: start the slider at the minimum raise
        const key = s.round + '/' + s.phase + '/' + s.currentBet;
        if (key !== raiseKey) {
            raiseKey = key;
            raiseTo = s.minRaiseTo;
        }
        $('.js-raise-range').attr({ min: s.minRaiseTo, max: s.maxRaiseTo });
        $('.js-raise-amount').attr({ min: s.minRaiseTo, max: s.maxRaiseTo });
        $('.js-raise-preset[data-preset=min]').text(t('pk_min'));
        $('.js-raise-preset[data-preset=half]').text(t('pk_half_pot'));
        $('.js-raise-preset[data-preset=pot]').text(t('pk_pot'));
        setRaise(raiseTo);
    }

    function setRaise(value) {
        if (!state) return;
        raiseTo = Math.max(state.minRaiseTo, Math.min(state.maxRaiseTo, Math.round(value) || 0));
        $('.js-raise-range').val(raiseTo);
        $('.js-raise-amount').val(raiseTo);
        $('.js-pk-act[data-action=raise]').text(t(state.currentBet > 0 ? 'pk_raise' : 'pk_bet', { amount: fmt(raiseTo) }));
    }

    function tickTimer() {
        const bar = $('.pk-seat.turn .pk-timer i');
        if (!bar.length || !state || !turnEndsAt) return;
        const left = Math.max(0, turnEndsAt - Date.now()) / 1000;
        bar.css('width', Math.min(100, left / state.turnSeconds * 100) + '%').toggleClass('low', left <= 5);
    }

    /** Asks how many chips the player takes to the table, then sits down. */
    function buyIn(seat) {
        const [min, max] = state.buyin;
        const def = Math.min(max, Math.max(min, state.blinds[1] * 100));
        const title = t('pk_buyin_title');
        const hint = t('pk_buyin_hint', { min: fmt(min), max: fmt(max) });
        const ask = window.Swal && Swal.fire
            ? Swal.fire({
                title: title,
                text: hint,
                input: 'range',
                inputAttributes: { min: min, max: max, step: 1 },
                inputValue: def,
                showCancelButton: true,
                confirmButtonText: t('pk_sit'),
                cancelButtonText: t('cancel'),
                background: '#0f3d2e',
                color: '#f1f5f2',
                confirmButtonColor: '#c99a2e',
                cancelButtonColor: '#555',
            }).then(r => r.isConfirmed ? Number(r.value) : 0)
            : Promise.resolve(Number(window.prompt(title + '\n' + hint, def) || 0));
        ask.then(amount => { if (amount > 0) Casino.act('sit', { seat: seat, amount: amount }); });
    }

    $(document).on('click', '.js-sit', function () {
        buyIn($(this).data('seat'));
    });
    $(document).on('click', '.js-pk-act', function () {
        const action = $(this).data('action');
        Casino.act(action, action === 'raise' ? { amount: raiseTo } : {}).then(() => CasinoSound.knock(0.3, 600));
    });
    $(document).on('input', '.js-raise-range', function () { setRaise(Number(this.value)); });
    $(document).on('change', '.js-raise-amount', function () { setRaise(Number(this.value)); });
    $(document).on('click', '.js-raise-preset', function () {
        const s = state, preset = $(this).data('preset');
        const potAfterCall = s.pot + s.toCall;
        if (preset === 'min') setRaise(s.minRaiseTo);
        if (preset === 'half') setRaise(s.currentBet + Math.round(potAfterCall / 2));
        if (preset === 'pot') setRaise(s.currentBet + potAfterCall);
    });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    setInterval(tickTimer, 200);
    // wide screens: the table fills the screen; again after each update, as the buttons under it change
    const fit = Casino.fitBoard('.cs-holdem .pk-table-wrap', '.cs-holdem .pk-controls');

    Casino.start(s => { render(s); fit(); });
})();
