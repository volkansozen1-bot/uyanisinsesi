<?php /** Tam ekran fotografli giris: buyuk soz, kaynak, buton */ ?>
<section class="hero-full<?= $block['image'] ? '' : ' hero-full-plain' ?>">
    <?php if ($block['image']): ?>
    <img class="hero-full-image" src="<?= e(url($block['image'])) ?>" alt="" fetchpriority="high">
    <?php endif; ?>
    <div class="container hero-full-content">
        <h1 class="hero-full-quote">“<?= e(trim((string) $block['subtitle'])) ?>”</h1>
        <?php if ($block['title']): ?><p class="hero-full-author">— <?= e($block['title']) ?></p><?php endif; ?>
        <?php if ($block['button_text'] && $block['button_url']): ?>
        <a class="hero-full-btn" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a>
        <?php endif; ?>
    </div>
</section>
