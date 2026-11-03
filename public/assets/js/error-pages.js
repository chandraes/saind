(() => {
    'use strict';

    const root = document.querySelector('[data-error-page]');
    if (!root) {
        return;
    }

    const scene = root.querySelector('.scene');
    const illustration = scene?.querySelector('svg');
    const truck = scene?.querySelector('.active-truck');
    const label = root.querySelector('[data-error-stage]');
    const play = root.querySelector('[data-error-play]');
    if (!illustration || !truck || !play || !label) {
        return;
    }

    const sceneType = root.dataset.errorScene;
    const defaultPhase = root.dataset.errorPhase;
    const wheels = truck.querySelectorAll('.wheel');
    const repairArm = scene.querySelector('.repair-arm');
    const smoke = scene.querySelector('.smoke');
    const note = illustration.querySelector('.site-note');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const gateScenes = ['checkpoint', 'payment', 'barrier', 'expired'];
    let time = 0;
    let paused = motion.matches;
    let lastFrame = 0;
    let lastPhase = '';
    let visible = !document.hidden;
    let frameId;

    function ease(value) {
        value = Math.max(0, Math.min(1, value));
        return value * value * (3 - 2 * value);
    }

    function draw() {
        let x = 650;
        let direction = 1;
        let phase = defaultPhase;

        if (sceneType === 'missing') {
            if (time < 7) {
                x = 200 + 530 * ease(time / 7);
                phase = 'Mencari jalur yang tersedia';
            } else if (time < 12) {
                x = 730;
                phase = 'Jalur tidak ditemukan';
            } else if (time < 14) {
                x = 730;
                direction = Math.cos(Math.PI * (time - 12) / 2);
                phase = 'Putar balik mencari jalan lain';
            } else if (time < 21) {
                x = 730 - 530 * ease((time - 14) / 7);
                direction = -1;
                phase = 'Kembali menuju jalur alternatif';
            } else {
                x = 200;
                direction = -Math.cos(Math.PI * ease((time - 21) / 3));
                phase = 'Menentukan jalur berikutnya';
            }
        } else if (gateScenes.includes(sceneType)) {
            x = 260 + 400 * ease(Math.min(time, 6) / 6);
        }

        const shake = sceneType === 'service' ? Math.sin(time * 2) * .35 : 0;
        truck.setAttribute('transform', `translate(${x.toFixed(2)} ${235 + shake}) scale(${direction.toFixed(3)} 1)`);

        const moving = (sceneType === 'missing' && (time < 7 || (time >= 14 && time < 21)))
            || (gateScenes.includes(sceneType) && time < 6);
        wheels.forEach((wheel, index) => {
            wheel.setAttribute('transform', `translate(${[-51, -16, 56][index]} 16) rotate(${moving ? time * 145 : 0})`);
        });
        if (repairArm) {
            repairArm.setAttribute('transform', `rotate(${Math.sin(time * 3) * 8} 10 -28)`);
        }
        if (smoke) {
            smoke.style.opacity = String(.35 + .2 * Math.sin(time * 1.5));
            smoke.setAttribute('transform', `translate(0 ${-3 * (1 + Math.sin(time * 1.5))})`);
        }

        if (innerWidth <= 700) {
            if (sceneType === 'queue') {
                illustration.setAttribute('viewBox', '140 30 900 290');
                note.setAttribute('x', '590');
            } else {
                const viewportX = Math.max(0, Math.min(700, x - 205));
                illustration.setAttribute('viewBox', `${viewportX.toFixed(2)} 30 500 290`);
                note.setAttribute('x', String(viewportX + 250));
            }
        } else {
            illustration.setAttribute('viewBox', '0 0 1200 320');
            note.setAttribute('x', '600');
        }

        if (phase !== lastPhase) {
            label.textContent = phase;
            lastPhase = phase;
        }
        play.textContent = paused ? 'Putar animasi' : 'Jeda animasi';
    }

    function frame(now) {
        if (lastFrame && !paused && visible) {
            time = (time + Math.min((now - lastFrame) / 1000, .06)) % 24;
            draw();
        }
        lastFrame = now;
        frameId = requestAnimationFrame(frame);
    }

    play.hidden = false;
    play.addEventListener('click', () => {
        paused = !paused;
        draw();
    });
    root.querySelector('[data-error-back]')?.addEventListener('click', event => {
        if (document.referrer && new URL(document.referrer).origin === location.origin && history.length > 1) {
            event.preventDefault();
            history.back();
        }
    });
    document.addEventListener('visibilitychange', () => {
        visible = !document.hidden;
    });
    addEventListener('resize', draw);
    motion.addEventListener('change', event => {
        if (event.matches) {
            paused = true;
            draw();
        }
    });
    addEventListener('pagehide', () => cancelAnimationFrame(frameId));
    addEventListener('pageshow', event => {
        if (event.persisted) {
            lastFrame = 0;
            frameId = requestAnimationFrame(frame);
        }
    });

    draw();
    frameId = requestAnimationFrame(frame);
})();
