<?php
/** @var string $heading */
/** @var string $text */
?>
<header class="page-head">
    <div class="container narrow">
        <h1><?= e($heading) ?></h1>
    </div>
</header>
<section class="section">
    <div class="container narrow center">
        <p class="lead"><?= e($text) ?></p>
        <p><a class="btn" href="<?= e(url('/')) ?>">Ana sayfaya dön</a></p>
    </div>
</section>
