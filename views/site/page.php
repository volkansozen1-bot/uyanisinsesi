<?php /** @var array<string, mixed> $page */ ?>
<article class="section">
    <div class="container narrow prose">
        <h1><?= e($page['title']) ?></h1>
        <?php /* body panelden girilen HTML'dir; kaydedilirken temizlenir (HTMLPurifier), burada oldugu gibi basilir */ ?>
        <?= $page['body'] ?>
    </div>
</article>
