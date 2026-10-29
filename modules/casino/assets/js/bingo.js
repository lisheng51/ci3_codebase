(function () {
    const LETTERS = ['B', 'I', 'N', 'G', 'O'];
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    let shownBalls = null;   // number of balls on screen
    let shownRound = null;
    let lastWon = 0;
    let boardBuilt = false;

    const letter = n => LETTERS[Math.floor((n - 1) / 15)];
    const ball = (n, cls) => `<div class="bg-ball bg-col-${letter(n)} ${cls || ''}"><span>${letter(n)}</span><b>${n}</b></div>`;

    function buildBoard() {
        if (boardBuilt) return;
        boardBuilt = true;
        let html = '';
        LETTERS.forEach((l, row) => {
            html += `<div class="bg-board-row"><span class="bg-board-letter bg-col-${l}">${l}</span>`;
            for (let i = 1; i <= 15; i++) html += `<span class="bg-num" data-n="${row * 15 + i}">${row * 15 + i}</span>`;
            html += '</div>';
        });
        $('.js-bg-board').html(html);
    }

    function render(s) {
        buildBoard();
        if (s.round !== shownRound) {
            // a prize won before the page was opened is not celebrated again
            lastWon = shownRound === null ? s.myWon : 0;
            shownRound = s.round;
            shownBalls = null;
        }
        $('.js-round').text(t('round_n', { n: s.round }));

        // balls: a new ball rolls in, the last few stay next to it
        const count = s.drawn.length;
        const fresh = shownBalls !== null && count > shownBalls && count - shownBalls <= 3;
        if (count !== shownBalls) {
            const last = s.drawn[count - 1];
            $('.js-bg-ball').html(last ? ball(last, fresh && Casino.animOn() ? 'roll' : '') : '<div class="bg-ball bg-empty"><b>?</b></div>');
            $('.js-bg-recent').html(s.drawn.slice(Math.max(0, count - 7), count - 1).reverse().map(n => ball(n, 'small')).join(''));
            if (fresh) CasinoSound.chime([520 + last * 6, 780 + last * 6], 0.07, 0.12);
            shownBalls = count;
        }
        const drawn = new Set(s.drawn);
        $('.bg-num').each(function () {
            const n = Number(this.dataset.n);
            this.classList.toggle('on', drawn.has(n));
            this.classList.toggle('last', n === s.drawn[count - 1]);
        });

        // status line
        let status = '';
        if (s.phase === 'buying') status = s.remaining === null ? t('bg_buy_start') : t('bg_buying');
        else if (s.phase === 'drawing') status = t('bg_drawing', { n: count });
        else status = [s.lineWinners.length ? t('bg_line_by', { names: s.lineWinners.join(', ') }) : '', t('bg_full_by', { names: s.fullWinners.join(', ') })].filter(Boolean).join(' · ');
        $('.js-status').text(status);

        // prizes
        $('.js-bg-pot').text(fmt(s.pot));
        $('.js-bg-line').text(fmt(s.linePrize));
        $('.js-bg-full').text(fmt(s.fullPrize));
        $('.js-bg-line-by').text(s.lineWinners.join(', '));
        $('.js-bg-full-by').text(s.fullWinners.join(', '));

        // buying
        const buying = s.phase === 'buying';
        const have = s.myCards.length;
        $('.js-bg-buy-buttons').html([1, 2, 4].filter(n => n <= s.maxCards).map(n =>
            `<button type="button" class="btn btn-warning js-bg-buy" data-n="${n}" ${!buying || have + n > s.maxCards || n * s.cardPrice > s.balance ? 'disabled' : ''}>${esc(t('bg_buy_n', { n: n, amount: fmt(n * s.cardPrice) }))}</button>`
        ).join(''));
        $('.js-clear').prop('disabled', !buying || !have);
        $('.js-bg-my-count').text(have ? t('bg_cards_n', { n: have }) + ' · ' + have + '/' + s.maxCards : '');

        // my cards: numbers are marked by themselves; the missing ones light up when the card is close
        $('.js-bg-cards').html(have ? s.myCards.map(card => {
            const close = card.missing <= 2 && s.phase === 'drawing';
            const cells = card.n.map(n => {
                if (n === 0) return '<span class="bg-cell free"><i class="fas fa-star"></i></span>';
                const on = drawn.has(n);
                return `<span class="bg-cell ${on ? 'on' : ''} ${!on && close ? 'need' : ''} ${n === s.drawn[count - 1] ? 'last' : ''}">${n}</span>`;
            }).join('');
            const badge = card.full ? '<span class="bg-badge full">BINGO</span>' : (card.line ? `<span class="bg-badge line">${esc(t('bg_line_badge'))}</span>` : '');
            return `<div class="bg-card ${card.full ? 'won' : ''}">
                <div class="bg-card-head">${LETTERS.map(l => `<span class="bg-col-${l}">${l}</span>`).join('')}</div>
                <div class="bg-card-grid">${cells}</div>
                <div class="bg-card-foot">${badge}<span>${card.full ? '' : esc(t('bg_to_go', { n: card.missing }))}</span></div>
            </div>`;
        }).join('') : `<div class="cs-muted small">${esc(t('bg_no_cards'))}</div>`);

        // a prize for me
        if (s.myWon > lastWon) {
            const amount = s.myWon - lastWon;
            const full = s.myCards.some(c => c.full);
            CasinoSound.chime(full ? [523, 659, 784, 1047, 1319, 1568] : [659, 784, 1047], 0.1, 0.2);
            celebrate(t(full ? 'bg_bingo_won' : 'bg_line_won', { amount: fmt(amount) }));
        }
        lastWon = s.myWon;

        $('.js-players').html(s.players.map(p =>
            `<li class="d-flex justify-content-between ${p.me ? 'fw-bold' : ''}"><span><i class="fas fa-user-circle"></i> ${esc(p.name)}${p.me ? ' (' + esc(t('you')) + ')' : ''}</span><span>${p.cards ? esc(t('bg_cards_n', { n: p.cards })) : ''}${p.won ? ' <span class="cs-plus">+' + fmt(p.won) + '</span>' : ''}</span></li>`
        ).join('') || `<li class="cs-muted">${esc(t('nobody'))}</li>`);
        $('.js-bg-history').html(s.history.map(h =>
            `<li class="mb-1"><b>${esc(t('bg_round_line', { round: h.round, balls: h.balls }))}</b><br>` +
            (h.line.names.length ? esc(t('bg_line_by', { names: h.line.names.join(', ') })) + ` <span class="cs-plus">+${fmt(h.line.prize)}</span> · ` : '') +
            esc(t('bg_full_by', { names: h.full.names.join(', ') })) + ` <span class="cs-plus">+${fmt(h.full.prize)}</span></li>`
        ).join('') || `<li class="cs-muted">${esc(t('no_rolls'))}</li>`);

        Casino.renderRecords(s);
    }

    function celebrate(text) {
        const box = $('<div class="sl-celebrate"><div class="sl-cel-text"></div></div>');
        box.find('.sl-cel-text').text(text);
        if (Casino.animOn()) {
            for (let i = 0; i < 50; i++) {
                $('<i class="sl-coin"></i>').css({ left: (Math.random() * 100) + '%', animationDelay: (Math.random() * 1.2) + 's', animationDuration: (1.6 + Math.random() * 1.4) + 's' }).appendTo(box);
            }
        }
        box.appendTo('body').on('click', () => box.remove());
        setTimeout(() => box.addClass('show'), 10);
        setTimeout(() => box.remove(), 3500);
    }

    $(document).on('click', '.js-bg-buy', function () {
        Casino.act('buy', { amount: $(this).data('n') });
    });
    $(document).on('click', '.js-clear', function () {
        Casino.act('clear');
    });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    Casino.start(render);
})();
