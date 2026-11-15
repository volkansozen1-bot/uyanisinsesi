<?php
/** @var string $content */
$siteName = setting('site_name', 'Uyanışın Sesi');
$title    = isset($pageTitle) && $pageTitle !== $siteName ? $pageTitle . ' | ' . $siteName : $siteName;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="tr_TR">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Mulish:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#icerik">İçeriğe geç</a>
    <?= App\Core\View::renderFile('partials/header') ?>
    <main id="icerik">
        <?= $content ?>
    </main>
    <?= App\Core\View::renderFile('partials/newsletter') ?>
    <?= App\Core\View::renderFile('partials/footer') ?>
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
