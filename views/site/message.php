<?php
/** @var string $heading */
/** @var string $text */
?>
<section class="section">
    <div class="container narrow">
        <h1><?= e($heading) ?></h1>
        <p><?= e($text) ?></p>
        <p><a class="btn" href="<?= e(url('/')) ?>">Ana sayfaya dön</a></p>
    </div>
</section>
