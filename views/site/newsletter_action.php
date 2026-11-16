<?php
/** @var string $heading */
/** @var string $text */
/** @var string $action site ici yol */
/** @var string $button */
/** @var string|null $error */
?>
<header class="page-head">
    <div class="container narrow">
        <h1><?= e($heading) ?></h1>
    </div>
</header>
<section class="section">
    <div class="container narrow center">
        <?php if (!empty($error)): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <p class="lead"><?= e($text) ?></p>
        <form method="post" action="<?= e(url($action)) ?>">
            <?= App\Core\Csrf::field() ?>
            <p><button type="submit" class="btn"><?= e($button) ?></button></p>
        </form>
        <p><a href="<?= e(url('/')) ?>">Ana sayfaya dön</a></p>
    </div>
</section>
