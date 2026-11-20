<?php
/** Ekranin sag kenarinda sabit sosyal medya seridi (masaustu). Baglanti yoksa hic gorunmez. */
$social = social_links();
if (!$social) {
    return;
}
?>
<nav class="social-float" aria-label="Sosyal medya">
    <span class="social-float-line" aria-hidden="true"></span>
    <?php foreach ($social as $link): ?>
    <a class="social-btn social-<?= e($link['platform']) ?>" href="<?= e($link['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($link['label']) ?>">
        <?php if ($link['icon']): ?>
        <img src="<?= e(url($link['icon'])) ?>" alt="" width="22" height="22" loading="lazy">
        <?php else: ?>
        <?= social_glyph($link['platform']) ?>
        <?php endif; ?>
        <span class="social-float-label"><?= e($link['label']) ?></span>
    </a>
    <?php endforeach; ?>
    <span class="social-float-line" aria-hidden="true"></span>
</nav>
