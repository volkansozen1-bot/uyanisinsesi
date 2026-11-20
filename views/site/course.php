<?php
/**
 * @var array<string, mixed> $course
 * @var array<int, array<string, mixed>> $modules
 * @var array<int, array<string, mixed>> $plans
 * @var array<int, array<string, mixed>> $faqs
 */
use App\Core\View;

$until = $course['countdown_until'] ? strtotime((string) $course['countdown_until']) : null;
?>
<header class="page-head">
    <div class="container narrow">
        <span class="eyebrow">Eğitim</span>
        <h1><?= e($course['title']) ?></h1>
        <?php if ($course['summary']): ?><p class="lead"><?= e($course['summary']) ?></p><?php endif; ?>
        <?php if ($course['start_date']): ?>
        <p class="promo-date">Başlangıç: <?= e(tr_date($course['start_date'])) ?></p>
        <?php endif; ?>
        <?php if ($until && $until > time()): ?>
        <?php $left = $until - time(); ?>
        <div class="countdown" data-countdown="<?= e(date('c', $until)) ?>" role="timer" aria-label="Başlangıca kalan süre">
            <div><strong data-unit="days"><?= intdiv($left, 86400) ?></strong><span>Gün</span></div>
            <div><strong data-unit="hours"><?= intdiv($left % 86400, 3600) ?></strong><span>Saat</span></div>
            <div><strong data-unit="minutes"><?= intdiv($left % 3600, 60) ?></strong><span>Dakika</span></div>
            <div><strong data-unit="seconds"><?= $left % 60 ?></strong><span>Saniye</span></div>
        </div>
        <?php endif; ?>
        <?php if ($plans): ?><p><a class="btn" href="#kayit">Kayıt Seçenekleri</a></p><?php endif; ?>
    </div>
</header>

<article class="section">
    <div class="container narrow prose">
        <?php if ($course['image']): ?>
        <img class="prose-image" src="<?= e(url($course['image'])) ?>" alt="">
        <?php endif; ?>
        <?php /* Panelden girilen zengin metin; kaydedilirken HtmlSanitizer ile temizlenir, burada oldugu gibi basilir */ ?>
        <?= $course['body'] ?>
    </div>
</article>

<?php if ($modules): ?>
<section class="section section-alt">
    <div class="container narrow">
        <div class="section-head">
            <span class="eyebrow">Program</span>
            <h2>Adım adım içerik</h2>
        </div>
        <ol class="steps">
            <?php foreach ($modules as $i => $module): ?>
            <li>
                <span class="step-number" aria-hidden="true"><?= $i + 1 ?></span>
                <div>
                    <h3><?= e($module['title']) ?></h3>
                    <?php if ($module['description']): ?><p><?= nl2br(e($module['description'])) ?></p><?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<?php if ($plans): ?>
<section class="section" id="kayit">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Kayıt</span>
            <h2>Katılım seçenekleri</h2>
        </div>
        <div class="card-grid price-grid">
            <?php foreach ($plans as $plan): ?>
            <div class="card price-card">
                <h3><?= e($plan['name']) ?></h3>
                <?php if ($plan['price'] !== null): ?>
                <div class="price">
                    <?php if ($plan['list_price'] !== null && (float) $plan['list_price'] > (float) $plan['price']): ?>
                    <del><?= e(tr_price($plan['list_price'])) ?></del>
                    <?php endif; ?>
                    <?= e(tr_price($plan['price'])) ?>
                </div>
                <?php else: ?>
                <div class="price price-ask">Fiyat için iletişime geçin</div>
                <?php endif; ?>
                <?php if ($plan['description']): ?><p><?= e($plan['description']) ?></p><?php endif; ?>
                <a class="btn" href="<?= e(url($plan['button_url'] ?: '/iletisim')) ?>"><?= e($plan['button_text'] ?: 'Kayıt İçin Yaz') ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= View::renderFile('partials/faq', ['faqs' => $faqs]) ?>
<?= View::renderFile('partials/cta') ?>
