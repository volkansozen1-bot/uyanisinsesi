<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$posts = $data['posts'] ?? [];
if (!$posts) {
    return;
}
$months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <?php if ($block['subtitle']): ?><span class="eyebrow"><?= e($block['subtitle']) ?></span><?php endif; ?>
            <?php if ($block['title']): ?><h2><?= e($block['title']) ?></h2><?php endif; ?>
        </div>
        <div class="card-grid">
            <?php foreach ($posts as $post): ?>
            <?php $time = strtotime((string) $post['published_at']); ?>
            <article class="card">
                <div class="card-media">
                    <?php if ($post['cover_image']): ?><img src="<?= e(url($post['cover_image'])) ?>" alt="" loading="lazy"><?php endif; ?>
                </div>
                <div class="card-date"><?= (int) date('j', $time) ?> <?= $months[(int) date('n', $time)] ?> <?= date('Y', $time) ?></div>
                <h3><a href="<?= e(url('/blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
                <?php if ($post['excerpt']): ?><p><?= e($post['excerpt']) ?></p><?php endif; ?>
                <a class="card-link" href="<?= e(url('/blog/' . $post['slug'])) ?>" aria-label="<?= e($post['title']) ?>: devamını oku">Devamını oku →</a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
