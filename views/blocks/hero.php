<?php
$extra = json_decode((string) ($block['extra_json'] ?? ''), true) ?: [];
?>
<section class="opening">
    <div class="container opening-grid">
        <div class="opening-copy">
            <p class="eyebrow"><?= e(setting('site_name', 'Perihan Ateş')) ?></p>
            <h1><?= e($block['title']) ?></h1>
            <?php if ($block['subtitle']): ?><p class="lead"><?= e($block['subtitle']) ?></p><?php endif; ?>
            <div class="opening-links">
                <a class="btn" href="<?= e(url($block['button_url'] ?: '/hizmetler')) ?>"><?= e($block['button_text'] ?: 'Çalışmaları keşfet') ?></a>
                <a class="opening-contact" href="<?= e(url($extra['button2_url'] ?? '/iletisim')) ?>"><?= e($extra['button2_text'] ?? 'İletişime geç') ?> <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <div class="opening-sky" aria-hidden="true">
            <div class="opening-orbit"></div>
            <div class="opening-orbit opening-orbit-inner"></div>
            <div class="opening-window">
                <?php if ($block['image']): ?>
                <img src="<?= e(url($block['image'])) ?>" alt="" fetchpriority="high">
                <?php else: ?>
                <span class="opening-moon"></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
