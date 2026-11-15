<?php /* Veritabanina ihtiyac duymayan, tek basina calisan sayfa */ ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sayfa bulunamadı</title>
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
</head>
<body>
    <main class="section">
        <div class="container narrow center">
            <h1>Sayfa bulunamadı</h1>
            <p>Aradığınız sayfa taşınmış ya da kaldırılmış olabilir.</p>
            <p><a class="btn" href="<?= e(url('/')) ?>">Ana sayfaya dön</a></p>
        </div>
    </main>
</body>
</html>
