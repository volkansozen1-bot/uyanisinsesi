<?php /** @var array<string, mixed> $block */ ?>
<section class="hero"<?php if ($block['image']): ?> style="background-image:url('<?= e(url($block['image'])) ?>')"<?php endif; ?>>
    <div class="container narrow">
        <h1><?= e($block['title']) ?></h1>
        <?php if ($block['subtitle']): ?><p class="lead"><?= e($block['subtitle']) ?></p><?php endif; ?>
        <?php if ($block['button_text'] && $block['button_url']): ?>
        <a class="btn" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a>
        <?php endif; ?>
    </div>
</section>
