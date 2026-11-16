<?php
/** @var array<int, array<string, mixed>> $courses */
use App\Core\View;

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'Eğitimler',
    'title'   => 'Derinleşmek isteyenler için',
    'lead'    => 'Dönem dönem açılan, adım adım ilerleyen eğitim programları.',
]);
?>
<section class="section">
    <div class="container">
        <?php if (!$courses): ?>
        <p class="center lead">Yeni eğitimler yakında duyurulacak.</p>
        <?php else: ?>
        <div class="card-grid">
            <?php foreach ($courses as $course): ?>
            <article class="card">
                <div class="card-media">
                    <?php if ($course['image']): ?><img src="<?= e(url($course['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
                </div>
                <?php if ($course['start_date']): ?>
                <div class="card-date">Başlangıç: <?= e(tr_date($course['start_date'])) ?></div>
                <?php endif; ?>
                <h2 class="h3"><a href="<?= e(url('/egitimler/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></h2>
                <?php if ($course['summary']): ?><p><?= e($course['summary']) ?></p><?php endif; ?>
                <a class="card-link" href="<?= e(url('/egitimler/' . $course['slug'])) ?>" aria-label="<?= e($course['title']) ?>: eğitimi incele">Eğitimi incele →</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?= View::renderFile('partials/cta') ?>
