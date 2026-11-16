<?php
/** @var array<int, array<string, mixed>> $services */
use App\Core\View;

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'Hizmetler',
    'title'   => 'Birlikte neler yapıyoruz?',
    'lead'    => 'Sana uygun çalışmayı seç; ayrıntıları ve paketleri sayfasında bulabilirsin.',
]);
?>
<section class="section">
    <div class="container">
        <?php if (!$services): ?>
        <p class="center lead">Hizmetler yakında burada olacak.</p>
        <?php else: ?>
        <div class="card-grid">
            <?php foreach ($services as $i => $service): ?>
            <article class="card">
                <?php if ($service['image']): ?>
                <div class="card-media"><img src="<?= e(url($service['image'])) ?>" alt="" loading="lazy"></div>
                <?php else: ?>
                <div class="card-number" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <?php endif; ?>
                <h2 class="h3"><a href="<?= e(url('/hizmetler/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h2>
                <?php if ($service['summary']): ?><p><?= e($service['summary']) ?></p><?php endif; ?>
                <a class="card-link" href="<?= e(url('/hizmetler/' . $service['slug'])) ?>" aria-label="<?= e($service['title']) ?>: ayrıntılar">Ayrıntılar →</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?= View::renderFile('partials/cta') ?>
