<?php
/** @var array<string, mixed> $block */
/** @var array<string, mixed> $data */
$stats = $data['stats'] ?? [];
if (!$stats) {
    return;
}
?>
<section class="section">
    <div class="container">
        <?php if ($block['title']): ?>
        <div class="section-head"><h2><?= e($block['title']) ?></h2></div>
        <?php endif; ?>
        <dl class="stats">
            <?php foreach ($stats as $stat): ?>
            <div>
                <?php /* Gercek sayi HTML'de durur; JS yalnizca sayma animasyonu ekler */ ?>
                <dd><span data-count="<?= (int) $stat['value'] ?>"><?= (int) $stat['value'] ?></span><?= e($stat['suffix']) ?></dd>
                <dt><?= e($stat['label']) ?></dt>
            </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
