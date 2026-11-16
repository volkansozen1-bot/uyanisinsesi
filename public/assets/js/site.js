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
