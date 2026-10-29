/**
 * Dou Dizhu (斗地主) table: three players, me at the bottom, the next player on the right, the one after on the left.
 * Cards are picked by tapping them; the server checks every play.
 */
(function () {
    const SUITS = { S: '♠', H: '♥', D: '♦', C: '♣' };
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    let state = null;
    let selected = new Set();
    let handKey = '';
    let lastRound = null, lastMyTurn = false, announced = null, lastPlayKey = null, playedRound = null;

    function card(c, cls) {
        if (c === '??') return `<span class="cs-card-face back ${cls || ''}"></span>`;
        if (c === 'LJ' || c === 'BJ') {
            return `<span class="cs-card-face dz-joker ${c === 'BJ' ? 'red' : ''} ${cls || ''}" data-card="${c}"><b>JOKER</b><i>★</i></span>`;
        }
        const suit = c.slice(-1), rank = c.slice(0, -1);
        return `<span class="cs-card-face ${suit === 'H' || suit === 'D' ? 'red' : ''} ${cls || ''}" data-card="${esc(c)}"><b>${esc(rank)}</b><i>${SUITS[suit]}</i></span>`;
    }

    const cards = (list, cls) => list.map(c => card(c, cls)).join('');

    /** Only touches the page when the html changed, so cards do not flicker on every update. */
    function setHtml(selector, html) {
        const el = $(selector);
        if (el.data('html') !== html) el.data('html', html).html(html);
        return el;
    }

    /** What a seat did last: its bid, a pass or the cards it played. */
    function moveHtml(seat) {
        if (seat.move === 'pass') return `<span class="dz-say">${esc(t('ddz_pass'))}</span>`;
        if (/^bid\d$/.test(seat.move)) {
            const n = Number(seat.move.slice(3));
            return `<span class="dz-say ${n ? 'bid' : ''}">${esc(n ? t('ddz_bid_n', { n: n }) : t('ddz_no_bid'))}</span>`;
        }
        if (seat.move === 'play' && seat.played.length) return `<div class="dz-played">${cards(seat.played, 'sm')}</div>`;
        return '';
    }

    function roleBadge(s, seat) {
        if (!s.landlord) return '';
        return seat.landlord
            ? `<span class="dz-role landlord"><i class="fas fa-crown"></i> ${esc(t('ddz_landlord'))}</span>`
            : `<span class="dz-role farmer"><i class="fas fa-seedling"></i> ${esc(t('ddz_farmer'))}</span>`;
    }

    function seatHtml(s, seat, place) {
        if (!seat || seat.empty) {
            const sit = s.can.sit && !s.solo
                ? `<button type="button" class="btn btn-sm btn-outline-warning js-sit" data-seat="${seat ? seat.no : 0}"><i class="fas fa-chair"></i> ${esc(t('pk_sit'))}</button>`
                : `<span class="cs-muted">${esc(t('free'))}</span>`;
            return `<div class="dz-player empty">${sit}</div>`;
        }
        const tags = [];
        if (seat.auto) tags.push(`<span class="dz-tag">${esc(t('ddz_auto_on'))}</span>`);
        if (seat.leaving) tags.push(`<span class="dz-tag">${esc(t('pk_leaving'))}</span>`);
        const result = s.phase === 'result' && seat.result
            ? `<span class="dz-result ${seat.result > 0 ? 'plus' : 'minus'}">${seat.result > 0 ? '+' : ''}${fmt(seat.result)}</span>` : '';
        const stack = seat.stack !== null ? `<span class="dz-stack"><i class="fas fa-coins"></i> ${fmt(seat.stack)}</span>` : `<span class="dz-stack"><i class="fas fa-robot"></i></span>`;
        const info = `<div class="dz-player ${seat.turn ? 'turn' : ''} ${seat.landlord ? 'is-landlord' : ''}">
                <div class="dz-name">${esc(seat.name)}${seat.me ? ' (' + esc(t('you')) + ')' : ''}</div>
                ${roleBadge(s, seat)} ${stack} ${tags.join(' ')} ${result}
            </div>`;
        if (place === 'me') {
            return `<div class="dz-me-row">${info}<div class="dz-move">${moveHtml(seat)}</div></div>`;
        }
        // the others: a card back with how many cards are left; their cards face up when the hand is over
        const back = seat.cards.length
            ? `<div class="dz-open">${cards(seat.cards, 'sm')}</div>`
            : (seat.count ? `<div class="dz-back"><span class="cs-card-face back"></span><b class="${seat.count <= 2 ? 'warn' : ''}">${seat.count}</b></div>` : '');
        return `${info}${back}<div class="dz-move">${moveHtml(seat)}</div>`;
    }

    function statusText(s) {
        const me = s.seats.find(x => x.me);
        const turn = s.seats.find(x => x.turn);
        if (s.phase === 'waiting') {
            if (!me) return s.solo ? t('ddz_solo_start') : t('ddz_wait_players');
            return s.solo ? t('ddz_solo_ready') : (s.seats.filter(x => !x.empty).length < 3 ? t('ddz_wait_players') : t('ddz_starting'));
        }
        if (s.phase === 'bidding') return turn && turn.me ? t('ddz_your_bid') : t('ddz_bidding', { name: turn ? turn.name : '' });
        if (s.phase === 'playing') return turn && turn.me ? t('your_turn') : t('turn_of', { name: turn ? turn.name : '' });
        if (s.phase === 'result') {
            const winner = s.seats.find(x => x.no === s.winner);
            let text = t(winner && winner.landlord ? 'ddz_landlord_wins' : 'ddz_farmers_wins') + ' · x' + s.mult;
            if (s.spring) text += ' · ' + t('ddz_spring');
            if (me && me.result) text += ' · ' + (me.result > 0 ? t('you_win', { amount: fmt(me.result) }) : t('you_lose', { amount: fmt(-me.result) }));
            return text;
        }
        return '';
    }

    function render(s) {
        state = s;
        const myNo = s.mySeat || 1;
        const right = myNo % 3 + 1, left = right % 3 + 1;
        const bySeat = no => s.seats.find(x => x.no === no);
        $('.js-round').text(s.round ? t('round_n', { n: s.round }) : '');

        // a new hand: nothing picked
        if (s.round !== lastRound) {
            lastRound = s.round;
            selected = new Set();
        }

        setHtml('.js-dz-bottom', s.bottom.length ? cards(s.bottom, 'sm') : '');
        $('.js-dz-base').text(fmt(s.base));
        $('.js-dz-bid').text(s.bid ? t('ddz_bid_n', { n: s.bid }) : '-');
        $('.js-dz-mult').text('x' + s.mult);
        setHtml('[data-place=left]', seatHtml(s, bySeat(left), 'left'));
        setHtml('[data-place=right]', seatHtml(s, bySeat(right), 'right'));
        setHtml('[data-place=me]', s.mySeat ? seatHtml(s, bySeat(myNo), 'me') : '');
        $('.js-dz-status').text(statusText(s)).toggleClass('result', s.phase === 'result');

        // my cards: picked cards stay picked between updates as long as they are still in the hand
        const me = bySeat(s.mySeat);
        const hand = me && !me.empty ? me.cards : [];
        const key = hand.join(',');
        if (key !== handKey) {
            handKey = key;
            selected = new Set([...selected].filter(c => hand.includes(c)));
        }
        setHtml('.js-dz-hand', hand.map(c => card(c, selected.has(c) ? 'pick' : '')).join('')).toggleClass('many', hand.length > 17);

        renderActions(s);
        sounds(s);
        Casino.renderRecords(s);
    }

    function renderActions(s) {
        const b = [];
        const btn = (cls, icon, text, data, disabled) =>
            `<button type="button" class="btn ${cls}" ${data || ''} ${disabled ? 'disabled' : ''}>${icon ? `<i class="fas fa-${icon}"></i> ` : ''}${esc(text)}</button>`;
        if (s.can.sit) {
            b.push(btn('btn-warning btn-lg js-sit', 'chair', s.solo ? t('ddz_solo_sit') : t('pk_sit'), 'data-seat="0"'));
        }
        if (s.can.go) {
            b.push(btn('btn-warning btn-lg cs-go js-dz-act', 'layer-group', s.phase === 'result' ? t('ddz_next_hand') : t('ddz_deal'), 'data-action="go"'));
        }
        if (s.phase === 'bidding' && s.seats.some(x => x.me && x.turn)) {
            b.push(btn('btn-outline-light btn-lg js-dz-bid', '', t('ddz_no_bid'), 'data-points="0"'));
            s.can.bid.forEach(n => b.push(btn('btn-warning btn-lg js-dz-bid', '', t('ddz_bid_n', { n: n }), `data-points="${n}"`)));
        }
        if (s.can.play) {
            if (s.can.pass) b.push(btn('btn-outline-light btn-lg js-dz-act', '', t('ddz_pass'), 'data-action="pass"'));
            b.push(btn('btn-info btn-lg js-dz-hint', 'lightbulb', t('ddz_hint')));
            b.push(btn('btn-success btn-lg js-dz-play', 'hand-paper', t('ddz_play'), '', selected.size === 0));
        }
        if (s.can.auto) {
            const me = s.seats.find(x => x.me);
            b.push(btn('btn-sm btn-outline-secondary js-dz-auto', 'robot', me && me.auto ? t('ddz_auto_off') : t('ddz_auto'), `data-on="${me && me.auto ? 0 : 1}"`));
        }
        setHtml('.js-dz-actions', b.join(''));
    }

    /** A bomb or the rocket: a big "炸弹 x2" / "王炸 x4" over the table, the table shakes and the multiplier jumps. */
    function boom(s, type) {
        const rocket = type === 'rocket';
        const text = t(rocket ? 'ddz_rocket_fx' : 'ddz_bomb_fx', { n: rocket ? s.rocketMult : s.bombMult });
        CasinoSound.chime(rocket ? [392, 523, 659, 784, 1047, 1319] : [330, 440, 554], 0.07, 0.22);
        $('.js-dz-mult').removeClass('bump');
        void document.body.offsetWidth;
        $('.js-dz-mult').addClass('bump');
        if (!Casino.animOn()) return;
        const felt = $('.dz-felt');
        felt.find('.dz-boom').remove();
        felt.removeClass('dz-shake');
        void felt[0].offsetWidth;
        felt.addClass('dz-shake');
        const fx = $(`<div class="dz-boom ${rocket ? 'rocket' : 'bomb'}"><span>${esc(text)}</span></div>`).appendTo(felt);
        setTimeout(() => fx.remove(), 1800);
    }

    function sounds(s) {
        if (typeof CasinoSound === 'undefined') return;
        const myTurn = s.seats.some(x => x.me && x.turn);
        if (myTurn && !lastMyTurn) CasinoSound.chime([880, 1175], 0.12, 0.14);
        lastMyTurn = myTurn;
        const playKey = s.last ? s.last.seat + ':' + s.last.cards.join(',') : '';
        if (playKey && playKey !== lastPlayKey) {
            const big = ['bomb', 'rocket'].includes(s.last.type);
            CasinoSound.knock(big ? 0.7 : 0.3, big ? 180 : 900);
            // not when the page opens on a bomb that was played before
            if (big && lastPlayKey !== null) boom(s, s.last.type);
        }
        lastPlayKey = playKey;
        const me = s.seats.find(x => x.me);
        if (s.phase === 'playing') playedRound = s.round;
        // the win is shown for a hand we saw being played (not when the page opens on a result)
        if (s.phase === 'result' && announced !== s.round && playedRound === s.round) {
            announced = s.round;
            if (me && me.result > 0) Casino.winShow('.dz-felt', me.result);
        }
    }

    /** Asks how many chips the player takes to the table, then sits down. */
    function buyIn(seat) {
        const [min, max] = state.buyin;
        const def = Math.min(max, Math.max(min, state.base * 100));
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

    // picking cards
    $(document).on('click', '.js-dz-hand .cs-card-face', function () {
        const c = String($(this).data('card'));
        selected.has(c) ? selected.delete(c) : selected.add(c);
        $(this).toggleClass('pick', selected.has(c));
        $('.js-dz-play').prop('disabled', selected.size === 0);
    });
    // a right click (or a long press) puts all picked cards back
    $(document).on('contextmenu', '.js-dz-hand', function (e) {
        e.preventDefault();
        selected = new Set();
        $('.js-dz-hand .cs-card-face').removeClass('pick');
        $('.js-dz-play').prop('disabled', true);
    });

    $(document).on('click', '.js-sit', function () { buyIn(Number($(this).data('seat'))); });
    $(document).on('click', '.js-dz-bid', function () { Casino.act('bid', { points: $(this).data('points') }); });
    $(document).on('click', '.js-dz-act', function () { Casino.act($(this).data('action')); });
    $(document).on('click', '.js-dz-auto', function () { Casino.act('auto', { on: $(this).data('on') }); });
    $(document).on('click', '.js-dz-play', function () {
        if (!selected.size) return;
        Casino.act('play', { cards: [...selected].join(',') });
    });
    $(document).on('click', '.js-dz-hint', function () {
        if (!state || !state.hint.length) {
            Casino.toast(t('ddz_no_hint'));
            return;
        }
        selected = new Set(state.hint);
        $('.js-dz-hand .cs-card-face').each(function () { $(this).toggleClass('pick', selected.has(String($(this).data('card')))); });
        $('.js-dz-play').prop('disabled', false);
    });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    // wide screens: the table fills the screen; again after each update, as the buttons change
    const fit = Casino.fitBoard('.cs-ddz .dz-felt', '.cs-ddz .dz-rules');

    Casino.start(s => { render(s); fit(); });
})();
