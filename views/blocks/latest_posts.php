<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$posts = $data['posts'] ?? [];
if (!$posts) {
    return;
}
?>
<section class="section">
    <div class="container">
        <?php if ($block['title']): ?><h2><?= e($block['title']) ?></h2><?php endif; ?>
        <div class="card-grid">
            <?php foreach ($posts as $post): ?>
            <article class="card">
                <h3><a href="<?= e(url('/blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
                <?php if ($post['excerpt']): ?><p><?= e($post['excerpt']) ?></p><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
