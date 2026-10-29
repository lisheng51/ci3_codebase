(function () {
    const S = Casino.config.slot; // reels, lines, paytable (from the server)
    const fmt = Casino.fmt;
    const esc = Casino.esc;
    const t = Casino.t;
    const ROWS = 3;
    const SPEED = 24;              // symbols per second at full speed
    const DECEL = 0.6;             // seconds to come to a stop
    const DDEC = SPEED * DECEL / 4.7; // distance while stopping (easeOutBack starts at about 4.7 x the average speed)
    const LINE_COLORS = ['#ffd34d', '#ff5a5a', '#4da3ff', '#5ad67d', '#c77dff'];
    const reels = [];
    let spinning = false;          // our spin is running (request and animation)
    let requested = false;         // waiting for the answer to our spin
    let shownSpin = null;          // id of the spin on the reels
    let raf = 0, lastNow = 0, lastTick = 0, landing = null;
    let jackpotShown = null, jackpotRaf = 0;
    let lines = S.lines.length; // how many lines the player plays (1-5), remembered per browser
    try { lines = Math.max(1, Math.min(S.lines.length, Number(localStorage.getItem('casino-slot-lines')) || S.lines.length)); } catch (e) { }

    const mod = (a, n) => ((a % n) + n) % n;
    const icon = sym => `<svg class="sl-icon" viewBox="0 0 100 100" aria-hidden="true"><use href="#sl-${sym}"/></svg>`;
    const tile = sym => `<div class="sl-sym"><svg viewBox="0 0 100 100" aria-hidden="true"><use href="#sl-${sym}"/></svg></div>`;
    const easeOutBack = x => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2); };

    function build() {
        const box = $('.js-reels');
        S.reels.forEach(strip => {
            const col = $('<div class="sl-reel"><div class="sl-strip"></div></div>');
            // three times the strip, so the window never runs out of symbols while it turns
            col.find('.sl-strip').html(strip.concat(strip, strip).map(tile).join(''));
            box.append(col);
            reels.push({ el: col[0], strip: col.find('.sl-strip')[0], n: strip.length, pos: Math.floor(Math.random() * strip.length), mode: 'stop' });
        });
        // the 5 win lines over the window (viewBox 300 x 300: three reels of 100, three rows of 100)
        $('.js-paylines').html(S.lines.map((line, i) => {
            const pts = [[0, 50 + 100 * line[0]]].concat(line.map((row, r) => [50 + 100 * r, 50 + 100 * row]), [[300, 50 + 100 * line[2]]]);
            const left = i < 3; // labels of the diagonals on the right, so they do not overlap
            const y = 50 + 100 * (left ? line[0] : line[2]) + 6;
            return `<polyline class="sl-line" data-line="${i}" points="${pts.map(p => p.join(',')).join(' ')}" style="--c:${LINE_COLORS[i]}"/>` +
                `<text class="sl-line-no" data-line="${i}" x="${left ? 12 : 288}" y="${y}" style="--c:${LINE_COLORS[i]}">${i + 1}</text>`;
        }).join(''));
        reels.forEach(place);
        window.addEventListener('resize', () => reels.forEach(place));
    }

    function place(r) {
        const h = r.el.clientHeight / ROWS;
        r.strip.style.transform = `translate3d(0, ${(-mod(r.pos, r.n) * h).toFixed(1)}px, 0)`;
    }

    function setStops(stops) {
        reels.forEach((r, i) => { r.pos = mod(stops[i] - 1, r.n); r.mode = 'stop'; r.el.classList.remove('spin'); place(r); });
    }

    /* ---------- animation: spin freely until the result is known, then stop reel by reel ---------- */
    function startSpinning() {
        reels.forEach(r => { r.mode = 'free'; });
        lastNow = performance.now();
        if (!raf) raf = requestAnimationFrame(step);
    }

    function step(now) {
        const dt = Math.min(0.05, (now - lastNow) / 1000);
        lastNow = now;
        reels.forEach(r => {
            if (r.mode === 'free') {
                r.pos += SPEED * dt;
                r.el.classList.add('spin');
            } else if (r.mode === 'plan') {
                const p = r.plan, tt = (now - p.t0) / 1000;
                if (tt < p.tConst) {
                    r.pos = p.p0 + SPEED * tt;
                    r.el.classList.add('spin');
                } else {
                    const x = Math.min(1, (tt - p.tConst) / DECEL);
                    r.pos = p.p0 + (p.D - DDEC) + DDEC * easeOutBack(x);
                    r.el.classList.toggle('spin', x < 0.25);
                    if (x >= 1) {
                        r.pos = p.p0 + p.D;
                        r.mode = 'stop';
                        r.el.classList.remove('spin');
                        CasinoSound.knock(0.6, 700);
                    }
                }
            }
            place(r);
        });
        if (reels.some(r => r.mode !== 'stop') && now - lastTick > 70) {
            lastTick = now;
            CasinoSound.knock(0.07, 2400);
        }
        if (reels.every(r => r.mode === 'stop')) {
            raf = 0;
            if (landing) { const done = landing; landing = null; done(); }
            return;
        }
        raf = requestAnimationFrame(step);
    }

    /** Lets the reels come to a stop on the given stops, from left to right. */
    function land(stops) {
        return new Promise(resolve => {
            const now = performance.now();
            let prev = 0;
            reels.forEach((r, i) => {
                const top = mod(stops[i] - 1, r.n);
                const minConst = Math.max(0.45 + 0.4 * i, prev + 0.3); // seconds at full speed from now
                let D = mod(top - r.pos, r.n);
                while (D - DDEC < SPEED * minConst) D += r.n;
                r.plan = { p0: r.pos, t0: now, tConst: (D - DDEC) / SPEED, D: D };
                r.mode = 'plan';
                prev = r.plan.tConst;
            });
            landing = resolve;
            if (!raf) { lastNow = now; raf = requestAnimationFrame(step); }
        });
    }

    /* ---------- a spin ---------- */
    function spin() {
        if (spinning) return;
        spinning = true;
        requested = true;
        clearWins();
        $('.js-spin').prop('disabled', true);
        $('.js-sl-msg').text(t('sl_spinning'));
        CasinoSound.ensure();
        if (Casino.animOn()) startSpinning();
        Casino.act('spin', { amount: Casino.chip(), lines: lines }).then(() => {
            if (!requested) return;
            // refused (e.g. not enough chips; Casino.act showed why): stop the reels without a result
            requested = false;
            const stop = Casino.animOn() ? land(reels.map(r => mod(Math.floor(r.pos) + 5, r.n) + 1)) : Promise.resolve();
            stop.then(finish);
            $('.js-sl-msg').text(t('sl_press_spin'));
        });
    }

    function play(sp) {
        const run = Casino.animOn() && raf ? land(sp.stops) : Promise.resolve(setStops(sp.stops));
        run.then(() => {
            showWin(sp);
            finish();
        });
    }

    function finish() {
        spinning = false;
        $('.js-spin').prop('disabled', false);
    }

    function clearWins() {
        $('.sl-line, .sl-line-no').removeClass('on');
        $('.sl-sym.hit').removeClass('hit');
        $('.js-sl-win').text('');
    }

    function symbolAt(reel, row) {
        const r = reels[reel];
        return $(r.strip.children[mod(Math.round(r.pos), r.n) + row]);
    }

    function showWin(sp) {
        clearWins();
        $('.js-balance').text(fmt(Casino.balance()));
        const total = sp.win + sp.jackpot;
        sp.wins.forEach(w => {
            $(`.sl-line[data-line=${w.line}], .sl-line-no[data-line=${w.line}]`).addClass('on');
            const line = S.lines[w.line];
            for (let r = 0; r < w.count; r++) symbolAt(r, line[r]).addClass('hit');
        });
        if (total <= 0) {
            $('.js-sl-msg').text(t('sl_no_win'));
            return;
        }
        countUp($('.js-sl-win'), total);
        $('.js-sl-msg').text(sp.wins.map(w => t('sl_line', { n: w.line + 1 }) + ' x' + w.mult).join(' · '));
        if (sp.jackpot > 0) {
            CasinoSound.chime([523, 659, 784, 1047, 784, 1047, 1319, 1568], 0.12, 0.22);
            celebrate(t('sl_jackpot_win', { amount: fmt(sp.jackpot) }), 80);
        } else if (total >= sp.stake * 10) {
            CasinoSound.chime([523, 659, 784, 1047, 1319], 0.1);
            celebrate(t('sl_big_win', { amount: fmt(total) }), 40);
        } else {
            CasinoSound.chime(total > sp.stake ? [659, 784, 1047] : [659, 784], 0.08);
        }
        if (total > sp.stake) Casino.toast(t('sl_win', { amount: fmt(total) }), 'good');
    }

    function countUp(el, value) {
        if (!Casino.animOn()) { el.text('+' + fmt(value)); return; }
        const start = performance.now(), dur = Math.min(1500, 300 + value * 4);
        const tick = now => {
            const x = Math.min(1, (now - start) / dur);
            el.text('+' + fmt(Math.round(value * x)));
            if (x < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /** Big win or jackpot: coins fall over the screen with the amount in the middle. */
    function celebrate(text, coins) {
        const box = $('<div class="sl-celebrate"><div class="sl-cel-text"></div></div>');
        box.find('.sl-cel-text').text(text);
        if (Casino.animOn()) {
            for (let i = 0; i < coins; i++) {
                $('<i class="sl-coin"></i>').css({
                    left: (Math.random() * 100) + '%',
                    animationDelay: (Math.random() * 1.2) + 's',
                    animationDuration: (1.6 + Math.random() * 1.4) + 's',
                }).appendTo(box);
            }
        }
        box.appendTo('body').on('click', () => box.remove());
        setTimeout(() => box.addClass('show'), 10);
        setTimeout(() => box.remove(), 3800);
    }

    function updateJackpot(value) {
        if (jackpotShown === null || !Casino.animOn()) {
            jackpotShown = value;
            $('.js-jackpot').text(fmt(value));
            return;
        }
        const from = jackpotShown, start = performance.now();
        jackpotShown = value;
        cancelAnimationFrame(jackpotRaf);
        const tick = now => {
            const x = Math.min(1, (now - start) / 700);
            $('.js-jackpot').text(fmt(Math.round(from + (value - from) * x)));
            if (x < 1) jackpotRaf = requestAnimationFrame(tick);
        };
        jackpotRaf = requestAnimationFrame(tick);
    }

    /** Marks the lines that play; with preview they light up for a moment. */
    function setLines(n, preview) {
        lines = Math.max(1, Math.min(S.lines.length, n));
        try { localStorage.setItem('casino-slot-lines', String(lines)); } catch (e) { }
        $('.js-sl-lines [data-lines]').each(function () { this.classList.toggle('active', Number(this.dataset.lines) === lines); });
        $('.sl-line-no').each(function () { this.classList.toggle('off', Number(this.dataset.line) >= lines); });
        updateTotal();
        if (preview) {
            clearWins();
            $('.sl-line').each(function () { this.classList.toggle('preview', Number(this.dataset.line) < lines); });
            setTimeout(() => $('.sl-line').removeClass('preview'), 1200);
        }
    }

    function updateTotal() {
        $('.js-sl-total').text(fmt(Casino.chip()) + ' x ' + lines + ' = ' + fmt(Casino.chip() * lines));
        // the best line (three sevens) at this line bet; the jackpot comes on top
        const top = Math.max(...Object.values(S.paytable));
        $('.js-sl-maxwin').text(t('can_win_max', { amount: fmt(Math.floor(top * Casino.chip())) }) + ' + ' + t('sl_jackpot_short'));
    }

    function render(s) {
        updateJackpot(s.jackpot);
        updateTotal();

        const sp = s.mySpin;
        if (sp && sp.spin !== shownSpin) {
            shownSpin = sp.spin;
            if (requested) {
                requested = false;
                play(sp);
            } else if (!spinning) {
                setStops(sp.stops); // the page was opened: show the last spin as it ended
            }
        }
        if (shownSpin === null && !spinning) $('.js-sl-msg').text(t('sl_press_spin'));

        $('.js-sl-feed').html(s.history.length ? s.history.map(h =>
            `<li class="sl-feed-row ${h.jackpot ? 'jp' : ''}"><span>${h.middle.map(icon).join('')}</span> <b>${esc(h.name)}</b> <span class="cs-plus">+${fmt(h.win)}</span>${h.jackpot ? ' <span class="sl-feed-jp">JACKPOT</span>' : ''}</li>`
        ).join('') : `<li class="cs-muted">${esc(t('nobody'))}</li>`);
        $('.js-players').html(s.players.map(p => `<li${p.me ? ' class="fw-bold"' : ''}><i class="fas fa-user-circle"></i> ${esc(p.name)}${p.me ? ' (' + esc(t('you')) + ')' : ''}</li>`).join(''));
        Casino.renderRecords(s, { hide: spinning ? shownSpin : null });
    }

    build();
    setLines(lines, false);
    $('.js-spin').on('click', spin);
    $(document).on('keydown', e => {
        if ((e.code === 'Space' || e.code === 'Enter') && !$(e.target).is('input, textarea, select, button, a')) {
            e.preventDefault();
            spin();
        }
    });
    $(document).on('click', '.js-chips [data-chip]', updateTotal);
    $(document).on('click', '.js-sl-lines [data-lines]', function () {
        if (spinning) return;
        setLines(Number($(this).data('lines')), true);
    });
    $('.js-sound').attr('aria-pressed', String(CasinoSound.isOn())).on('click', function () {
        const on = !CasinoSound.isOn();
        CasinoSound.setOn(on);
        $(this).attr('aria-pressed', String(on));
    });

    Casino.holdBalance(() => spinning);
    Casino.start(render);
})();
