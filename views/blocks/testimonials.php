<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$items = $data['testimonials'] ?? [];
if (!$items) {
    return;
}
?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <?php if ($block['subtitle']): ?><span class="eyebrow"><?= e($block['subtitle']) ?></span><?php endif; ?>
            <?php if ($block['title']): ?><h2><?= e($block['title']) ?></h2><?php endif; ?>
        </div>
        <div class="card-grid">
            <?php foreach ($items as $item): ?>
            <?php $rating = max(1, min(5, (int) $item['rating'])); ?>
            <figure class="testimonial">
                <div class="stars" role="img" aria-label="5 üzerinden <?= $rating ?>"><?= str_repeat('★', $rating) ?></div>
                <blockquote><?= e($item['text']) ?></blockquote>
                <figcaption><cite><?= e($item['name']) ?></cite></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
