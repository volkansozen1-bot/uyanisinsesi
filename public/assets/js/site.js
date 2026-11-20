// Mobil menu
(function () {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('ana-menu');
    if (!toggle || !nav) return;
    toggle.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
})();

// Sayac animasyonu: sayi HTML'de zaten yazili, ekrana girince 0'dan o sayiya sayar
(function () {
    var items = document.querySelectorAll('[data-count]');
    if (!items.length || !('IntersectionObserver' in window)) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            var el = entry.target;
            var target = parseInt(el.getAttribute('data-count'), 10) || 0;
            var start = null;
            var duration = 1200;
            function step(time) {
                if (start === null) start = time;
                var progress = Math.min((time - start) / duration, 1);
                el.textContent = Math.round(target * progress);
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }, { threshold: 0.4 });

    items.forEach(function (el) { observer.observe(el); });
})();

// Geri sayim: kalan sure HTML'de zaten yazili, her saniye guncellenir
(function () {
    var box = document.querySelector('[data-countdown]');
    if (!box) return;
    var target = new Date(box.getAttribute('data-countdown')).getTime();
    if (isNaN(target)) return;
    function set(unit, value) {
        var el = box.querySelector('[data-unit="' + unit + '"]');
        if (el) el.textContent = value;
    }
    function tick() {
        var left = Math.max(0, Math.floor((target - Date.now()) / 1000));
        set('days', Math.floor(left / 86400));
        set('hours', Math.floor((left % 86400) / 3600));
        set('minutes', Math.floor((left % 3600) / 60));
        set('seconds', left % 60);
        if (left === 0) clearInterval(timer);
    }
    var timer = setInterval(tick, 1000);
    tick();
})();

// Sekmeler (hizmet sayfasi kitle bolumleri). JS yoksa tum bolumler alt alta gorunur.
// Adres cubugundaki #erkek gibi capalar ilgili sekmeyi acar.
(function () {
    var root = document.querySelector('[data-tabs]');
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = Array.prototype.slice.call(root.querySelectorAll('[role="tabpanel"]'));
    if (tabs.length < 2) return;

    function activate(id, focusTab) {
        var found = false;
        tabs.forEach(function (tab) {
            var on = tab.getAttribute('aria-controls') === id;
            if (on) found = true;
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
            tab.tabIndex = on ? 0 : -1;
            if (on && focusTab) tab.focus();
        });
        if (!found) return false;
        panels.forEach(function (panel) { panel.hidden = panel.id !== id; });
        return true;
    }

    root.classList.add('tabs-ready');
    activate(tabs[0].getAttribute('aria-controls'), false);

    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () {
            var id = tab.getAttribute('aria-controls');
            activate(id, false);
            if (history.replaceState) history.replaceState(null, '', '#' + id);
        });
        tab.addEventListener('keydown', function (event) {
            var next = null;
            if (event.key === 'ArrowRight') next = tabs[(index + 1) % tabs.length];
            if (event.key === 'ArrowLeft') next = tabs[(index - 1 + tabs.length) % tabs.length];
            if (event.key === 'Home') next = tabs[0];
            if (event.key === 'End') next = tabs[tabs.length - 1];
            if (!next) return;
            event.preventDefault();
            activate(next.getAttribute('aria-controls'), true);
        });
    });

    function fromHash() {
        var id = decodeURIComponent(location.hash.replace('#', ''));
        if (id && activate(id, false)) {
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    window.addEventListener('hashchange', fromHash);
    if (location.hash) fromHash();
})();

/* Tam ekran giris: ozune donen isik zerreleri + fareyle hafif derinlik (hero_full) */
(function () {
    var hero = document.querySelector('[data-illusion]');
    if (!hero) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canvas = hero.querySelector('.hero-full-particles');
    var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;

    if (!reduce) {
        hero.addEventListener('pointermove', function (e) {
            var r = hero.getBoundingClientRect();
            hero.style.setProperty('--mx', ((e.clientX - r.left) / r.width - 0.5).toFixed(3));
            hero.style.setProperty('--my', ((e.clientY - r.top) / r.height - 0.5).toFixed(3));
        });
        hero.addEventListener('pointerleave', function () {
            hero.style.setProperty('--mx', 0);
            hero.style.setProperty('--my', 0);
        });
    }
    if (!ctx) return;

    var w, h, cx, cy, maxR, dots = [], dpr = Math.min(window.devicePixelRatio || 1, 2), running = true;
    function spawn(d, anywhere) {
        d.a = Math.random() * Math.PI * 2;
        d.r = anywhere ? Math.random() * maxR : maxR * (0.85 + Math.random() * 0.3);
        d.v = 0.12 + Math.random() * 0.35;          // merkeze dogru hiz
        d.s = 0.002 + Math.random() * 0.004;        // donus
        d.z = 0.6 + Math.random() * 1.6;            // boyut
        return d;
    }
    function resize() {
        var r = hero.getBoundingClientRect();
        w = r.width; h = r.height;
        canvas.width = w * dpr; canvas.height = h * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        cx = w / 2; cy = h * 0.42; maxR = Math.hypot(w, h) * 0.55;
        var n = Math.round(Math.min(140, w * h / 9000));
        dots = [];
        for (var i = 0; i < n; i++) dots.push(spawn({}, true));
    }
    function frame() {
        ctx.clearRect(0, 0, w, h);
        for (var i = 0; i < dots.length; i++) {
            var d = dots[i];
            if (!reduce) {
                d.r -= d.v * (0.4 + d.r / maxR);
                d.a += d.s;
                if (d.r < 8) spawn(d, false);
            }
            var t = d.r / maxR;                       // merkeze yaklastikca parlar, sonra kaybolur
            var alpha = Math.min(1, t * 6) * (0.25 + 0.75 * (1 - t));
            ctx.beginPath();
            ctx.arc(cx + Math.cos(d.a) * d.r, cy + Math.sin(d.a) * d.r * 0.82, d.z, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(240, 222, 175,' + alpha.toFixed(3) + ')';
            ctx.fill();
        }
        if (!reduce && running) requestAnimationFrame(frame);
    }
    resize();
    window.addEventListener('resize', function () { resize(); if (reduce) frame(); });
    if ('IntersectionObserver' in window && !reduce) {
        new IntersectionObserver(function (entries) {
            var vis = entries[0].isIntersecting;
            if (vis && !running) { running = true; requestAnimationFrame(frame); }
            running = vis;
        }).observe(hero);
    }
    frame();
})();
