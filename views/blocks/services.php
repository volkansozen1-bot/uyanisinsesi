<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$services = $data['services'] ?? [];
if (!$services) {
    return;
}
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <?php if ($block['subtitle']): ?><span class="eyebrow"><?= e($block['subtitle']) ?></span><?php endif; ?>
            <?php if ($block['title']): ?><h2><?= e($block['title']) ?></h2><?php endif; ?>
        </div>
        <div class="card-grid">
            <?php foreach ($services as $i => $service): ?>
            <article class="card">
                <div class="card-number" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <h3><a href="<?= e(url('/hizmetler/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                <?php if ($service['summary']): ?><p><?= e($service['summary']) ?></p><?php endif; ?>
                <a class="card-link" href="<?= e(url('/hizmetler/' . $service['slug'])) ?>" aria-label="<?= e($service['title']) ?>: ayrıntılar">Ayrıntılar →</a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
