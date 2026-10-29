/**
 * Casino sounds made with Web Audio (no sound files): short clicks (dice, ball over the frets),
 * the hum of the dice shaker and the rolling noise of the roulette ball. On/off is remembered per browser.
 */
window.CasinoSound = (function () {
    let on = true;
    try { on = localStorage.getItem('casino-sound') !== 'off'; } catch (e) { }
    let audio = null;
    const rand = (a, b) => a + Math.random() * (b - a);

    function ensure() {
        if (!on) return;
        try {
            if (!audio) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                const ctx = new AC();
                // a short click: noise with a fast decay
                const len = Math.floor(ctx.sampleRate * 0.05), buf = ctx.createBuffer(1, len, ctx.sampleRate), ch = buf.getChannelData(0);
                for (let i = 0; i < len; i++) ch[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 4);
                // one second of plain noise, looped for the rolling ball
                const nlen = ctx.sampleRate, noise = ctx.createBuffer(1, nlen, ctx.sampleRate), nch = noise.getChannelData(0);
                for (let i = 0; i < nlen; i++) nch[i] = Math.random() * 2 - 1;
                const out = ctx.createGain();
                out.gain.value = 0.9; out.connect(ctx.destination);
                audio = { ctx, buf, noise, out, last: 0, hum: null, roll: null };
            }
            if (audio.ctx.state === 'suspended') audio.ctx.resume();
        } catch (e) { audio = null; }
    }

    function knock(strength, freq) {
        if (!on || !audio || strength < 0.04) return;
        const { ctx, buf, out } = audio, now = ctx.currentTime;
        if (now - audio.last < 0.012) return;
        audio.last = now;
        const src = ctx.createBufferSource(), bp = ctx.createBiquadFilter(), g = ctx.createGain();
        src.buffer = buf; src.playbackRate.value = rand(0.8, 1.3);
        bp.type = 'bandpass'; bp.frequency.value = freq * rand(0.85, 1.2); bp.Q.value = 3;
        g.gain.value = Math.min(0.7, strength);
        src.connect(bp); bp.connect(g); g.connect(out);
        src.start(now);
    }

    function hum(start) {
        if (!audio) return;
        const { ctx, out } = audio, now = ctx.currentTime;
        if (start && on && !audio.hum) {
            const o = ctx.createOscillator(), lp = ctx.createBiquadFilter(), g = ctx.createGain();
            o.type = 'sawtooth'; o.frequency.value = 50;
            lp.type = 'lowpass'; lp.frequency.value = 180;
            g.gain.setValueAtTime(0, now); g.gain.linearRampToValueAtTime(0.09, now + 0.08);
            o.connect(lp); lp.connect(g); g.connect(out); o.start(now);
            audio.hum = { o, g };
        } else if (!start && audio.hum) {
            const { o, g } = audio.hum;
            g.gain.cancelScheduledValues(now); g.gain.setValueAtTime(g.gain.value, now); g.gain.linearRampToValueAtTime(0, now + 0.15);
            o.stop(now + 0.2);
            audio.hum = null;
        }
    }

    /** Rolling ball: level 0..1 (0 stops), speed 0..1 moves the pitch. */
    function roll(level, speed) {
        if (!audio) return;
        const { ctx, out } = audio, now = ctx.currentTime;
        if (level > 0.01 && on) {
            if (!audio.roll) {
                const src = ctx.createBufferSource(), bp = ctx.createBiquadFilter(), g = ctx.createGain();
                src.buffer = audio.noise; src.loop = true;
                bp.type = 'bandpass'; bp.Q.value = 1.2; bp.frequency.value = 900;
                g.gain.value = 0;
                src.connect(bp); bp.connect(g); g.connect(out); src.start(now);
                audio.roll = { src, bp, g };
            }
            audio.roll.g.gain.setTargetAtTime(Math.min(0.22, level * 0.22), now, 0.05);
            audio.roll.bp.frequency.setTargetAtTime(500 + 1600 * Math.max(0, Math.min(1, speed)), now, 0.05);
        } else if (audio.roll) {
            const { src, g } = audio.roll;
            g.gain.cancelScheduledValues(now); g.gain.setTargetAtTime(0, now, 0.05);
            src.stop(now + 0.3);
            audio.roll = null;
        }
    }

    /** A short melody (frequencies in Hz, one every `step` seconds), for wins. */
    function chime(notes, step, volume) {
        if (!on || !audio) return;
        const { ctx, out } = audio, t0 = ctx.currentTime;
        notes.forEach((f, i) => {
            const o = ctx.createOscillator(), g = ctx.createGain(), t = t0 + i * (step || 0.09);
            o.type = 'triangle';
            o.frequency.value = f;
            g.gain.setValueAtTime(0, t);
            g.gain.linearRampToValueAtTime(volume || 0.18, t + 0.01);
            g.gain.exponentialRampToValueAtTime(0.001, t + 0.35);
            o.connect(g); g.connect(out);
            o.start(t); o.stop(t + 0.4);
        });
    }

    function setOn(value) {
        if (!value) { hum(false); roll(0); }
        on = value;
        try { localStorage.setItem('casino-sound', on ? 'on' : 'off'); } catch (e) { }
        if (on) ensure();
    }

    // browsers only allow sound after a user gesture
    document.addEventListener('pointerdown', ensure, { passive: true });

    return { ensure, knock, hum, roll, chime, setOn, isOn: () => on };
})();
