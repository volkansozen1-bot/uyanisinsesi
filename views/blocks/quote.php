<?php /** @var array<string, mixed> $block */ ?>
<section class="quote-band">
    <div class="container narrow">
        <div class="ornament" aria-hidden="true"><span></span></div>
        <blockquote>
            <p><?= e($block['subtitle']) ?></p>
            <?php if ($block['title']): ?><cite><?= e($block['title']) ?></cite><?php endif; ?>
        </blockquote>
    </div>
</section>
