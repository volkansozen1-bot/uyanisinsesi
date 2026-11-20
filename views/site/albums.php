<?php
/** @var array<int, array<string, mixed>> $albums */
use App\Core\View;

$types = ['album' => 'Albüm', 'ep' => 'EP', 'single' => 'Single'];

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'Müzik',
    'title'   => 'Albümler',
    'lead'    => 'Tüm kayıtlar dijital platformlarda dinlenebilir.',
]);
?>
<section class="section">
    <div class="container">
        <?php if (!$albums): ?>
        <p class="center lead">Albümler yakında burada olacak.</p>
        <?php else: ?>
        <div class="card-grid album-grid">
            <?php foreach ($albums as $album): ?>
            <?php
            $meta = array_filter([
                $types[$album['type']] ?? null,
                $album['year'] ? (string) $album['year'] : null,
                $album['track_count'] ? $album['track_count'] . ' parça' : null,
            ]);
            ?>
            <article class="card album">
                <div class="album-cover">
                    <?php if ($album['cover_image']): ?><img src="<?= e(url($album['cover_image'])) ?>" alt="<?= e($album['title']) ?> kapağı" loading="lazy"><?php endif; ?>
                </div>
                <?php if ($meta): ?><div class="card-date"><?= e(implode(' · ', $meta)) ?></div><?php endif; ?>
                <h2 class="h3"><?= e($album['title']) ?></h2>
                <?php if ($album['listen_url']): ?>
                <a class="card-link" href="<?= e($album['listen_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($album['title']) ?>: dinle">Dinle →</a>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        // Spotify dugmesi: Sosyal Medya bolumundeki ilk aktif Spotify baglantisi (yoksa dugme gorunmez)
        try {
            $spotify = App\Core\Database::one(
                "SELECT url FROM social_links WHERE platform = 'spotify' AND is_active = 1 ORDER BY sort_order, id"
            );
        } catch (Throwable $e) {
            $spotify = null;
        }
        ?>
        <?php if ($spotify): ?>
        <p class="center" style="margin-top:2.5rem"><a class="btn btn-outline" href="<?= e($spotify['url']) ?>" target="_blank" rel="noopener">Spotify Profilini Aç</a></p>
        <?php endif; ?>
    </div>
</section>
