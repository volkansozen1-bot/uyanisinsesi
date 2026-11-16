<?php
/** Sayfa sonu cagri bandi. */
$whatsapp = preg_replace('/\D+/', '', setting('whatsapp_number'));
?>
<section class="section section-dark promo">
    <div class="container narrow">
        <span class="eyebrow">Bir sorun mu var?</span>
        <h2>Birlikte konuşalım</h2>
        <p class="lead">Hangi çalışmanın sana uygun olduğundan emin değilsen yaz, birlikte bakalım.</p>
        <div class="hero-actions" style="justify-content:center">
            <a class="btn" href="<?= e(url('/iletisim')) ?>">İletişime Geç</a>
            <?php if ($whatsapp): ?>
            <a class="btn btn-ghost" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener">WhatsApp'tan Yaz</a>
            <?php endif; ?>
        </div>
    </div>
</section>
