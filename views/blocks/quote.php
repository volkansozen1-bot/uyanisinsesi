<?php /** @var array<string, mixed> $block */ ?>
<section class="quote-band">
    <div class="container narrow">
        <blockquote>
            <p><?= e($block['subtitle']) ?></p>
            <?php if ($block['title']): ?><cite><?= e($block['title']) ?></cite><?php endif; ?>
        </blockquote>
    </div>
</section>
