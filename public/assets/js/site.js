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
