/**
 * Dice shaker: a glass dome on a vibrating base with three 3D dice and simple physics.
 * The result comes from the server; the animation only acts it out and ends with the given faces up.
 * DiceShaker.run([a, b, c], {title, tapSkip, tapContinue, label}) returns a Promise that resolves when the dome closes.
 * Ported from the Sic Bo artifact (glass dome + quaternion dice + Web Audio clicks).
 */
window.DiceShaker = (function () {
    /* ---------- 2D dice (Chinese dice: the 1 and 4 are red) ---------- */
    const PIPS = { 1: [4], 2: [0, 8], 3: [0, 4, 8], 4: [0, 2, 6, 8], 5: [0, 2, 4, 6, 8], 6: [0, 2, 3, 5, 6, 8] };
    const dieClass = n => `pdie f${n}${n === 1 || n === 4 ? ' red' : ''}`;
    const pipsHTML = n => Array.from({ length: 9 }, (_, i) => `<i${PIPS[n].includes(i) ? ' class="on"' : ''}></i>`).join('');
    const dieHTML = n => `<span class="${dieClass(n)}" aria-hidden="true">${pipsHTML(n)}</span>`;

    /* ---------- overlay ---------- */
    let ov = null, machine = null, layerBack = null, layerDice = null, layerFront = null, readout = null, hint = null, neon = null;
    function ensureOverlay() {
        if (ov) return;
        ov = document.createElement('div');
        ov.className = 'shaker';
        ov.hidden = true;
        ov.innerHTML = '<div class="shaker-inner"><div class="neon" aria-hidden="true"></div>' +
            '<div class="machine"><div class="layer sk-back"></div><div class="layer sk-dice"></div><div class="layer front sk-front"></div></div>' +
            '<div class="readout" aria-live="polite"></div><div class="hint"></div></div>';
        document.body.appendChild(ov);
        neon = ov.querySelector('.neon');
        machine = ov.querySelector('.machine');
        layerBack = ov.querySelector('.sk-back');
        layerDice = ov.querySelector('.sk-dice');
        layerFront = ov.querySelector('.sk-front');
        readout = ov.querySelector('.readout');
        hint = ov.querySelector('.hint');
    }

    /* ---------- 3D: view 38° from above (CSS axes: x right, y down, z towards the viewer) ---------- */
    const TILT = 38 * Math.PI / 180, COS_T = Math.cos(TILT), SIN_T = Math.sin(TILT);
    const CAM = [[1, 0, 0], [0, COS_T, SIN_T], [0, -SIN_T, COS_T]];
    const LIGHT = (() => { const v = [-0.35, -0.8, 0.5], l = Math.hypot(...v); return v.map(c => c / l); })();
    const FACE_TF = { 1: 'rotateX(90deg)', 6: 'rotateX(-90deg)', 2: '', 5: 'rotateY(180deg)', 3: 'rotateY(90deg)', 4: 'rotateY(-90deg)' };
    const NORMALS = { 1: [0, -1, 0], 6: [0, 1, 0], 2: [0, 0, 1], 5: [0, 0, -1], 3: [1, 0, 0], 4: [-1, 0, 0] };
    const rand = (a, b) => a + Math.random() * (b - a); // animation only, never the result

    const Q = {
        axis(x, y, z, a) { const l = Math.hypot(x, y, z) || 1, s = Math.sin(a / 2); return [Math.cos(a / 2), x / l * s, y / l * s, z / l * s]; },
        mul(a, b) {
            return [a[0] * b[0] - a[1] * b[1] - a[2] * b[2] - a[3] * b[3],
                a[0] * b[1] + a[1] * b[0] + a[2] * b[3] - a[3] * b[2],
                a[0] * b[2] - a[1] * b[3] + a[2] * b[0] + a[3] * b[1],
                a[0] * b[3] + a[1] * b[2] - a[2] * b[1] + a[3] * b[0]];
        },
        norm(q) { const l = Math.hypot(...q); return q.map(v => v / l); },
        slerp(a, b, t) {
            let d = a[0] * b[0] + a[1] * b[1] + a[2] * b[2] + a[3] * b[3];
            if (d < 0) { b = b.map(v => -v); d = -d; }
            if (d > 0.9995) return Q.norm(a.map((v, i) => v + (b[i] - v) * t));
            const th = Math.acos(d), s = Math.sin(th), wa = Math.sin((1 - t) * th) / s, wb = Math.sin(t * th) / s;
            return a.map((v, i) => v * wa + b[i] * wb);
        },
        matrix([w, x, y, z]) {
            return [[1 - 2 * (y * y + z * z), 2 * (x * y - w * z), 2 * (x * z + w * y)],
                [2 * (x * y + w * z), 1 - 2 * (x * x + z * z), 2 * (y * z - w * x)],
                [2 * (x * z - w * y), 2 * (y * z + w * x), 1 - 2 * (x * x + y * y)]];
        },
    };
    const mulV = (M, v) => M.map(r => r[0] * v[0] + r[1] * v[1] + r[2] * v[2]);
    // orientation with face n on top
    const FACE_UP = {
        1: [1, 0, 0, 0], 6: Q.axis(1, 0, 0, Math.PI),
        2: Q.axis(1, 0, 0, Math.PI / 2), 5: Q.axis(1, 0, 0, -Math.PI / 2),
        3: Q.axis(0, 0, 1, -Math.PI / 2), 4: Q.axis(0, 0, 1, Math.PI / 2),
    };

    // machine size, scaled from a 300px wide design
    function geometry(W) {
        const k = W / 300;
        const R = 100 * k, s = 52 * k, h = s / 2, H = 2.3 * R;
        const Rc = R + 8 * k, domeH = 42 * k;
        const Rb = R + 18 * k, baseH = 40 * k;
        const topY = 26 * k + domeH;
        const cy = topY + H * COS_T;
        return {
            W, k, R, s, h, H, Rc, domeH, Rb, baseH, topY, cy, cx: W / 2,
            ry: R * SIN_T, ryc: Rc * SIN_T, ryb: Rb * SIN_T,
            Hm: cy + baseH + Rb * SIN_T + 6 * k,
            g: 2600 * k, kick: 900 * k, side: 230 * k,
        };
    }
    // resting places: front left, back middle, front right
    const slotsOf = G => [[-0.5 * G.R, 0.2 * G.R], [0, -0.36 * G.R], [0.5 * G.R, 0.2 * G.R]];

    function domeSVG(G) {
        const { W, Hm, cx, cy, R, ry, Rb, ryb, baseH, Rc, ryc, domeH, topY, k } = G;
        const f = n => n.toFixed(1);
        const L = cx - R, Rt = cx + R;
        const frontArc = (rx, r2, y) => `M${f(cx - rx)} ${f(y)} A${f(rx)} ${f(r2)} 0 0 0 ${f(cx + rx)} ${f(y)}`;
        const band = (rx, r2, y1, y2) => `${frontArc(rx, r2, y1)} L${f(cx + rx)} ${f(y2)} A${f(rx)} ${f(r2)} 0 0 1 ${f(cx - rx)} ${f(y2)}Z`;
        const backWall = `M${f(L)} ${f(topY)} A${f(R)} ${f(ry)} 0 0 1 ${f(Rt)} ${f(topY)} L${f(Rt)} ${f(cy)} A${f(R)} ${f(ry)} 0 0 0 ${f(L)} ${f(cy)}Z`;
        const frontWall = `${frontArc(R, ry, topY)} L${f(Rt)} ${f(cy)} A${f(R)} ${f(ry)} 0 0 1 ${f(L)} ${f(cy)}Z`;

        // fringe under the lid
        const yRim = x => topY + ryc * Math.sqrt(Math.max(0, 1 - ((x - cx) / Rc) ** 2));
        const N = 11, drip = 12 * k;
        let fringe = `M${f(cx - Rc)} ${f(topY)}`;
        for (let i = 0; i < N; i++) {
            const x2 = cx - Rc + 2 * Rc * (i + 1) / N, xm = x2 - Rc / N;
            const depth = drip * (0.55 + 0.45 * Math.sqrt(Math.max(0, 1 - ((xm - cx) / Rc) ** 2)));
            fringe += ` Q${f(xm)} ${f(yRim(xm) + depth * 2)} ${f(x2)} ${f(yRim(x2))}`;
        }
        fringe += ` A${f(Rc)} ${f(ryc)} 0 0 1 ${f(cx - Rc)} ${f(topY)}Z`;
        const dome = `M${f(cx - Rc)} ${f(topY)} A${f(Rc)} ${f(domeH)} 0 0 1 ${f(cx + Rc)} ${f(topY)} A${f(Rc)} ${f(ryc)} 0 0 1 ${f(cx - Rc)} ${f(topY)}Z`;
        const ledY = cy + baseH * 0.55;
        const svg = body => `<svg viewBox="0 0 ${f(W)} ${f(Hm)}" aria-hidden="true">${body}</svg>`;

        const back = svg(`
    <defs>
      <radialGradient id="sbFloor"><stop offset="0" stop-color="#d4ffe6"/><stop offset=".5" stop-color="#46e095" stop-opacity=".75"/><stop offset="1" stop-color="#0d5e3e" stop-opacity=".5"/></radialGradient>
      <linearGradient id="sbBack" x1="0" x2="1"><stop offset="0" stop-color="#fff" stop-opacity=".12"/><stop offset=".5" stop-color="#fff" stop-opacity=".02"/><stop offset="1" stop-color="#fff" stop-opacity=".12"/></linearGradient>
      <linearGradient id="sbUp" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#46e095" stop-opacity=".45"/><stop offset=".65" stop-color="#46e095" stop-opacity="0"/></linearGradient>
      <linearGradient id="sbBaseTop" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#55595c"/><stop offset="1" stop-color="#2b2d2f"/></linearGradient>
    </defs>
    <ellipse cx="${f(cx)}" cy="${f(cy)}" rx="${f(Rb)}" ry="${f(ryb)}" fill="url(#sbBaseTop)"/>
    <path d="${backWall}" fill="url(#sbBack)"/>
    <path d="${backWall}" fill="url(#sbUp)"/>
    <ellipse class="floor" cx="${f(cx)}" cy="${f(cy)}" rx="${f(R)}" ry="${f(ry)}" fill="url(#sbFloor)"/>`);

        const front = svg(`
    <defs>
      <linearGradient id="sbGlass" x1="0" x2="1">
        <stop offset="0" stop-color="#fff" stop-opacity=".28"/><stop offset=".07" stop-color="#fff" stop-opacity=".06"/>
        <stop offset=".22" stop-color="#fff" stop-opacity="0"/><stop offset=".78" stop-color="#fff" stop-opacity="0"/>
        <stop offset=".93" stop-color="#fff" stop-opacity=".08"/><stop offset="1" stop-color="#fff" stop-opacity=".26"/>
      </linearGradient>
      <linearGradient id="sbSide" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3b3e41"/><stop offset="1" stop-color="#0c0d0e"/></linearGradient>
      <linearGradient id="sbPost" x1="0" x2="1"><stop offset="0" stop-color="#2a2c2e"/><stop offset=".5" stop-color="#9a9fa3"/><stop offset="1" stop-color="#2a2c2e"/></linearGradient>
      <radialGradient id="sbFelt" cx=".45" cy=".25" r=".9"><stop offset="0" stop-color="#34433d"/><stop offset="1" stop-color="#0b100e"/></radialGradient>
      <linearGradient id="sbBar" x1="0" x2="1"><stop offset="0" stop-color="#2dff8f"/><stop offset=".5" stop-color="#eafff3"/><stop offset="1" stop-color="#2dff8f"/></linearGradient>
      <filter id="sbGlow" x="-30%" y="-80%" width="160%" height="260%"><feGaussianBlur stdDeviation="${f(3.2 * k)}"/></filter>
      <filter id="sbSoft" x="-50%" y="-5%" width="200%" height="110%"><feGaussianBlur stdDeviation="${f(1.4 * k)}"/></filter>
    </defs>
    <path d="${frontWall}" fill="url(#sbGlass)"/>
    <rect x="${f(cx - R * 0.62)}" y="${f(topY + ry * 0.5)}" width="${f(7 * k)}" height="${f(cy - topY)}" fill="#fff" opacity=".1" filter="url(#sbSoft)"/>
    <rect x="${f(cx + R * 0.45)}" y="${f(topY + ry * 0.6)}" width="${f(3.5 * k)}" height="${f(cy - topY - ry * 0.3)}" fill="#fff" opacity=".08" filter="url(#sbSoft)"/>
    <path d="${frontArc(R, ry, cy)}" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="${f(1.4 * k)}"/>
    <rect x="${f(L - 6 * k)}" y="${f(topY)}" width="${f(4.5 * k)}" height="${f(cy - topY)}" fill="url(#sbPost)"/>
    <rect x="${f(Rt + 1.5 * k)}" y="${f(topY)}" width="${f(4.5 * k)}" height="${f(cy - topY)}" fill="url(#sbPost)"/>
    <path d="${band(Rb, ryb, cy, cy + baseH)}" fill="url(#sbSide)"/>
    <path class="led" d="${frontArc(Rb, ryb, ledY)}" fill="none" stroke-width="${f(7 * k)}" filter="url(#sbGlow)" opacity=".9"/>
    <path class="led" d="${frontArc(Rb, ryb, ledY)}" fill="none" stroke-width="${f(2.6 * k)}"/>
    <path d="${fringe}" fill="#121815" stroke="#57e3a0" stroke-width="${f(1.6 * k)}" stroke-linejoin="round"/>
    <path d="${fringe}" fill="none" stroke="#57e3a0" stroke-width="${f(3 * k)}" filter="url(#sbGlow)" opacity=".55"/>
    <path d="${dome}" fill="url(#sbFelt)"/>
    <rect x="${f(cx - W * 0.22)}" y="${f(topY - domeH - 16 * k)}" width="${f(W * 0.44)}" height="${f(8 * k)}" rx="${f(4 * k)}" fill="url(#sbBar)" filter="url(#sbGlow)"/>
    <rect x="${f(cx - W * 0.22)}" y="${f(topY - domeH - 16 * k)}" width="${f(W * 0.44)}" height="${f(8 * k)}" rx="${f(4 * k)}" fill="url(#sbBar)"/>`);
        return [back, front];
    }

    const shaker = { dice: null, G: null };

    function makeDie() {
        const el = document.createElement('div'), cube = document.createElement('div'), shadow = document.createElement('div');
        el.className = 'die3d'; cube.className = 'cube'; shadow.className = 'dshadow';
        const faces = {};
        for (const n of [1, 2, 3, 4, 5, 6]) {
            const face = document.createElement('div');
            face.className = `face f${n}${n === 1 || n === 4 ? ' red' : ''}`;
            face.innerHTML = pipsHTML(n);
            cube.appendChild(face); faces[n] = face;
        }
        // three inner planes that fill the rounded corners
        for (const tf of ['none', 'rotateX(90deg)', 'rotateY(90deg)']) {
            const core = document.createElement('div');
            core.className = 'core'; core.style.transform = tf; cube.appendChild(core);
        }
        el.appendChild(cube);
        return { el, cube, shadow, faces, x: 0, z: 0, u: 0, vx: 0, vz: 0, vu: 0, w: [0, 0, 0], q: [1, 0, 0, 0] };
    }

    function layoutShaker(restValues) {
        const W = Math.round(Math.max(200, Math.min(300, window.innerWidth - 64, (window.innerHeight - 170) / 1.27)));
        const old = shaker.G, G = geometry(W);
        shaker.G = G;
        machine.style.width = W + 'px';
        machine.style.height = G.Hm + 'px';
        const [back, front] = domeSVG(G);
        layerBack.innerHTML = back;
        layerFront.innerHTML = front;
        layerDice.style.setProperty('--s', G.s + 'px');
        if (!shaker.dice) {
            shaker.dice = [0, 1, 2].map(() => { const d = makeDie(); layerDice.append(d.shadow, d.el); return d; });
            slotsOf(G).forEach(([x, z], i) => {
                const d = shaker.dice[i];
                Object.assign(d, { x, z, u: G.h, q: Q.mul(Q.axis(0, 1, 0, rand(-0.6, 0.6)), FACE_UP[restValues[i] || 1]) });
            });
        } else if (old && old.W !== W) {
            const r = G.k / old.k;
            shaker.dice.forEach(d => { d.x *= r; d.z *= r; d.u = G.h; });
        }
        for (const d of shaker.dice) {
            for (const n in d.faces) d.faces[n].style.transform = `${FACE_TF[n]} translateZ(${G.h}px)`;
        }
        renderShaker(false);
    }

    function renderShaker(flying) {
        const G = shaker.G;
        for (const d of shaker.dice) {
            const yp = -d.u * COS_T + d.z * SIN_T, zp = d.u * SIN_T + d.z * COS_T;
            const sc = 1 + 0.14 * zp / G.R; // closer to the viewer = bigger
            d.el.style.transform = `translate3d(${(G.cx + d.x - G.h).toFixed(2)}px, ${(G.cy + yp - G.h).toFixed(2)}px, 0) scale(${sc.toFixed(3)})`;
            d.el.style.zIndex = String(1000 + Math.round(zp));
            const M = Q.matrix(d.q);
            d.cube.style.transform = `rotateX(${-TILT}rad) matrix3d(${M[0][0]},${M[1][0]},${M[2][0]},0,${M[0][1]},${M[1][1]},${M[2][1]},0,${M[0][2]},${M[1][2]},${M[2][2]},0,0,0,0,1)`;
            // light per face: faces turned away are darker
            for (const n in d.faces) {
                const nc = mulV(CAM, mulV(M, NORMALS[n]));
                const lit = Math.max(0, nc[0] * LIGHT[0] + nc[1] * LIGHT[1] + nc[2] * LIGHT[2]);
                d.faces[n].style.setProperty('--shade', (0.42 * (1 - lit)).toFixed(3));
            }
            // floor shadow: the higher the die, the fainter and bigger
            const lift = Math.max(0, (d.u - G.h) / G.H);
            d.shadow.style.transform = `translate3d(${(G.cx + d.x - G.h).toFixed(2)}px, ${(G.cy + d.z * SIN_T - G.s * 0.22).toFixed(2)}px, 0) scale(${(1.15 + lift).toFixed(3)}, ${(0.9 + lift * 0.5).toFixed(3)})`;
            d.shadow.style.opacity = Math.max(0, 0.8 * (1 - lift * 1.5)).toFixed(3);
            // a little motion blur when flying fast
            const blur = flying ? Math.min(1.6, Math.hypot(d.vx, d.vz, d.vu) / G.kick * 1.5) : 0;
            if (Math.abs(blur - (d.blur || 0)) > 0.15 || (blur === 0 && d.blur)) {
                d.blur = blur;
                d.el.style.filter = blur > 0.2 ? `blur(${blur.toFixed(1)}px)` : '';
            }
        }
    }

    function physicsStep(dt, phase) {
        const G = shaker.G, D = shaker.dice, top = G.H - G.h, lim = G.R - G.h * 1.08;
        for (const d of D) {
            d.vu -= G.g * dt;
            if (phase === 'settle') { // slide to the resting place like a spring
                d.vx += ((d.sx - d.x) * 60 - d.vx * 11) * dt;
                d.vz += ((d.sz - d.z) * 60 - d.vz * 11) * dt;
            } else {
                const sp = Math.hypot(d.vx, d.vz), cap = G.side * 2.4;
                if (sp > cap) { d.vx *= cap / sp; d.vz *= cap / sp; }
            }
            d.x += d.vx * dt; d.z += d.vz * dt; d.u += d.vu * dt;

            if (d.u < G.h) { // hits the base
                const hit = -d.vu;
                d.u = G.h;
                if (phase === 'shake') { // the base vibrates: kick the die up with a random spin
                    d.vu = G.kick * rand(0.5, 1);
                    d.vx += rand(-1, 1) * G.side;
                    d.vz += rand(-1, 1) * G.side;
                    const spin = rand(10, 22), a = [rand(-1, 1), rand(-1, 1), rand(-1, 1)], l = Math.hypot(...a) || 1;
                    d.w = a.map(v => v / l * spin);
                    knock(hit / G.kick * 0.45, 1700);
                } else {
                    d.vu = hit > G.kick * 0.12 ? hit * 0.3 : 0;
                    knock(hit / G.kick * 0.4, 1500);
                }
            }
            if (d.u > top) { // hits the lid
                d.u = top;
                if (d.vu > 0) { knock(d.vu / G.kick * 0.3, 3200); d.vu *= -0.4; }
            }
            const r = Math.hypot(d.x, d.z);
            if (r > lim) { // hits the glass
                const nx = d.x / r, nz = d.z / r, vn = d.vx * nx + d.vz * nz;
                d.x = nx * lim; d.z = nz * lim;
                if (vn > 0) { d.vx -= 1.6 * vn * nx; d.vz -= 1.6 * vn * nz; knock(vn / G.kick * 0.6, 3600); }
            }
            if (phase === 'shake') {
                const wl = Math.hypot(...d.w);
                if (wl > 0) d.q = Q.norm(Q.mul(Q.axis(d.w[0], d.w[1], d.w[2], wl * dt), d.q));
            }
        }
        // dice hit each other (as spheres)
        const min = G.s * 0.98;
        for (let i = 0; i < D.length; i++) for (let j = i + 1; j < D.length; j++) {
            const a = D[i], b = D[j];
            const dx = b.x - a.x, du = b.u - a.u, dz = b.z - a.z, dist = Math.hypot(dx, du, dz);
            if (dist >= min || dist === 0) continue;
            const nx = dx / dist, nu = du / dist, nz = dz / dist, push = (min - dist) / 2;
            a.x -= nx * push; a.u -= nu * push; a.z -= nz * push;
            b.x += nx * push; b.u += nu * push; b.z += nz * push;
            const vrel = (b.vx - a.vx) * nx + (b.vu - a.vu) * nu + (b.vz - a.vz) * nz;
            if (vrel < 0) {
                const J = -0.85 * vrel;
                a.vx -= J * nx; a.vu -= J * nu; a.vz -= J * nz;
                b.vx += J * nx; b.vu += J * nu; b.vz += J * nz;
                knock(-vrel / G.kick * 0.5, 2800);
            }
        }
    }

    let running = false;
    let lastFinal = [3, 5, 6];
    /** Opens the shaker and resolves when it is closed again. opts.animate === false skips it (the player switched animations off). */
    function run(final, opts) {
        opts = opts || {};
        if (running || opts.animate === false) return Promise.resolve();
        ensureOverlay();
        running = true;
        return new Promise(resolve => {
            layoutShaker(lastFinal);
            neon.textContent = opts.title || '骰宝';
            readout.innerHTML = '';
            hint.textContent = opts.tapSkip || '';
            ov.hidden = false;
            requestAnimationFrame(() => ov.classList.add('open'));

            const T0 = 0.3, T1 = T0 + 1.4, T2 = 0.95, STEP = 1 / 240; // pause > shake > settle
            let t = 0, acc = 0, last = performance.now(), phase = 'idle', closed = false, timer = 0;

            const close = () => {
                if (closed) return;
                closed = true; clearTimeout(timer); ov.onclick = null;
                ov.classList.remove('open');
                hum(false);
                lastFinal = final;
                setTimeout(() => { ov.hidden = true; running = false; resolve(); }, 200);
            };
            ov.onclick = () => {
                if (phase === 'landed') close();
                else t = Math.max(t, T1); // tap to skip straight to the landing
            };

            const beginSettle = () => {
                const S = slotsOf(shaker.G);
                // resting places by the current left-right position: final[0], final[1], final[2] from left to right
                [...shaker.dice].sort((a, b) => a.x - b.x).forEach((d, i) => {
                    d.sx = S[i][0]; d.sz = S[i][1];
                    d.qStart = d.q;
                    d.qTarget = Q.mul(Q.axis(0, 1, 0, rand(-0.6, 0.6)), FACE_UP[final[i]]);
                    d.w = [0, 0, 0];
                });
            };

            const frame = now => {
                if (closed) return;
                const dt = Math.min(0.05, (now - last) / 1000);
                last = now; t += dt;
                if (phase === 'idle' && t >= T0) { phase = 'shake'; machine.classList.add('shaking'); hum(true); }
                if (phase === 'shake' && t >= T1) { phase = 'settle'; machine.classList.remove('shaking'); hum(false); beginSettle(); }
                if (phase === 'shake' || phase === 'settle') {
                    acc += dt;
                    while (acc >= STEP) { physicsStep(STEP, phase); acc -= STEP; }
                }
                if (phase === 'settle') {
                    const p = Math.min(1, (t - T1) / (T2 * 0.85)), e = 1 - Math.pow(1 - p, 3);
                    for (const d of shaker.dice) d.q = Q.slerp(d.qStart, d.qTarget, e);
                    if (t >= T1 + T2) {
                        phase = 'landed';
                        for (const d of shaker.dice) { d.u = shaker.G.h; d.vx = d.vz = d.vu = 0; d.q = d.qTarget; }
                        const sum = final[0] + final[1] + final[2];
                        readout.innerHTML = final.map(dieHTML).join('') + `<span class="eq">= ${sum}</span>` +
                            (opts.label ? `<span class="cat ${opts.labelClass || ''}">${opts.label}</span>` : '');
                        hint.textContent = opts.tapContinue || '';
                        timer = setTimeout(close, 1400);
                    }
                }
                renderShaker(phase === 'shake');
                if (phase !== 'landed') requestAnimationFrame(frame);
            };
            requestAnimationFrame(frame);
        });
    }

    const knock = (strength, freq) => CasinoSound.knock(strength, freq);
    const hum = on => CasinoSound.hum(on);

    window.addEventListener('resize', () => { if (running && shaker.G) layoutShaker(lastFinal); });

    return {
        run: run,
        dieHTML: dieHTML,
        isRunning: () => running,
        soundOn: () => CasinoSound.isOn(),
        setSound: on => CasinoSound.setOn(on),
    };
})();
