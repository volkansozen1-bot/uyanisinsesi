<?php
/**
 * Ic sayfa basligi.
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $lead
 */
?>
<header class="page-head">
    <div class="container narrow">
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
        <h1><?= e($title) ?></h1>
        <?php if (!empty($lead)): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
    </div>
</header>
