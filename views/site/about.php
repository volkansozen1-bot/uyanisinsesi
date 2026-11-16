<?php
/**
 * Hakkimda sayfasi (pages.template = 'about').
 * @var array<string, mixed> $page
 * @var array<int, array<string, mixed>> $timeline
 * @var array<int, array<string, mixed>> $values
 */
use App\Core\View;

$quote       = setting('about_quote');
$quoteAuthor = setting('about_quote_author');

// Yaklasim kartlari icin ince cizgili ikonlar (24x24, stroke = currentColor)
$icons = [
    'breath' => '<path d="M3 9c3 0 4-2 7-2s4 2 7 2 3-1 4-1"/><path d="M3 14c3 0 4-2 7-2s4 2 7 2 3-1 4-1"/><path d="M6 19c2 0 3-1.5 5-1.5S14 19 16 19"/>',
    'heart'  => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/><path d="M12 11v4M10 13h4"/>',
    'moon'   => '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.500 6.500 0 0 0 10.500 10.500z"/><path d="M17 3v3M15.500 4.500h3"/>',
    'circle' => '<circle cx="12" cy="12" r="8.500"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="0.8" fill="currentColor"/>',
    'leaf'   => '<path d="M5 19c0-8 5-13 14-14-1 9-6 14-14 14z"/><path d="M5 19l8-8"/>',
];
?>
<section class="about-hero">
    <div class="container about-hero-grid">
        <div class="about-hero-text">
            <span class="eyebrow"><?= e(setting('site_name', 'Perihan Ateş')) ?></span>
            <h1><?= e($page['title']) ?></h1>
            <div class="ornament" aria-hidden="true"><span></span></div>
            <?php if (!empty($page['meta_description'])): ?>
            <p class="lead"><?= e($page['meta_description']) ?></p>
            <?php endif; ?>
        </div>
        <div class="about-portrait">
            <?php if (!empty($page['hero_image'])): ?>
            <img src="<?= e(url($page['hero_image'])) ?>" alt="<?= e(setting('site_name', 'Perihan Ateş')) ?> portresi">
            <?php else: ?>
            <svg viewBox="0 0 340 460" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <defs>
                    <linearGradient id="aboutSky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#2e2547"/>
                        <stop offset="0.6" stop-color="#5b3a6e"/>
                        <stop offset="1" stop-color="#c39a4b"/>
                    </linearGradient>
                    <radialGradient id="aboutGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0" stop-color="#e6d3a7" stop-opacity="0.7"/>
                        <stop offset="1" stop-color="#e6d3a7" stop-opacity="0"/>
                    </radialGradient>
                </defs>
                <rect width="340" height="460" fill="url(#aboutSky)"/>
                <circle cx="170" cy="190" r="150" fill="url(#aboutGlow)"/>
                <g fill="none" stroke="#e6d3a7" stroke-opacity="0.35">
                    <circle cx="170" cy="190" r="110"/>
                    <circle cx="170" cy="190" r="140" stroke-dasharray="2 10"/>
                </g>
                <?php /* Soyut siluet: bas ve omuzlar */ ?>
                <circle cx="170" cy="190" r="48" fill="#1d1730" opacity="0.85"/>
                <path d="M60 460 C60 340 110 270 170 270 C230 270 280 340 280 460 Z" fill="#1d1730" opacity="0.85"/>
                <g fill="#fbf7f0">
                    <circle cx="58" cy="70" r="1.800"/><circle cx="290" cy="56" r="1.400"/><circle cx="300" cy="150" r="2"/><circle cx="40" cy="200" r="1.300"/>
                </g>
            </svg>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (trim((string) $page['body']) !== ''): ?>
<section class="section">
    <div class="container narrow prose about-intro">
        <?php /* body panelden girilen HTML'dir; kaydedilirken temizlenir */ ?>
        <?= $page['body'] ?>
    </div>
</section>
<?php endif; ?>

<?php if ($timeline): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Hikâyem</span>
            <h2>Yolculuğum</h2>
        </div>
        <ol class="timeline">
            <?php foreach ($timeline as $item): ?>
            <li class="timeline-item">
                <span class="timeline-dot" aria-hidden="true"></span>
                <div class="timeline-card">
                    <span class="timeline-year"><?= e($item['year_label']) ?></span>
                    <h3><?= e($item['title']) ?></h3>
                    <?php if ($item['text']): ?><p><?= nl2br(e($item['text'])) ?></p><?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<?php if ($values): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Nasıl çalışırım?</span>
            <h2>Yaklaşımım</h2>
        </div>
        <div class="card-grid value-grid">
            <?php foreach ($values as $value): ?>
            <article class="card value-card">
                <span class="value-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$value['icon']] ?? $icons['circle'] ?></svg>
                </span>
                <h3><?= e($value['title']) ?></h3>
                <?php if ($value['text']): ?><p><?= nl2br(e($value['text'])) ?></p><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($quote !== ''): ?>
<section class="quote-band">
    <div class="container narrow">
        <div class="ornament" aria-hidden="true"><span></span></div>
        <blockquote>
            <p><?= e($quote) ?></p>
            <?php if ($quoteAuthor !== ''): ?><cite><?= e($quoteAuthor) ?></cite><?php endif; ?>
        </blockquote>
    </div>
</section>
<?php endif; ?>

<?= View::renderFile('partials/cta') ?>
