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
        <?php if ($block['title']): ?><h2><?= e($block['title']) ?></h2><?php endif; ?>
        <div class="card-grid">
            <?php foreach ($services as $service): ?>
            <article class="card">
                <h3><a href="<?= e(url('/hizmetler/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                <?php if ($service['summary']): ?><p><?= e($service['summary']) ?></p><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
