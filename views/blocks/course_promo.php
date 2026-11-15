<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$course = $data['course'] ?? null;
if (!$course) {
    return;
}
$months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
$start  = $course['start_date'] ? strtotime((string) $course['start_date']) : null;
$until  = $course['countdown_until'] ? strtotime((string) $course['countdown_until']) : null;
?>
<section class="section section-dark promo">
    <div class="container narrow">
        <span class="eyebrow"><?= e($block['subtitle'] ?: 'Yeni dönem') ?></span>
        <h2><?= e($block['title'] ?: $course['title']) ?></h2>
        <?php if ($course['summary']): ?><p class="lead"><?= e($course['summary']) ?></p><?php endif; ?>
        <?php if ($start): ?>
        <p class="promo-date">Başlangıç: <?= (int) date('j', $start) ?> <?= $months[(int) date('n', $start)] ?> <?= date('Y', $start) ?></p>
        <?php endif; ?>
        <?php if ($until && $until > time()): ?>
        <?php /* Kalan sure sunucuda hesaplanip yazilir; JS her saniye gunceller */ ?>
        <?php $left = $until - time(); ?>
        <div class="countdown" data-countdown="<?= e(date('c', $until)) ?>" role="timer" aria-label="Başlangıca kalan süre">
            <div><strong data-unit="days"><?= intdiv($left, 86400) ?></strong><span>Gün</span></div>
            <div><strong data-unit="hours"><?= intdiv($left % 86400, 3600) ?></strong><span>Saat</span></div>
            <div><strong data-unit="minutes"><?= intdiv($left % 3600, 60) ?></strong><span>Dakika</span></div>
            <div><strong data-unit="seconds"><?= $left % 60 ?></strong><span>Saniye</span></div>
        </div>
        <?php endif; ?>
        <p><a class="btn" href="<?= e(url($block['button_url'] ?: '/egitimler/' . $course['slug'])) ?>"><?= e($block['button_text'] ?: 'Eğitimi İncele') ?></a></p>
    </div>
</section>
