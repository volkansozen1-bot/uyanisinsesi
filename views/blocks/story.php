<?php /** @var array<string, mixed> $block */ ?>
<section class="section">
    <div class="container split">
        <div class="split-media">
            <?php if ($block['image']): ?>
            <img src="<?= e(url($block['image'])) ?>" alt="" loading="lazy">
            <?php endif; ?>
        </div>
        <div>
            <?php if ($block['subtitle']): ?><span class="eyebrow"><?= e($block['subtitle']) ?></span><?php endif; ?>
            <h2><?= e($block['title']) ?></h2>
            <?php if ($block['body']): ?><p><?= nl2br(e($block['body'])) ?></p><?php endif; ?>
            <?php if ($block['button_text'] && $block['button_url']): ?>
            <p><a class="btn btn-outline" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
