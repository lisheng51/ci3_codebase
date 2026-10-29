/**
 * Full-screen European roulette wheel. The winning number comes from the server; the animation only acts it out:
 * the rotor spins, the ball runs the other way on the track, slows down, drops, rattles over the frets
 * and ends exactly in the pocket of the given number.
 * RouletteWheel.spin(number, {title, tapSkip, tapContinue, label, color}) returns a Promise that resolves when it is closed.
 */
window.RouletteWheel = (function () {
    const ORDER = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    const RED = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
    const N = ORDER.length;
    const SEG = Math.PI * 2 / N;
    const TAU = Math.PI * 2;
    const colorOf = n => n === 0 ? 'green' : (RED.includes(n) ? 'red' : 'black');
    const FILL = { red: '#b3121f', black: '#141414', green: '#0b7a3b' };
    const rand = (a, b) => a + Math.random() * (b - a); // animation only, never the result
    const clamp01 = x => Math.max(0, Math.min(1, x));
    const smooth = x => { x = clamp01(x); return x * x * (3 - 2 * x); };

    let ov, canvas, ctx, readout, hint, title;
    let S = 0, R = 0, dpr = 1, rotorImg = null, bowlImg = null;
    let running = false;
    let wheelAngle = rand(0, TAU); // the rotor keeps its position between spins
    let live, liveNum, liveLabel, board;
    const cam = { z: 1, fx: 0, fy: 0 }; // camera: zoom and the point it looks at (wheel coordinates)

    function ensureOverlay() {
        if (ov) return;
        ov = document.createElement('div');
        ov.className = 'wheel-ov';
        ov.hidden = true;
        ov.innerHTML = '<div class="wheel-inner"><div class="neon wheel-title" aria-hidden="true"></div>' +
            '<div class="wheel-live"><span class="wheel-live-label"></span><span class="wheel-num green">0</span></div>' +
            '<div class="wheel-stage"><canvas class="wheel-canvas"></canvas><div class="wheel-board" aria-hidden="true"></div></div>' +
            '<div class="wheel-readout" aria-live="polite"></div><div class="hint"></div></div>';
        document.body.appendChild(ov);
        canvas = ov.querySelector('canvas');
        ctx = canvas.getContext('2d');
        readout = ov.querySelector('.wheel-readout');
        hint = ov.querySelector('.hint');
        title = ov.querySelector('.wheel-title');
        live = ov.querySelector('.wheel-live');
        liveNum = live.querySelector('.wheel-num');
        liveLabel = live.querySelector('.wheel-live-label');
        board = ov.querySelector('.wheel-board');
    }

    function layout() {
        const wide = window.innerWidth >= 700; // the score board stands next to the wheel, otherwise below it
        S = Math.round(Math.max(240, Math.min(window.innerWidth - 24 - (wide ? 120 : 0), window.innerHeight - (wide ? 250 : 300), 640)));
        R = S / 2;
        dpr = Math.min(2, window.devicePixelRatio || 1);
        canvas.width = S * dpr;
        canvas.height = S * dpr;
        canvas.style.width = S + 'px';
        canvas.style.height = S + 'px';
        board.style.maxHeight = wide ? S + 'px' : '';
        // the rotor is drawn sharper than the screen, so the numbers stay crisp when the camera zooms in
        rotorImg = drawRotor(Math.min(dpr * 2.4, 3600 / S));
        bowlImg = drawBowl(Math.min(dpr * 1.6, 3000 / S));
    }

    function offscreen(res) {
        const c = document.createElement('canvas');
        c.width = Math.round(S * res);
        c.height = Math.round(S * res);
        const g = c.getContext('2d');
        g.scale(res, res);
        g.translate(R, R);
        return [c, g];
    }

    /** The number of the pocket at angle a (wheel coordinates) for the current rotor position. */
    function pocketAt(a) {
        const rel = a - wheelAngle + Math.PI / 2;
        const i = Math.round(rel / SEG);
        return ORDER[((i % N) + N) % N];
    }

    /** Casino score board: newest number on top, red numbers right, black left, zero in the middle. */
    function renderBoard(numbers, fresh) {
        board.innerHTML = numbers.slice(0, 14).map((n, i) =>
            `<div class="wb-row ${colorOf(n)}${i === 0 && fresh ? ' fresh' : ''}"><span class="wheel-num ${colorOf(n)}">${n}</span></div>`
        ).join('');
    }

    /* ---------- the parts that do not turn: wooden rim, ball track, deflectors ---------- */
    function drawBowl(res) {
        const [c, g] = offscreen(res);
        // wooden rim
        let gr = g.createRadialGradient(0, 0, R * 0.86, 0, 0, R);
        gr.addColorStop(0, '#2a1407');
        gr.addColorStop(0.45, '#7a4220');
        gr.addColorStop(0.8, '#4a2410');
        gr.addColorStop(1, '#1b0c04');
        g.fillStyle = gr;
        g.beginPath(); g.arc(0, 0, R * 0.995, 0, TAU); g.fill();
        // wood grain
        g.save();
        g.globalAlpha = 0.12;
        g.strokeStyle = '#000';
        for (let i = 0; i < 90; i++) {
            const a = i / 90 * TAU;
            g.beginPath();
            g.arc(0, 0, R * rand(0.935, 0.99), a, a + rand(0.05, 0.2));
            g.lineWidth = rand(0.4, 1.4);
            g.stroke();
        }
        g.restore();
        // gold ring
        g.strokeStyle = gold(g, R * 0.92);
        g.lineWidth = R * 0.018;
        g.beginPath(); g.arc(0, 0, R * 0.925, 0, TAU); g.stroke();
        // ball track: dark polished wood, a little lighter at the outside
        gr = g.createRadialGradient(0, 0, R * 0.74, 0, 0, R * 0.915);
        gr.addColorStop(0, '#1a0f08');
        gr.addColorStop(0.55, '#3b2414');
        gr.addColorStop(0.9, '#5a371e');
        gr.addColorStop(1, '#24150a');
        g.fillStyle = gr;
        g.beginPath(); g.arc(0, 0, R * 0.915, 0, TAU); g.arc(0, 0, R * 0.735, 0, TAU, true); g.fill();
        // diamond deflectors on the slope
        for (let i = 0; i < 8; i++) {
            const a = i / 8 * TAU + SEG / 2, r = R * 0.785;
            g.save();
            g.rotate(a);
            g.translate(r, 0);
            const w = R * 0.022, h = (i % 2 ? R * 0.05 : R * 0.035);
            g.beginPath(); g.moveTo(0, -h); g.lineTo(w, 0); g.lineTo(0, h); g.lineTo(-w, 0); g.closePath();
            g.fillStyle = gold(g, w);
            g.fill();
            g.restore();
        }
        return c;
    }

    function gold(g, r) {
        const gr = g.createLinearGradient(-r, -r, r, r);
        gr.addColorStop(0, '#f7e08a');
        gr.addColorStop(0.35, '#c99a2e');
        gr.addColorStop(0.6, '#fff1b8');
        gr.addColorStop(1, '#8a6414');
        return gr;
    }

    /* ---------- the rotor: numbers, pockets, frets, cone and turret (drawn once, rotated every frame) ---------- */
    function drawRotor(res) {
        const [c, g] = offscreen(res);
        const rNumOut = R * 0.725, rNumIn = R * 0.6, rPocIn = R * 0.47;
        for (let i = 0; i < N; i++) {
            const n = ORDER[i], a0 = -Math.PI / 2 + i * SEG - SEG / 2, a1 = a0 + SEG;
            // number band
            g.beginPath(); g.arc(0, 0, rNumOut, a0, a1); g.arc(0, 0, rNumIn, a1, a0, true); g.closePath();
            g.fillStyle = FILL[colorOf(n)];
            g.fill();
            // pocket: same color, darker towards the bottom
            const gr = g.createRadialGradient(0, 0, rPocIn, 0, 0, rNumIn);
            gr.addColorStop(0, shade(FILL[colorOf(n)], -0.55));
            gr.addColorStop(1, shade(FILL[colorOf(n)], -0.15));
            g.beginPath(); g.arc(0, 0, rNumIn, a0, a1); g.arc(0, 0, rPocIn, a1, a0, true); g.closePath();
            g.fillStyle = gr;
            g.fill();
            // number, reading outwards
            const am = a0 + SEG / 2, rt = (rNumOut + rNumIn) / 2;
            g.save();
            g.rotate(am);
            g.translate(rt, 0);
            g.rotate(Math.PI / 2);
            g.fillStyle = '#fff';
            g.font = `700 ${Math.round(R * 0.068)}px "Barlow Condensed", "Arial Narrow", sans-serif`;
            g.textAlign = 'center';
            g.textBaseline = 'middle';
            g.fillText(String(n), 0, 0);
            g.restore();
        }
        // frets between the pockets
        g.strokeStyle = gold(g, R * 0.6);
        g.lineWidth = Math.max(1, R * 0.008);
        for (let i = 0; i < N; i++) {
            const a = -Math.PI / 2 + i * SEG - SEG / 2;
            g.beginPath();
            g.moveTo(Math.cos(a) * rPocIn, Math.sin(a) * rPocIn);
            g.lineTo(Math.cos(a) * rNumOut, Math.sin(a) * rNumOut);
            g.stroke();
        }
        g.lineWidth = Math.max(1, R * 0.012);
        [rNumOut, rNumIn, rPocIn].forEach(r => { g.beginPath(); g.arc(0, 0, r, 0, TAU); g.stroke(); });

        // cone: polished wood with gold ring
        let gr = g.createRadialGradient(-R * 0.08, -R * 0.1, R * 0.05, 0, 0, rPocIn);
        gr.addColorStop(0, '#9b5b2c');
        gr.addColorStop(0.6, '#5c3015');
        gr.addColorStop(1, '#2c1608');
        g.fillStyle = gr;
        g.beginPath(); g.arc(0, 0, rPocIn - g.lineWidth / 2, 0, TAU); g.fill();
        g.strokeStyle = gold(g, R * 0.3);
        g.lineWidth = R * 0.012;
        g.beginPath(); g.arc(0, 0, R * 0.33, 0, TAU); g.stroke();
        // turret: four spokes with knobs
        for (let i = 0; i < 4; i++) {
            g.save();
            g.rotate(i * Math.PI / 2);
            g.fillStyle = gold(g, R * 0.3);
            g.beginPath();
            g.moveTo(0, -R * 0.025); g.lineTo(R * 0.27, -R * 0.012); g.lineTo(R * 0.27, R * 0.012); g.lineTo(0, R * 0.025);
            g.closePath(); g.fill();
            const kn = g.createRadialGradient(R * 0.28 - R * 0.01, -R * 0.01, 1, R * 0.28, 0, R * 0.035);
            kn.addColorStop(0, '#fff6cf'); kn.addColorStop(1, '#a37a1c');
            g.fillStyle = kn;
            g.beginPath(); g.arc(R * 0.28, 0, R * 0.032, 0, TAU); g.fill();
            g.restore();
        }
        gr = g.createRadialGradient(-R * 0.03, -R * 0.03, 1, 0, 0, R * 0.09);
        gr.addColorStop(0, '#fff8d8'); gr.addColorStop(0.5, '#d9ac3c'); gr.addColorStop(1, '#7a5610');
        g.fillStyle = gr;
        g.beginPath(); g.arc(0, 0, R * 0.085, 0, TAU); g.fill();
        return c;
    }

    function shade(hex, amount) {
        const v = parseInt(hex.slice(1), 16);
        const f = c => Math.round(Math.max(0, Math.min(255, c + c * amount)));
        return `rgb(${f(v >> 16)}, ${f((v >> 8) & 255)}, ${f(v & 255)})`;
    }

    /* ---------- one frame ---------- */
    function draw(ball, highlight) {
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, S, S);
        ctx.translate(R, R);
        ctx.scale(cam.z, cam.z);
        ctx.translate(-cam.fx, -cam.fy);

        ctx.drawImage(bowlImg, -R, -R, S, S);
        ctx.save();
        ctx.rotate(wheelAngle);
        ctx.drawImage(rotorImg, -R, -R, S, S);
        if (highlight !== null) {
            // glowing winning pocket
            const i = ORDER.indexOf(highlight), a0 = -Math.PI / 2 + i * SEG - SEG / 2;
            ctx.save();
            ctx.globalCompositeOperation = 'lighter';
            ctx.globalAlpha = 0.35 + 0.25 * Math.sin(performance.now() / 160);
            ctx.fillStyle = '#ffd86b';
            ctx.beginPath(); ctx.arc(0, 0, R * 0.725, a0, a0 + SEG); ctx.arc(0, 0, R * 0.47, a0 + SEG, a0, true); ctx.closePath();
            ctx.fill();
            ctx.restore();
        }
        ctx.restore();

        // sheen of the varnish (does not turn with the rotor)
        const sh = ctx.createRadialGradient(-R * 0.35, -R * 0.45, R * 0.05, -R * 0.2, -R * 0.25, R * 0.95);
        sh.addColorStop(0, 'rgba(255,255,255,.16)');
        sh.addColorStop(0.5, 'rgba(255,255,255,.04)');
        sh.addColorStop(1, 'rgba(0,0,0,.18)');
        ctx.fillStyle = sh;
        ctx.beginPath(); ctx.arc(0, 0, R * 0.92, 0, TAU); ctx.fill();

        if (ball) {
            // motion trail when it runs fast
            for (let k = ball.trail.length - 1; k >= 0; k--) {
                const p = ball.trail[k];
                ctx.globalAlpha = 0.18 * (1 - k / ball.trail.length);
                ctx.fillStyle = '#fff';
                ctx.beginPath(); ctx.arc(p[0], p[1], R * 0.03, 0, TAU); ctx.fill();
            }
            ctx.globalAlpha = 1;
            const bx = Math.cos(ball.a) * ball.r, by = Math.sin(ball.a) * ball.r, br = R * 0.032;
            // shadow towards the centre-bottom
            ctx.fillStyle = 'rgba(0,0,0,.45)';
            ctx.beginPath(); ctx.ellipse(bx + br * 0.35, by + br * 0.45, br * 1.05, br * 0.8, 0, 0, TAU); ctx.fill();
            const bg = ctx.createRadialGradient(bx - br * 0.4, by - br * 0.45, br * 0.1, bx, by, br);
            bg.addColorStop(0, '#ffffff');
            bg.addColorStop(0.55, '#e9e9ea');
            bg.addColorStop(1, '#8d9196');
            ctx.fillStyle = bg;
            ctx.beginPath(); ctx.arc(bx, by, br, 0, TAU); ctx.fill();
        }
    }

    /* ---------- the spin ---------- */
    function spin(number, opts) {
        opts = opts || {};
        if (running) return Promise.resolve();
        ensureOverlay();
        running = true;
        layout();
        title.textContent = opts.title || 'ROULETTE';
        readout.innerHTML = '';
        readout.className = 'wheel-readout';
        hint.textContent = opts.tapSkip || '';
        liveLabel.textContent = opts.liveLabel || '';
        live.className = 'wheel-live';
        renderBoard(opts.history || [], false);
        cam.z = 1; cam.fx = 0; cam.fy = 0;
        ov.hidden = false;
        requestAnimationFrame(() => ov.classList.add('open'));
        CasinoSound.ensure();

        const D = opts.animate === false ? 0.01 : 8; // seconds until the ball rests (animations off: straight to the result)
        const W0 = rand(1.1, 1.5), WA = 0.05;     // rotor speed (rad/s) and its slow down
        const target = ORDER.indexOf(number) * SEG - Math.PI / 2; // pocket angle in the rotor frame
        const rel0 = rand(0, TAU);
        const turns = Math.round(rand(9, 11));
        const delta = (((target - rel0) % TAU) + TAU) % TAU - TAU * turns; // negative: the ball runs against the rotor
        const ease = x => 1 - Math.pow(1 - x, 2.4);
        const bounces = [rand(-0.5, 0.5), rand(-0.4, 0.4), rand(-0.3, 0.3)];
        const startAngle = wheelAngle;
        const rTrack = R * 0.855, rSlope = R * 0.75, rPocket = R * 0.535;

        return new Promise(resolve => {
            let t = 0, last = performance.now(), closed = false, landed = false, landTimer = 0, lastHop = -1;
            let shown = null, shownAt = 0;
            const ZMAX = 2.4; // close-up of the camera while the ball drops
            const trail = [];

            const close = () => {
                if (closed) return;
                closed = true;
                clearTimeout(landTimer);
                ov.onclick = null;
                CasinoSound.roll(0);
                ov.classList.remove('open');
                setTimeout(() => { ov.hidden = true; running = false; resolve(); }, 250);
            };
            ov.onclick = () => {
                if (landed) close();
                else t = Math.max(t, D); // tap to skip straight to the result
            };

            const frame = now => {
                if (closed) return;
                const dt = Math.min(0.05, (now - last) / 1000);
                last = now;
                t += dt;
                wheelAngle = startAngle + W0 * t - 0.5 * WA * t * t;
                const x = clamp01(t / D);

                // angle of the ball relative to the rotor, plus a rattle while it drops into the pockets
                let rel = rel0 + delta * ease(x);
                const win = x > 0.55 && x < 0.9 ? Math.sin(Math.PI * (x - 0.55) / 0.35) : 0;
                rel += win * (bounces[0] * Math.sin(x * 40) + bounces[1] * Math.sin(x * 71) + bounces[2] * Math.sin(x * 113));

                // distance from the centre: track > slope (deflectors) > hops over the frets > pocket
                let r;
                if (x < 0.5) {
                    r = rTrack + R * 0.006 * Math.sin(t * 9);
                } else if (x < 0.62) {
                    r = rTrack + (rSlope - rTrack) * smooth((x - 0.5) / 0.12);
                } else if (x < 0.9) {
                    const s = (x - 0.62) / 0.28;
                    const hops = 5;
                    const hop = Math.abs(Math.sin(Math.PI * hops * s)) * (1 - s);
                    r = rSlope + (rPocket - rSlope) * smooth(s) + R * 0.07 * hop;
                    const h = Math.floor(hops * s);
                    if (h !== lastHop) { lastHop = h; CasinoSound.knock(0.55 * (1 - s) + 0.1, 2600); }
                } else {
                    r = rPocket;
                }

                const a = wheelAngle + rel;
                const ball = { a, r, trail };

                // live display: the number of the pocket under the ball
                const under = pocketAt(a);
                if (under !== shown && (now - shownAt > 45 || x >= 0.6)) {
                    shown = under;
                    shownAt = now;
                    liveNum.textContent = under;
                    liveNum.className = 'wheel-num ' + colorOf(under);
                    if (x > 0.6 && x < 1) CasinoSound.knock(0.12, 4200);
                }

                // camera: zoom in and follow the ball when it drops, hold on the pocket, then zoom out to the whole wheel
                const tl = landed ? t - D : 0;
                let zt = 1 + (ZMAX - 1) * smooth((x - 0.5) / 0.28);
                if (landed) zt = 1 + (ZMAX - 1) * (1 - smooth((tl - 1.5) / 0.8));
                const follow = (zt - 1) / (ZMAX - 1) * 0.92;
                const k = Math.min(1, dt * 4), kf = Math.min(1, dt * 7);
                cam.z += (zt - cam.z) * k;
                cam.fx += (Math.cos(a) * r * follow - cam.fx) * kf;
                cam.fy += (Math.sin(a) * r * follow - cam.fy) * kf;
                // rolling noise follows the speed of the ball over the track
                const speed = Math.abs(W0 + delta * 2.4 * Math.pow(1 - x, 1.4) / D) / 30;
                CasinoSound.roll(x < 0.62 ? 0.4 + 0.6 * speed : (x < 0.9 ? 0.25 * (1 - (x - 0.62) / 0.28) : 0), speed);

                draw(ball, landed ? number : null);
                trail.unshift([Math.cos(a) * r, Math.sin(a) * r]);
                if (trail.length > Math.round(6 * speed)) trail.length = Math.round(6 * speed);

                if (!landed && x >= 1) {
                    landed = true;
                    CasinoSound.roll(0);
                    CasinoSound.knock(0.5, 1800);
                    readout.className = 'wheel-readout show ' + colorOf(number);
                    readout.innerHTML = `<span class="wheel-num ${colorOf(number)}">${number}</span>` + (opts.label ? `<span class="wheel-label">${opts.label}</span>` : '');
                    hint.textContent = opts.tapContinue || '';
                    live.className = 'wheel-live final';
                    liveNum.textContent = number;
                    liveNum.className = 'wheel-num ' + colorOf(number);
                    renderBoard([number].concat(opts.history || []), true);
                    landTimer = setTimeout(close, 3600);
                }
                requestAnimationFrame(frame);
            };
            requestAnimationFrame(frame);
        });
    }

    window.addEventListener('resize', () => { if (running) layout(); });

    return {
        spin: spin,
        isRunning: () => running,
        colorOf: colorOf,
        ORDER: ORDER,
    };
})();
