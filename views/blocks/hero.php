<?php
/**
 * Karşılama bölümü ve isteğe bağlı kapak görseli.
 * @var array<string, mixed> $block
 */
$extra    = json_decode((string) ($block['extra_json'] ?? ''), true) ?: [];
$keywords = array_filter((array) ($extra['keywords'] ?? []), 'is_string');
?>
<section class="hero hero-cover">
    <?php if ($block['image']): ?>
    <img class="hero-cover-image" src="<?= e(url($block['image'])) ?>" alt="" fetchpriority="high">
    <?php endif; ?>
    <div class="container hero-grid">
        <div class="hero-text">
            <?php if (!empty($extra['eyebrow'])): ?><span class="eyebrow"><?= e($extra['eyebrow']) ?></span><?php endif; ?>
            <h1><?= e($block['title']) ?></h1>
            <div class="ornament" aria-hidden="true"><span></span></div>
            <?php if ($block['subtitle']): ?><p class="lead"><?= e($block['subtitle']) ?></p><?php endif; ?>
            <div class="hero-actions">
                <?php if ($block['button_text'] && $block['button_url']): ?>
                <a class="btn" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a>
                <?php endif; ?>
                <?php if (!empty($extra['button2_text']) && !empty($extra['button2_url'])): ?>
                <a class="btn btn-ghost" href="<?= e(url($extra['button2_url'])) ?>"><?= e($extra['button2_text']) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($keywords): ?>
    <div class="hero-band" aria-hidden="true">
        <div class="hero-band-track">
            <?php foreach ($keywords as $word): ?>
            <span><?= e($word) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
