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

// Basliktan otomatik adres kisaltmasi: <input data-slug-from="f-title">
// Kullanici kisaltmayi elle degistirdiyse dokunulmaz.
(function () {
    var map = { 'ç': 'c', 'ğ': 'g', 'ı': 'i', 'ö': 'o', 'ş': 's', 'ü': 'u', 'Ç': 'c', 'Ğ': 'g', 'İ': 'i', 'I': 'i', 'Ö': 'o', 'Ş': 's', 'Ü': 'u' };
    function slugify(text) {
        return text.replace(/[çğıöşüÇĞİIÖŞÜ]/g, function (c) { return map[c]; })
            .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 140);
    }
    document.querySelectorAll('[data-slug-from]').forEach(function (slug) {
        var source = document.getElementById(slug.getAttribute('data-slug-from'));
        if (!source) return;
        var touched = slug.value !== '';
        slug.addEventListener('input', function () { touched = slug.value !== ''; });
        source.addEventListener('input', function () {
            if (!touched) slug.value = slugify(source.value);
        });
    });
})();

// Gorsel secilince onizleme
(function () {
    document.querySelectorAll('[data-image-field]').forEach(function (box) {
        var input = box.querySelector('input[type="file"]');
        var preview = box.querySelector('.image-preview');
        if (!input || !preview || !window.URL) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            preview.innerHTML = '';
            var img = document.createElement('img');
            img.alt = '';
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
        });
    });
})();

// Zengin metin editoru: <textarea data-richtext>
// Yazarken bicimlendirme yapilir, form gonderilirken HTML textarea'ya yazilir.
// Sunucu kaydederken HtmlSanitizer ile temizler; burada temizlik guvenlik icin yeterli sayilmaz.
(function () {
    var areas = document.querySelectorAll('textarea[data-richtext]');
    if (!areas.length || !document.execCommand) return;

    var buttons = [
        ['bold', '<b>K</b>', 'Kalın'],
        ['italic', '<i>İ</i>', 'İtalik'],
        ['h2', 'B2', 'Başlık'],
        ['h3', 'B3', 'Ara başlık'],
        ['p', '¶', 'Paragraf'],
        ['insertUnorderedList', '•', 'Madde listesi'],
        ['insertOrderedList', '1.', 'Numaralı liste'],
        ['blockquote', '❝', 'Alıntı'],
        ['link', '🔗', 'Bağlantı'],
        ['unlink', '⛓', 'Bağlantıyı kaldır'],
        ['removeFormat', '⌫', 'Biçimi temizle'],
        ['source', '&lt;/&gt;', 'HTML olarak düzenle']
    ];

    areas.forEach(function (area) {
        var wrap = document.createElement('div');
        wrap.className = 'rte';
        var bar = document.createElement('div');
        bar.className = 'rte-bar';
        bar.setAttribute('role', 'toolbar');
        bar.setAttribute('aria-label', 'Metin biçimlendirme');
        var editor = document.createElement('div');
        editor.className = 'rte-editor prose';
        editor.contentEditable = 'true';
        editor.setAttribute('role', 'textbox');
        editor.setAttribute('aria-multiline', 'true');
        var label = document.querySelector('label[for="' + area.id + '"]');
        if (label) editor.setAttribute('aria-label', label.textContent.replace('*', '').trim());
        editor.innerHTML = area.value;

        var sourceMode = false;
        buttons.forEach(function (b) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rte-btn';
            btn.innerHTML = b[1];
            btn.title = b[2];
            btn.setAttribute('aria-label', b[2]);
            btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
            btn.addEventListener('click', function () {
                if (b[0] === 'source') {
                    sourceMode = !sourceMode;
                    if (sourceMode) { area.value = editor.innerHTML; }
                    else { editor.innerHTML = area.value; }
                    wrap.classList.toggle('is-source', sourceMode);
                    btn.setAttribute('aria-pressed', sourceMode ? 'true' : 'false');
                    (sourceMode ? area : editor).focus();
                    return;
                }
                if (sourceMode) return;
                editor.focus();
                if (b[0] === 'link') {
                    var url = window.prompt('Bağlantı adresi (https://... ya da /sayfa):', 'https://');
                    if (url) document.execCommand('createLink', false, url);
                } else if (['h2', 'h3', 'p', 'blockquote'].indexOf(b[0]) !== -1) {
                    document.execCommand('formatBlock', false, '<' + b[0] + '>');
                } else {
                    document.execCommand(b[0], false, null);
                }
            });
            bar.appendChild(btn);
        });

        // Baska yerden yapistirilan metin duz metin olarak eklenir (Word bicimleri tasinmasin)
        editor.addEventListener('paste', function (e) {
            if (!e.clipboardData) return;
            e.preventDefault();
            var text = e.clipboardData.getData('text/plain');
            var html = text.split(/\r?\n\r?\n/).map(function (p) {
                return '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\r?\n/g, '<br>') + '</p>';
            }).join('');
            document.execCommand('insertHTML', false, html);
        });

        area.parentNode.insertBefore(wrap, area);
        wrap.appendChild(bar);
        wrap.appendChild(editor);
        wrap.appendChild(area);
        area.classList.add('rte-source');

        var form = area.form;
        if (form) {
            form.addEventListener('submit', function () {
                if (!sourceMode) area.value = editor.innerHTML;
            });
        }
    });
})();
