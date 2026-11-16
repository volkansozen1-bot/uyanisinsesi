// Yonetim paneli: mobil menu ve silme onayi
(function () {
    var toggle = document.querySelector('.menu-toggle');
    var sidebar = document.getElementById('yonetim-menu');
    var backdrop = document.querySelector('.sidebar-backdrop');
    if (toggle && sidebar) {
        var setOpen = function (open) {
            document.body.classList.toggle('menu-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (backdrop) backdrop.hidden = !open;
        };
        toggle.addEventListener('click', function () {
            setOpen(!document.body.classList.contains('menu-open'));
        });
        if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
    }

    // <form data-confirm="..."> gonderilmeden once onay ister
    document.addEventListener('submit', function (e) {
        var message = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            e.preventDefault();
        }
    });
})();
