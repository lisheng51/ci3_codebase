/**
 * Chess and xiangqi table: draws the board (from the player's side), lets the player pick a piece and a target
 * from the legal moves the server sent, and shows the clocks, the moves and the result.
 */
(function () {
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    const GAME = document.querySelector('.cs-boardgame').dataset.game;
    const XQ = GAME === 'xiangqi';
    const COLS = XQ ? 9 : 8, ROWS = XQ ? 10 : 8;
    // solid chess figures for both colours (the text variant, not emoji), coloured by css
    const CHESS = { K: '♚', Q: '♛', R: '♜', B: '♝', N: '♞', P: '♟' };
    const XQ_RED = { K: '帅', A: '仕', B: '相', N: '马', R: '车', C: '炮', P: '兵' };
    const XQ_BLACK = { K: '将', A: '士', B: '象', N: '马', R: '车', C: '炮', P: '卒' };
    const FILES = 'abcdefghi';

    let state = null;
    let selected = '';
    let clocks = { at: 0, seats: [] };
    let lastMoveCount = -1, lastRound = null, playedRound = null, announced = null, lastMyTurn = false;
    let soloSeat = 1, soloLevel = 2;
    try {
        soloSeat = Number(localStorage.getItem('casino-board-seat')) || 1;
        soloLevel = Number(localStorage.getItem('casino-board-level')) || 2;
    } catch (e) { }

    const flipped = () => state && state.mySeat === 2;
    /** Square name of row r / column c of the board as the server sends it (row 0 = black's side). */
    const squareName = (r, c) => FILES[c] + (XQ ? 9 - r : 8 - r);
    const isWhite = p => p === p.toUpperCase();

    function pieceHtml(p) {
        const u = p.toUpperCase();
        if (XQ) return `<span class="bd-piece xq ${isWhite(p) ? 'red' : 'black'}">${(isWhite(p) ? XQ_RED : XQ_BLACK)[u]}</span>`;
        return `<span class="bd-piece ch ${isWhite(p) ? 'white' : 'black'}">${CHESS[u]}&#xFE0E;</span>`;
    }

    /** The lines of the xiangqi board: points at (column * 100 + 50, row * 100 + 50), the river, the two palaces. */
    function xqLines() {
        const l = [];
        for (let r = 0; r < 10; r++) l.push(`<line x1="50" y1="${r * 100 + 50}" x2="850" y2="${r * 100 + 50}"/>`);
        for (let c = 0; c < 9; c++) {
            if (c === 0 || c === 8) l.push(`<line x1="${c * 100 + 50}" y1="50" x2="${c * 100 + 50}" y2="950"/>`);
            else l.push(`<line x1="${c * 100 + 50}" y1="50" x2="${c * 100 + 50}" y2="450"/><line x1="${c * 100 + 50}" y1="550" x2="${c * 100 + 50}" y2="950"/>`);
        }
        [[0, 2], [7, 9]].forEach(([a, b]) => {
            l.push(`<line x1="350" y1="${a * 100 + 50}" x2="550" y2="${b * 100 + 50}"/><line x1="550" y1="${a * 100 + 50}" x2="350" y2="${b * 100 + 50}"/>`);
        });
        return `<svg class="bd-lines" viewBox="0 0 900 1000" preserveAspectRatio="none" aria-hidden="true">${l.join('')}
            <text x="240" y="515">楚 河</text><text x="660" y="515">汉 界</text></svg>`;
    }

    function renderBoard(s) {
        const flip = flipped();
        const legal = s.can.move ? s.legal : [];
        const targets = selected ? legal.filter(m => m.startsWith(selected)).map(m => m.slice(2, 4)) : [];
        const last = s.last ? [s.last.slice(0, 2), s.last.slice(2, 4)] : [];
        const cells = [];
        for (let dr = 0; dr < ROWS; dr++) {
            for (let dc = 0; dc < COLS; dc++) {
                const r = flip ? ROWS - 1 - dr : dr, c = flip ? COLS - 1 - dc : dc;
                const sq = squareName(r, c);
                const p = s.rows[r][c];
                const cls = ['bd-sq'];
                if (!XQ) cls.push((r + c) % 2 ? 'dark' : 'light');
                if (last.includes(sq)) cls.push('last');
                if (sq === selected) cls.push('sel');
                if (sq === s.check) cls.push('check');
                if (targets.includes(sq)) cls.push(p ? 'target cap' : 'target');
                if (legal.some(m => m.startsWith(sq))) cls.push('movable');
                // coordinates along the edges (chess)
                let coord = '';
                if (!XQ && dc === 0) coord += `<i class="bd-rank">${sq[1]}</i>`;
                if (!XQ && dr === ROWS - 1) coord += `<i class="bd-file">${sq[0]}</i>`;
                const style = XQ ? ` style="left:${(dc + 0.5) / 9 * 100}%;top:${(dr + 0.5) / 10 * 100}%"` : '';
                cells.push(`<div class="${cls.join(' ')}" data-sq="${sq}"${style}>${coord}${p ? pieceHtml(p) : ''}</div>`);
            }
        }
        $('.js-board').html((XQ ? xqLines() : '') + cells.join(''));
    }

    function clockText(sec) {
        if (sec === null || sec === undefined) return '';
        sec = Math.max(0, Math.ceil(sec));
        return Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
    }

    function colorName(no) {
        return no === 1 ? t(XQ ? 'board_red' : 'board_white') : t('board_black');
    }

    function playerHtml(s, seat) {
        if (!seat || seat.empty) {
            const sit = s.can.sit ? `<button type="button" class="btn btn-sm btn-outline-warning js-bd-sit" data-seat="${seat.no}"><i class="fas fa-chair"></i> ${esc(t('pk_sit'))} (${esc(colorName(seat.no))})</button>` : `<span class="cs-muted">${esc(t('free'))}</span>`;
            return `<div class="bd-pl empty">${sit}</div>`;
        }
        const dot = `<span class="bd-dot ${seat.no === 1 ? (XQ ? 'red' : 'white') : 'black'}"></span>`;
        const clock = seat.clock !== null ? `<span class="bd-clock js-clock ${seat.turn ? 'run' : ''}" data-no="${seat.no}">${clockText(seat.clock)}</span>` : '';
        const tags = (seat.bot ? ` <i class="fas fa-robot"></i>` : '') + (seat.away ? ` <span class="dz-tag">${esc(t('board_away'))}</span>` : '');
        return `<div class="bd-pl ${seat.turn ? 'turn' : ''}">${dot}<span class="bd-name">${esc(seat.name)}${seat.me ? ' (' + esc(t('you')) + ')' : ''}${tags}</span>${clock}</div>`;
    }

    function statusText(s) {
        const me = s.seats.find(x => x.me);
        if (s.phase === 'result' && s.result) {
            const w = s.result.winner;
            const why = t('board_reason_' + s.result.reason);
            if (w === 0) return t('board_draw') + ' · ' + why;
            const winner = s.seats.find(x => x.no === w);
            const mine = me && me.no === w;
            return (mine ? t('board_you_won') : (me ? t('board_you_lost') : t('board_wins', { name: winner ? winner.name : colorName(w) }))) + ' · ' + why;
        }
        if (s.phase === 'playing') {
            let text = s.can.move ? t('board_your_move') : t('turn_of', { name: (s.seats.find(x => x.turn) || {}).name || '' });
            if (s.check) text += ' · ' + t('board_check');
            const other = me ? 3 - me.no : 0;
            if (me && s.draw === other) text += ' · ' + t('board_draw_wanted');
            if (me && s.draw === me.no) text += ' · ' + t('board_draw_offered');
            if (s.declined) text += ' · ' + t('board_declined');
            return text;
        }
        if (s.solo) return t('board_solo_hint');
        return s.seats.filter(x => !x.empty).length < 2 ? t('board_waiting') : t('board_starting');
    }

    function renderActions(s) {
        const b = [];
        if (s.can.go) {
            const opt = (v, cur, text) => `<option value="${v}" ${v === cur ? 'selected' : ''}>${esc(text)}</option>`;
            b.push(`<div class="bd-solo">
                <label>${esc(t('board_play_as'))} <select class="form-select form-select-sm js-bd-seat">${opt(1, soloSeat, colorName(1))}${opt(2, soloSeat, colorName(2))}</select></label>
                <label>${esc(t('board_level'))} <select class="form-select form-select-sm js-bd-level">${[1, 2, 3].map(l => opt(l, soloLevel, t('board_level_' + l) + ' (+' + s.soloWin[{ 1: 'easy', 2: 'normal', 3: 'hard' }[l]] + '%)')).join('')}</select></label>
                <button type="button" class="btn btn-warning cs-go js-bd-go"><i class="fas fa-play"></i> ${esc(t('board_start', { stake: fmt(s.stake) }))}</button>
            </div>`);
        }
        const me = s.seats.find(x => x.me);
        if (s.can.draw) {
            const accept = me && s.draw === 3 - me.no;
            b.push(`<button type="button" class="btn btn-sm ${accept ? 'btn-success' : 'btn-outline-light'} js-bd-act" data-action="draw"><i class="fas fa-handshake"></i> ${esc(t(accept ? 'board_accept_draw' : 'board_offer_draw'))}</button>`);
        }
        if (s.can.resign) {
            b.push(`<button type="button" class="btn btn-sm btn-outline-danger js-bd-resign"><i class="fas fa-flag"></i> ${esc(t('board_resign'))}</button>`);
        }
        $('.js-bd-actions').html(b.join(''));
    }

    function renderMoves(s) {
        const rows = [];
        for (let i = 0; i < s.moves.length; i += 2) {
            rows.push(`<li><span>${esc(s.moves[i])}</span><span>${esc(s.moves[i + 1] || '')}</span></li>`);
        }
        const box = $('.js-bd-moves');
        box.html(rows.join(''));
        box.scrollTop(box[0].scrollHeight);
    }

    function render(s) {
        state = s;
        if (s.round !== lastRound) {
            lastRound = s.round;
            selected = '';
        }
        if (!s.can.move || !s.legal.some(m => m.startsWith(selected))) selected = '';
        $('.js-round').text(s.round ? t('round_n', { n: s.round }) : '');
        renderBoard(s);
        // me at the bottom, the other at the top (a watcher sees white / red at the bottom)
        const bottomNo = s.mySeat || 1;
        $('[data-place=bottom]').html(playerHtml(s, s.seats.find(x => x.no === bottomNo)));
        $('[data-place=top]').html(playerHtml(s, s.seats.find(x => x.no === 3 - bottomNo)));
        clocks = { at: Date.now(), seats: s.seats.map(x => ({ no: x.no, clock: x.clock, turn: x.turn })) };
        $('.js-bd-status').text(statusText(s)).toggleClass('result', s.phase === 'result');
        renderActions(s);
        renderMoves(s);
        sounds(s);
        Casino.renderRecords(s);
    }

    function sounds(s) {
        const count = s.moves.length;
        if (lastMoveCount >= 0 && count > lastMoveCount && typeof CasinoSound !== 'undefined') {
            CasinoSound.knock(s.check ? 0.6 : 0.35, s.check ? 300 : 700);
        }
        lastMoveCount = count;
        if (s.can.move && !lastMyTurn && typeof CasinoSound !== 'undefined') CasinoSound.chime([880, 1175], 0.12, 0.12);
        lastMyTurn = s.can.move;
        if (s.phase === 'playing') playedRound = s.round;
        const me = s.seats.find(x => x.me);
        if (s.phase === 'result' && s.result && me && announced !== s.round && playedRound === s.round) {
            announced = s.round;
            if (s.result.winner === me.no) {
                const won = s.solo ? Math.floor(s.stake * s.soloWin[{ 1: 'easy', 2: 'normal', 3: 'hard' }[s.level]] / 100) : s.stake;
                Casino.winShow('.bd-felt', s.stake + won);
            }
        }
    }

    // the clocks tick between updates
    setInterval(() => {
        if (!state || state.phase !== 'playing') return;
        const gone = (Date.now() - clocks.at) / 1000;
        $('.js-clock').each(function () {
            const seat = clocks.seats.find(x => x.no === Number(this.dataset.no));
            if (!seat || seat.clock === null) return;
            const left = seat.turn ? seat.clock - gone : seat.clock;
            $(this).text(clockText(left)).toggleClass('low', seat.turn && left <= 30);
        });
    }, 250);

    function send(move) {
        selected = '';
        Casino.act('move', { move: move });
    }

    /** A pawn reaching the last rank: the player picks the new piece. */
    function promote(from, to) {
        const pieces = ['q', 'r', 'b', 'n'];
        const label = { q: CHESS.Q, r: CHESS.R, b: CHESS.B, n: CHESS.N };
        if (window.Swal && Swal.fire) {
            Swal.fire({
                title: t('board_promote'),
                input: 'radio',
                inputOptions: Object.fromEntries(pieces.map(p => [p, label[p] + '︎'])),
                inputValue: 'q',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: t('cancel'),
                background: '#0f3d2e',
                color: '#f1f5f2',
                confirmButtonColor: '#c99a2e',
            }).then(r => { if (r.isConfirmed) send(from + to + (r.value || 'q')); });
        } else {
            send(from + to + 'q');
        }
    }

    $(document).on('click', '.js-board .bd-sq', function () {
        if (!state || !state.can.move) return;
        const sq = this.dataset.sq;
        if (selected) {
            const moves = state.legal.filter(m => m.startsWith(selected + sq));
            if (moves.length > 1) return promote(selected, sq);
            if (moves.length === 1) return send(moves[0]);
        }
        // pick (or pick another) own piece that can move
        selected = state.legal.some(m => m.startsWith(sq)) && sq !== selected ? sq : '';
        renderBoard(state);
    });

    $(document).on('click', '.js-bd-sit', function () { Casino.act('sit', { seat: $(this).data('seat') }); });
    $(document).on('click', '.js-bd-act', function () { Casino.act($(this).data('action')); });
    $(document).on('click', '.js-bd-resign', function () {
        const ask = window.casinoConfirm ? casinoConfirm(t('board_resign_confirm')) : Promise.resolve(window.confirm(t('board_resign_confirm')));
        ask.then(ok => { if (ok) Casino.act('resign'); });
    });
    $(document).on('change', '.js-bd-seat', function () { soloSeat = Number(this.value); try { localStorage.setItem('casino-board-seat', soloSeat); } catch (e) { } });
    $(document).on('change', '.js-bd-level', function () { soloLevel = Number(this.value); try { localStorage.setItem('casino-board-level', soloLevel); } catch (e) { } });
    $(document).on('click', '.js-bd-go', function () { Casino.act('go', { seat: soloSeat, level: soloLevel }); });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    Casino.start(render);
})();
