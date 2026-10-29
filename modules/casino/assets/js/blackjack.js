(function () {
    const SUITS = { S: '♠', H: '♥', D: '♦', C: '♣' };
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    const outcome = o => t(o === 'push' ? 'push' : o); // win, lose, push, blackjack
    let announced = null;

    function card(c) {
        if (c === '??') return '<span class="cs-card-face back"></span>';
        const suit = c.slice(-1);
        const rank = c.slice(0, -1);
        const red = suit === 'H' || suit === 'D';
        return `<span class="cs-card-face ${red ? 'red' : ''}"><b>${rank}</b><i>${SUITS[suit]}</i></span>`;
    }

    function render(s) {
        $('.js-round').text(t('round_n', { n: s.round }));
        $('.js-rules').text(t('rules', {
            min: fmt(s.minBet),
            max: fmt(s.maxBet),
            payout: s.payout,
            soft17: t(s.hitsSoft17 ? 'hits_soft17' : 'stands_soft17'),
        }));

        $('.js-dealer').html(s.dealer.map(card).join(''));
        $('.js-dealer-value').text(s.dealer.length ? s.dealerValue : '');

        $('.js-seats').html(s.seats.map(seat => {
            if (seat.empty) {
                return `<div class="cs-seat empty ${s.can.sit ? 'can-sit' : ''}" data-seat="${seat.no}"><div class="cs-seat-no">${seat.no}</div>${s.can.sit ? `<div><i class="fas fa-chair"></i> ${esc(t('take_seat'))}</div>` : `<div class="cs-muted">${esc(t('free'))}</div>`}</div>`;
            }
            let badge = '';
            if (seat.outcome) {
                const diff = seat.ret - seat.bet;
                badge = `<div class="cs-outcome ${seat.outcome}">${esc(outcome(seat.outcome))} ${diff ? (diff > 0 ? '+' : '') + fmt(diff) : ''}</div>`;
            } else if (seat.value > 21) {
                badge = `<div class="cs-outcome lose">${esc(t('bust'))}</div>`;
            } else if (seat.blackjack) {
                badge = `<div class="cs-outcome blackjack">${esc(t('blackjack'))}</div>`;
            }
            return `<div class="cs-seat ${seat.me ? 'me' : ''} ${seat.turn ? 'turn' : ''}">
                <div class="cs-seat-no">${seat.no}</div>
                <div class="cs-hand">${seat.cards.map(card).join('')}</div>
                <div class="cs-value">${seat.cards.length ? seat.value : ''}</div>
                ${badge}
                <div class="cs-seat-name">${esc(seat.name)}${seat.me ? ' (' + esc(t('you')) + ')' : ''}</div>
                <div class="cs-seat-bet">${seat.bet ? '<i class="fas fa-coins"></i> ' + fmt(seat.bet) + (seat.doubled ? ' x2' : '') : ''}</div>
                ${seat.bet && !seat.outcome ? `<div class="cs-seat-win">${esc(t('can_win', { amount: fmt(seat.bet) }))}${seat.cards.length <= 2 && !seat.doubled ? ' · BJ +' + fmt(Math.floor(seat.bet * s.payout)) : ''}</div>` : ''}
            </div>`;
        }).join(''));

        // solo: after the result the next bet starts a new hand; the player deals themselves
        const soloBet = s.solo && s.phase === 'result';
        $('.js-bet-controls').toggleClass('d-none', !(s.can.bet || soloBet));
        $('.js-go').toggleClass('d-none', !s.solo).prop('disabled', !(s.phase === 'betting' && s.myBet > 0));
        $('.js-timer').toggleClass('d-none', !!s.solo);
        $('.js-clear').prop('disabled', !s.can.clear);
        $('.js-play-controls').toggleClass('d-none', !s.can.hit);
        $('.js-act[data-action=double]').toggleClass('d-none', !s.can.double);
        $('.js-sit-controls').toggleClass('d-none', !s.can.sit);

        let status = '';
        if (s.phase === 'betting') {
            status = s.remaining === null ? t('waiting_bets') : t('betting_soon');
            if (s.mySeat && s.myBet) status = t('your_bet_wait', { amount: fmt(s.myBet) });
            if (s.solo) status = s.myBet ? t('solo_ready') : t('solo_hint');
        } else if (s.phase === 'playing') {
            const turn = s.seats.find(x => x.turn);
            status = s.can.hit ? t('your_turn') : (turn ? t('turn_of', { name: turn.name }) : '');
        } else if (s.phase === 'result') {
            status = t(s.dealerValue > 21 ? 'dealer_bust' : 'dealer_result', { value: s.dealerValue });
            const me = s.seats.find(x => x.me);
            if (me && me.outcome && announced !== s.round) {
                announced = s.round;
                const diff = me.ret - me.bet;
                Casino.toast(outcome(me.outcome) + (diff ? ` ${diff > 0 ? '+' : ''}${fmt(diff)}` : ''), diff > 0 ? 'good' : '');
            }
        }
        Casino.renderRecords(s);

        if (s.watchers) status += ' · ' + t('online', { count: s.watchers });
        $('.js-status').text(status);
    }

    $(document).on('click', '.cs-seat.can-sit', function () {
        Casino.act('sit', { seat: $(this).data('seat') });
    });
    $(document).on('click', '.js-bet', function () {
        Casino.act('bet', { amount: Casino.chip() });
    });
    $(document).on('click', '.js-clear', function () {
        Casino.act('clear');
    });
    $(document).on('click', '.js-act', function () {
        Casino.act($(this).data('action'));
    });

    // solo: deal yourself (also with the space bar)
    $(document).on('click', '.js-go', function () {
        Casino.act('go');
    });
    $(document).on('keydown', e => {
        if (e.code === 'Space' && !$(e.target).is('input, textarea, select, button, a') && $('.js-go').is(':visible:enabled')) {
            e.preventDefault();
            Casino.act('go');
        }
    });

    // wide screens: the table fills the screen; again after each update, as the buttons under it change
    const fit = Casino.fitBoard('.cs-bj .cs-felt-bj', '.cs-bj .cs-controls');

    Casino.start(s => { render(s); fit(); });
})();
