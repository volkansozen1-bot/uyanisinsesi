<?php
/**
 * @var array<int, array<string, mixed>> $posts
 * @var array<string, mixed>|null $category
 * @var array<int, array<string, mixed>> $categories
 * @var int $page
 * @var int $pages
 * @var string $basePath
 */
use App\Core\View;

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'Blog',
    'title'   => $category ? $category['name'] : 'Yazılar',
    'lead'    => $category ? null : 'Yolculuktan notlar, pratikler ve düşünceler.',
]);
?>
<section class="section">
    <div class="container">
        <?php if ($categories): ?>
        <nav class="chips" aria-label="Kategoriler">
            <a href="<?= e(url('/blog')) ?>"<?= $category ? '' : ' aria-current="page"' ?>>Tümü</a>
            <?php foreach ($categories as $item): ?>
            <a href="<?= e(url('/blog/kategori/' . $item['slug'])) ?>"<?= $category && $category['slug'] === $item['slug'] ? ' aria-current="page"' : '' ?>><?= e($item['name']) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php if (!$posts): ?>
        <p class="center lead">Henüz yazı yok.</p>
        <?php else: ?>
        <div class="card-grid">
            <?php foreach ($posts as $post): ?>
            <article class="card">
                <div class="card-media">
                    <?php if ($post['cover_image']): ?><img src="<?= e(url($post['cover_image'])) ?>" alt="" loading="lazy"><?php endif; ?>
                </div>
                <div class="card-date"><?= e(tr_date($post['published_at'])) ?></div>
                <h2 class="h3"><a href="<?= e(url('/blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h2>
                <?php if ($post['excerpt']): ?><p><?= e($post['excerpt']) ?></p><?php endif; ?>
                <a class="card-link" href="<?= e(url('/blog/' . $post['slug'])) ?>" aria-label="<?= e($post['title']) ?>: devamını oku">Devamını oku →</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Sayfalar">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= e(url($basePath) . ($i > 1 ? '?sayfa=' . $i : '')) ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>
