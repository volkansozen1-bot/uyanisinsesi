<?php
/** @var string $content */
$siteName = setting('site_name', 'Perihan Ateş');
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
    <?php
    // Kanonik adres: sorgu dizesi atilir; yalnizca blog sayfalamasinda ?sayfa=N korunur
    $seoPath      = current_path();
    $canonicalUrl = url($seoPath);
    $seoPageNo    = (int) ($_GET['sayfa'] ?? 1);
    if ($seoPageNo > 1 && ($seoPath === '/blog' || str_starts_with($seoPath, '/blog/kategori/'))) {
        $canonicalUrl .= '?sayfa=' . $seoPageNo;
    }
    $ogImageUrl = !empty($ogImage) ? url((string) $ogImage) : url('assets/img/og-default.png');
    ?>
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <?php if (!empty($metaDescription)): ?>
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <meta property="og:image" content="<?= e($ogImageUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php if ($seoPath === '/'): ?>
    <script type="application/ld+json"><?= json_encode(
        ['@context' => 'https://schema.org', '@type' => 'Person', 'name' => $siteName, 'url' => url('/')],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
    ) ?></script>
    <?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/logo-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
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
