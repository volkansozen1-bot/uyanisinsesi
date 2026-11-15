<?php /** @var array<string, mixed> $block */ ?>
<section class="section">
    <div class="container narrow center">
        <?php if ($block['subtitle']): ?><span class="eyebrow"><?= e($block['subtitle']) ?></span><?php endif; ?>
        <h2><?= e($block['title']) ?></h2>
        <div class="ornament" aria-hidden="true"><span></span></div>
        <?php if ($block['body']): ?><p class="lead"><?= nl2br(e($block['body'])) ?></p><?php endif; ?>
        <?php if ($block['button_text'] && $block['button_url']): ?>
        <p><a class="btn btn-outline" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a></p>
        <?php endif; ?>
    </div>
</section>
