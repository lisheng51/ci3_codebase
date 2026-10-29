/**
 * Shared part of the casino tables: polls the table state, sends actions, chip picker, countdown and toasts.
 * A game script calls Casino.start(renderFunction); render gets the state as the server sees it for this player.
 */
window.Casino = (function () {
    const C = window.CASINO_CONFIG;
    let render = null;
    let state = null;
    let receivedAt = 0;
    let busy = false;
    let pollTimer = null;
    let chip = 0;
    let hold = () => false; // a game can keep the balance back while an animation is still hiding the result
    // The big animations (dice shaker, roulette wheel) are the game, so they are on by default, also when the
    // system asks for less motion (Windows "Show animations" off). A player can switch them off (remembered per browser).
    let anim = true;
    try { anim = localStorage.getItem('casino-anim') !== 'off'; } catch (e) { }

    function fmt(n) {
        return Number(n || 0).toLocaleString(C.locale || undefined);
    }

    /** Text in the language of the player; {name} placeholders are filled from vars. */
    function t(key, vars) {
        let text = (C.text && C.text[key]) || key;
        Object.keys(vars || {}).forEach(k => { text = text.split('{' + k + '}').join(vars[k]); });
        return text;
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function toast(msg, type) {
        const el = $('<div class="cs-toast"></div>').addClass(type === 'good' ? 'cs-toast-good' : '').text(msg);
        $('body').append(el);
        setTimeout(() => el.addClass('show'), 10);
        setTimeout(() => el.removeClass('show'), 2600);
        setTimeout(() => el.remove(), 3000);
    }

    function handle(json) {
        if (!json) return;
        if (json.status === 'login') {
            window.location.href = json.redirect;
            return;
        }
        if (json.status !== 'good') {
            toast(json.msg || t('error'));
            return;
        }
        state = json.state;
        receivedAt = Date.now();
        if (!hold()) $('.js-balance').text(fmt(state.balance));
        renderChips();
        render(state);
        tickTimer();
    }

    function poll() {
        clearTimeout(pollTimer);
        fetch(C.stateUrl, { credentials: 'same-origin', cache: 'no-store' })
            .then(r => r.json())
            .then(handle)
            .catch(() => { })
            .finally(() => {
                pollTimer = setTimeout(poll, document.hidden ? C.pollMs * 3 : C.pollMs);
            });
    }

    let betQueue = Promise.resolve();

    /**
     * Sends an action. Bets clicked quickly one after the other are queued and all sent (none is lost);
     * other actions (hit, deal, spin, ...) are ignored while a request is still running, against double clicks.
     */
    function act(action, data) {
        if (action === 'bet' || action === 'buy') {
            betQueue = betQueue.then(() => send(action, data));
            return betQueue;
        }
        if (busy) return Promise.resolve();
        return send(action, data);
    }

    function send(action, data) {
        busy = true;
        $('body').addClass('cs-busy');
        const formData = new FormData();
        formData.set('action', action);
        Object.keys(data || {}).forEach(k => formData.set(k, data[k]));
        formData.set(csrf_token_name, csrf_hash);
        return fetch(C.actUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(r => r.json())
            .then(handle)
            .catch(() => toast(t('no_connection')))
            .finally(() => {
                busy = false;
                $('body').removeClass('cs-busy');
            });
    }

    /** Remaining seconds of the running timer, counted down locally between two polls. */
    function remaining() {
        if (!state || state.remaining === null || state.remaining === undefined) return null;
        return Math.max(0, state.remaining - Math.floor((Date.now() - receivedAt) / 1000));
    }

    function tickTimer() {
        const left = remaining();
        $('.js-timer').text(left === null ? '' : left + 's').toggleClass('cs-timer-low', left !== null && left <= 5);
    }

    /** Chip values from the table minimum up to the maximum, e.g. 10, 50, 100, 500. */
    function chipValues() {
        const values = [];
        [1, 5, 10, 25, 50, 100, 500, 1000].forEach(f => {
            const v = C.minBet * f;
            if (v <= C.maxBet && values.length < 6) values.push(v);
        });
        if (!values.length) values.push(C.minBet);
        return values;
    }

    function renderChips() {
        const box = $('.js-chips');
        if (!box.length) return;
        const values = chipValues();
        if (!values.includes(chip)) chip = values[0];
        const balance = state ? state.balance : 0;
        box.html(values.map(v =>
            `<button type="button" class="cs-chip cs-chip-${values.indexOf(v)} ${v === chip ? 'active' : ''}" data-chip="${v}" ${v > balance ? 'disabled' : ''}>${v >= 1000 ? (v / 1000) + 'k' : v}</button>`
        ).join(''));
    }

    $(document).on('click', '.js-chips [data-chip]', function () {
        chip = Number($(this).data('chip'));
        renderChips();
    });

    /**
     * The player's own last rounds at this table, kept on the page (newest first).
     * opts.hide: a round whose animation is still running (not shown yet).
     */
    function renderRecords(s, opts) {
        const body = $('.js-records');
        if (!body.length) return;
        opts = opts || {};
        const rows = (s.myRecent || []).filter(r => r.round !== opts.hide);
        if (!rows.length) {
            body.html(`<tr><td colspan="4" class="cs-muted">${esc(t('rec_none'))}</td></tr>`);
            $('.js-records-total').text('');
            return;
        }
        const signed = n => `<span class="${n > 0 ? 'cs-plus' : n < 0 ? 'cs-minus' : 'cs-muted'}">${n > 0 ? '+' : ''}${fmt(n)}</span>`;
        body.html(rows.map((r, i) => `<tr class="${i === 0 ? 'cs-rec-last' : ''}">
            <td class="text-nowrap">#${r.round}</td>
            <td>${resultHtml(r)}</td>
            <td class="text-end">${fmt(r.bet)}</td>
            <td class="text-end fw-bold">${signed(r.net)}</td>
        </tr>`).join(''));
        const total = rows.reduce((sum, r) => sum + r.net, 0);
        $('.js-records-total').html(esc(t('rec_total', { n: rows.length })) + ' ' + signed(total));
    }

    /** The result of a round in the records, the same way for every round (the server reads it from the log). */
    function resultHtml(r) {
        const x = r.result || {};
        const badge = `<span class="cs-outcome ${r.outcome}">${esc(t(r.outcome))}</span>`;
        const pip = n => window.DiceShaker ? DiceShaker.dieHTML(n) : `<b>${n}</b> `;
        if (x.dice) return `<span class="cs-pips">${x.dice.map(pip).join('')}</span> ${x.total}`;
        if (x.number !== undefined) return `<span class="wheel-num ${esc(x.color)}">${x.number}</span>`;
        if (x.player !== undefined) return `${badge} ${x.player} : ${x.bank}`;
        if (x.symbols) return x.symbols.map(s => `<svg class="sl-icon" viewBox="0 0 100 100" aria-hidden="true"><use href="#sl-${esc(s)}"/></svg>`).join('');
        if (x.hand) return `${badge} ${esc(t('hand_' + x.hand))}`;
        if (x.folded) return `${badge} ${esc(t('pk_fold'))}`;
        if (x.balls) return `${badge} ${esc(t('bg_balls', { n: x.balls }))}`;
        if (x.reason) return `${badge} ${esc(t('board_reason_' + x.reason))} · ${Math.ceil(x.plies / 2)} ${esc(t('board_moves_short'))}`;
        if (x.role) return `${badge} ${esc(t('ddz_' + x.role))} x${x.mult}${x.spring ? ' · ' + esc(t('ddz_spring')) : ''}`;
        return badge;
    }

    /**
     * Shows on a betting spot what the bet there wins (winnings on top of the stake back); nothing when win is 0.
     * upTo: the win can be smaller (e.g. a single number in dice pays 1x to 3x), so it reads "up to".
     */
    function winPill(el, win, upTo) {
        const box = $(el);
        let pill = box.children('.cs-winpill');
        if (!win) {
            pill.remove();
            return;
        }
        if (!pill.length) pill = $('<i class="cs-winpill"></i>').appendTo(box);
        pill.text(t(upTo ? 'can_win_max' : 'can_win', { amount: fmt(win) }));
    }

    /**
     * Wide screens in landscape (iPad, desktop): the table (felt) uses the whole width and exactly the screen height,
     * with the chips (dock) in view under it; players and records follow when scrolling down. Sets body.cs-fit.
     * allowed: optional check, e.g. only for the casino board. Returns the function that fits again.
     */
    function fitBoard(feltSelector, dockSelector, allowed) {
        const run = () => {
            const felt = document.querySelector(feltSelector);
            const dock = document.querySelector(dockSelector);
            if (!felt || !dock) return;
            const wide = window.innerWidth >= 900 && window.innerWidth > window.innerHeight;
            document.body.classList.toggle('cs-fit', wide && (!allowed || allowed()));
            felt.style.height = '';
            if (!document.body.classList.contains('cs-fit')) return;
            const top = felt.getBoundingClientRect().top + window.scrollY;
            const height = window.innerHeight - top - dock.offsetHeight - 16; // margins under the felt and the dock
            if (height < 420) {
                document.body.classList.remove('cs-fit'); // too low to fit: the normal page that scrolls
                return;
            }
            felt.style.height = height + 'px';
        };
        $(window).on('resize orientationchange load', () => requestAnimationFrame(run));
        run();
        return run;
    }

    /**
     * The big win on the table, as on the casino screens: a golden sunburst over the board, the amount counting up
     * and "Congratulations, you win ...". Goes away by itself after a few seconds, or on a click.
     */
    function winShow(board, amount) {
        const box = $(board).first();
        if (!box.length || !amount) return;
        if (!anim) {
            toast(t('win_congrats', { amount: fmt(amount) }), 'good');
            return;
        }
        box.find('.cs-winfx').remove();
        const fx = $(`<div class="cs-winfx" role="status"><i class="cs-winfx-rays"></i><div class="cs-winfx-text">${esc(t('win_congrats', { amount: fmt(amount) }))}</div><div class="cs-winfx-amount">0</div></div>`).appendTo(box);
        const num = fx.find('.cs-winfx-amount');
        const start = performance.now(), duration = Math.min(2200, 900 + String(amount).length * 250);
        (function step() {
            const p = Math.min(1, (performance.now() - start) / duration);
            num.text(fmt(Math.round(amount * (1 - Math.pow(1 - p, 3)))));
            if (p < 1 && fx[0].isConnected) requestAnimationFrame(step);
            else fx.addClass('done');
        })();
        if (typeof CasinoSound !== 'undefined') CasinoSound.chime([523, 659, 784, 1047, 1319], 0.11, 0.2);
        const close = () => fx.addClass('out').delay(450).queue(() => fx.remove());
        $(document).one('pointerdown', close); // the next click (often the next bet) clears it
        setTimeout(close, duration + 2600);
    }

    /**
     * The chip on a betting spot: the stake, with under it what comes back on a win (stake + win), as on the casino screens.
     * A chip that grows pops in big and settles, and the spot lights up green; the first render only remembers the amount.
     */
    function betChip(el, amount, win, upTo) {
        const box = $(el);
        const b = box.find('.js-bet');
        b.html(amount ? `${fmt(amount)}<i class="cs-chip-ret">${upTo ? '≤' : ''}${fmt(amount + win)}</i>` : '');
        const prev = box.data('chipAmount');
        box.data('chipAmount', amount);
        if (prev === undefined || amount <= prev || !anim) return;
        // restart the animations when bets land quickly after each other
        box.removeClass('cs-bet-flash');
        b.removeClass('cs-bet-pop');
        void b[0].offsetWidth;
        box.addClass('cs-bet-flash');
        b.addClass('cs-bet-pop');
    }

    /** True when leaving would cost the player something: open bets (dice, roulette) or a seat (blackjack). */
    function involved() {
        if (!state) return false;
        // solo blackjack: the seat is always yours; only a bet or a hand in play counts
        if (state.solo && state.game === 'blackjack') return (state.myBet || 0) > 0 && state.phase !== 'result';
        return Object.keys(state.myBets || {}).length > 0 || (state.mySeat || 0) > 0;
    }

    $(document).on('click', '.js-leave', function (e) {
        e.preventDefault();
        const leave = () => act('leave').finally(() => { window.casinoLeaving = true; window.location.href = C.lobbyUrl; });
        if (!involved()) {
            leave();
            return;
        }
        const ask = window.casinoConfirm ? casinoConfirm(t('confirm_leave'), t('confirm_leave_hint')) : Promise.resolve(window.confirm(t('confirm_leave')));
        ask.then(ok => { if (ok) leave(); });
    });

    // closing or reloading the tab by accident: the browser asks first (its own text)
    window.addEventListener('beforeunload', e => {
        if (!window.casinoLeaving && involved()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    function setAnim(on) {
        anim = on;
        try { localStorage.setItem('casino-anim', on ? 'on' : 'off'); } catch (e) { }
        document.body.classList.toggle('cs-no-anim', !on);
        $('.js-anim').attr('aria-pressed', String(on));
    }

    $(document).on('click', '.js-anim', () => setAnim(!anim));

    function start(renderFn) {
        render = renderFn;
        document.body.classList.toggle('cs-no-anim', !anim);
        $('.js-anim').attr('aria-pressed', String(anim));
        poll();
        setInterval(tickTimer, 250);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    }

    return {
        start: start,
        act: act,
        toast: toast,
        fmt: fmt,
        t: t,
        renderRecords: renderRecords,
        winPill: winPill,
        betChip: betChip,
        winShow: winShow,
        fitBoard: fitBoard,
        holdBalance: fn => { hold = fn; },
        animOn: () => anim,
        balance: () => state ? state.balance : 0,
        esc: esc,
        chip: () => chip,
        config: C,
    };
})();
