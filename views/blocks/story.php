<?php /** @var array<string, mixed> $block */ ?>
<section class="section home-story">
    <div class="container <?= $block['image'] ? 'split' : 'narrow' ?>">
        <?php if ($block['image']): ?>
        <div class="split-media">
            <img src="<?= e(url($block['image'])) ?>" alt="" loading="lazy">
        </div>
        <?php endif; ?>
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
