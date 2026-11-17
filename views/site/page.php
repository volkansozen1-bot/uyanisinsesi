<?php /** @var array<string, mixed> $page */ ?>
<header class="page-head page-cover">
    <?php if (!empty($page['hero_image'])): ?>
    <img class="page-cover-image" src="<?= e(url($page['hero_image'])) ?>" alt="">
    <?php endif; ?>
    <div class="container narrow">
        <h1><?= e($page['title']) ?></h1>
    </div>
</header>
<article class="section">
    <div class="container narrow prose">
        <?= $page['body'] ?>
    </div>
</article>
