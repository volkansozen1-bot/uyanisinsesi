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

    // "Bilgisayardan gorsel ekle": dosya secici acar, yukler ve imleç konumuna ekler
    function insertImage(editor) {
        var form = editor.closest('form');
        var token = form && form.querySelector('input[name="_csrf"]');
        var base = document.body.getAttribute('data-admin-base') || '';
        if (!token || !window.FormData) {
            window.alert('Bu tarayıcıda görsel ekleme çalışmıyor. Görseli Medya sayfasından yükleyip adresini kullanabilirsiniz.');
            return;
        }
        var picker = document.createElement('input');
        picker.type = 'file';
        picker.accept = 'image/jpeg,image/png,image/webp,image/gif';
        picker.addEventListener('change', function () {
            var file = picker.files && picker.files[0];
            if (!file) return;
            var data = new FormData();
            data.append('_csrf', token.value);
            data.append('image', file);
            data.append('alt', '');
            editor.setAttribute('aria-busy', 'true');
            fetch(base + 'yonetim/medya/yukle-json', { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    editor.removeAttribute('aria-busy');
                    if (!res.ok || !res.body.url) {
                        window.alert(res.body.error || 'Görsel yüklenemedi.');
                        return;
                    }
                    editor.focus();
                    document.execCommand('insertHTML', false, '<img src="' + res.body.url + '" alt="">');
                })
                .catch(function () {
                    editor.removeAttribute('aria-busy');
                    window.alert('Görsel yüklenemedi. Bağlantınızı kontrol edip yeniden deneyin.');
                });
        });
        picker.click();
    }

    if (!areas.length || !document.execCommand) return;

    var buttons = [
        ['bold', '<b>K</b>', 'Kalın'],
        ['italic', '<i>İ</i>', 'İtalik'],
        ['underline', '<u>A</u>', 'Altı çizili'],
        ['h2', 'B2', 'Başlık'],
        ['h3', 'B3', 'Ara başlık'],
        ['p', '¶', 'Paragraf'],
        ['insertUnorderedList', '•', 'Madde listesi'],
        ['insertOrderedList', '1.', 'Numaralı liste'],
        ['blockquote', '❝', 'Alıntı'],
        ['image', '🖼', 'Bilgisayardan görsel ekle'],
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
                if (b[0] === 'image') {
                    insertImage(editor);
                } else if (b[0] === 'link') {
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

        // Boyut / renk / yazi tipi (secili metne) ve hizalama (paragrafa): sitede tanimli siniflarla
        var styleMenus = [
            ['Boyut', 'span', [['t-sm', 'Küçük'], ['', 'Normal'], ['t-lg', 'Büyük'], ['t-xl', 'Çok büyük'], ['t-xxl', 'Dev']]],
            ['Renk', 'span', [['c-ink', 'Koyu yeşil'], ['c-gold', 'Altın'], ['c-plum', 'Mürdüm'], ['c-green', 'Adaçayı'], ['c-rose', 'Gül'], ['c-muted', 'Gri'], ['', 'Varsayılan']]],
            ['Yazı tipi', 'span', [['f-serif', 'Zarif (başlık)'], ['f-sans', 'Sade']]],
            ['Hizala', 'block', [['a-left', 'Sola'], ['a-center', 'Ortaya'], ['a-right', 'Sağa'], ['a-justify', 'İki yana']]]
        ];
        styleMenus.forEach(function (m) {
            var sel = document.createElement('select');
            sel.className = 'rte-select';
            sel.setAttribute('aria-label', m[0]);
            sel.innerHTML = '<option value="-">' + m[0] + '</option>' + m[2].map(function (o) { return '<option value="' + o[0] + '">' + o[1] + '</option>'; }).join('');
            sel.addEventListener('change', function () {
                var cls = sel.value; sel.value = '-';
                if (sourceMode || cls === '-') return;
                editor.focus();
                var s = window.getSelection();
                if (!s.rangeCount) return;
                if (m[1] === 'block') {
                    var node = s.anchorNode && (s.anchorNode.nodeType === 1 ? s.anchorNode : s.anchorNode.parentNode);
                    var block = node && node.closest('p,h2,h3,h4,li,blockquote');
                    if (block && editor.contains(block)) {
                        block.className = (block.className.replace(/\ba-\w+\b/g, '') + ' ' + cls).trim();
                    }
                    return;
                }
                if (s.isCollapsed) { window.alert('Önce biçimlendirmek istediğiniz metni seçin.'); return; }
                var div = document.createElement('div');
                div.appendChild(s.getRangeAt(0).cloneContents());
                var html = div.innerHTML;
                document.execCommand('insertHTML', false, cls ? '<span class="' + cls + '">' + html + '</span>' : html);
            });
            bar.appendChild(sel);
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
