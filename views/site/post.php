<?php
/**
 * @var array<string, mixed> $post
 * @var array<int, array<string, mixed>> $others
 */
?>
<header class="page-head">
    <div class="container narrow">
        <span class="eyebrow">
            <?= e(tr_date($post['published_at'])) ?><?php if ($post['category_name']): ?> · <?= e($post['category_name']) ?><?php endif; ?>
        </span>
        <h1><?= e($post['title']) ?></h1>
        <?php if ($post['excerpt']): ?><p class="lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
    </div>
</header>

<article class="section">
    <div class="container narrow prose">
        <?php if ($post['cover_image']): ?>
        <img class="prose-image" src="<?= e(url($post['cover_image'])) ?>" alt="">
        <?php endif; ?>
        <?php /* body panelden girilen HTML'dir; kaydedilirken temizlenir */ ?>
        <?= $post['body'] ?>
        <p class="back-link"><a href="<?= e(url('/blog')) ?>">← Tüm yazılar</a></p>
    </div>
</article>

<?php if ($others): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Blog</span>
            <h2>Diğer yazılar</h2>
        </div>
        <div class="card-grid">
            <?php foreach ($others as $other): ?>
            <article class="card">
                <div class="card-date"><?= e(tr_date($other['published_at'])) ?></div>
                <h3><a href="<?= e(url('/blog/' . $other['slug'])) ?>"><?= e($other['title']) ?></a></h3>
                <a class="card-link" href="<?= e(url('/blog/' . $other['slug'])) ?>" aria-label="<?= e($other['title']) ?>: devamını oku">Devamını oku →</a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
