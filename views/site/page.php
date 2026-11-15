<?php /** @var array<string, mixed> $page */ ?>
<header class="page-head">
    <div class="container narrow">
        <h1><?= e($page['title']) ?></h1>
    </div>
</header>
<article class="section">
    <div class="container narrow prose">
        <?php /* body panelden girilen HTML'dir; kaydedilirken temizlenir (HTMLPurifier), burada oldugu gibi basilir */ ?>
        <?= $page['body'] ?>
    </div>
</article>
