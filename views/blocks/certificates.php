<?php /** Ana sayfa: QR kodlu sertifika karti (Hakkimda'daki kartin aynisi) */ ?>
<section class="section section-alt about-certs">
    <div class="container narrow center">
        <h2><?= e($block['title']) ?></h2>
        <?php if ($block['subtitle']): ?><p class="muted"><?= nl2br(e($block['subtitle'])) ?></p><?php endif; ?>
        <div class="cert-card">
            <img src="<?= e($block['image'] ? url($block['image']) : asset('img/qr-sertifikalar.png')) ?>" alt="Sertifikalar QR kodu" width="160" height="160" loading="lazy">
            <h3>Okut, Sertifikaları İncele</h3>
            <p class="muted">Telefonunuzun kamerasıyla QR kodu okutarak tüm sertifikalara göz atabilirsiniz.</p>
            <a class="btn" href="<?= e(url($block['button_url'] ?: '/sertifikalar')) ?>"><?= e($block['button_text'] ?: 'Tüm Sertifikaları Gör') ?></a>
        </div>
    </div>
</section>
