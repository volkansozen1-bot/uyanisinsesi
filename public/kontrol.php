<?php
// Kurulum kontrol sayfasi. Yalnizca ayni bilgisayardan (localhost) acilir.
// Adres: http://localhost/uyanisinsesi/public/kontrol.php
// Canli siteye YUKLEMEYIN; kurulum bitince silebilirsiniz.

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
$root = dirname(__DIR__);
$rows = [];
$add  = static function (string $label, bool $ok, string $detail) use (&$rows): void {
    $rows[] = [$label, $ok, $detail];
};

// 1. PHP
$add('PHP sürümü', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION . ' (en az 8.0 gerekli)');
$add('pdo_mysql eklentisi', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'yüklü' : 'php.ini içinde extension=pdo_mysql açılmalı');
$add('mbstring eklentisi', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'yüklü' : 'php.ini içinde extension=mbstring açılmalı');

// 2. Dosyalar guncel mi (yonetim paneliyle gelen app/Core/Auth.php var mi)
$isNew = is_file($root . '/app/Core/Auth.php');
$add('Dosyalar güncel', $isNew, $isNew ? 'güncel sürüm' : 'ESKİ sürüm: app/Core/Auth.php yok. Dosyaları GitHub\'daki son sürümle değiştirin.');
$add('.htaccess', is_file(__DIR__ . '/.htaccess'), is_file(__DIR__ . '/.htaccess') ? 'var' : 'public/.htaccess eksik (gizli dosya kopyalanmamış olabilir)');

// 3. Veritabani
$env = [];
if (is_file($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line !== '' && $line[0] !== '#' && strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim(trim($v), "\"'");
        }
    }
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$name = $env['DB_NAME'] ?? 'uyanisinsesi';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$add('.env dosyası', true, is_file($root . '/.env') ? 'var' : 'yok (varsayılanlar kullanılıyor: veritabanı "' . $name . '", kullanıcı "' . $user . '", parola boş)');

// database/schema.sql icindeki tum CREATE TABLE adlari (schema.sql degisince burayi da guncelleyin)
$expected = [
    'admins', 'login_attempts', 'settings', 'menu_items', 'media', 'pages', 'about_timeline', 'about_values',
    'home_blocks', 'services', 'service_sections', 'service_packages', 'courses', 'course_modules', 'course_plans',
    'post_categories', 'posts', 'albums', 'testimonials', 'faqs', 'stats', 'subscribers', 'contact_messages',
];
// Eksik tablo => ice aktarilmasi gereken migration dosyasi
$migrationFor = [
    'service_sections' => '001_service_sections.sql',
    'login_attempts'   => '002_admin.sql',
    'about_timeline'   => '003_pages.sql',
    'about_values'     => '003_pages.sql',
];

try {
    // DB_DSN (ornek: sqlite:...) tanimliysa o kullanilir; yoksa MySQL
    $dsn = ($env['DB_DSN'] ?? '') !== '' ? $env['DB_DSN'] : "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $add('Veritabanı bağlantısı', true, '"' . $name . '" veritabanına bağlanıldı');

    $tables = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
        ? $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN)
        : $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tables  = array_map('strtolower', $tables);
    $missing = array_values(array_diff($expected, $tables));

    $needed = [];
    foreach ($missing as $t) {
        if (isset($migrationFor[$t])) {
            $needed[$migrationFor[$t]] = true;
        }
    }
    // pages.template sutunu 003_pages.sql ile eklendi
    if (in_array('pages', $tables, true)) {
        try {
            $pdo->query('SELECT template FROM pages LIMIT 1');
        } catch (Throwable $e) {
            $needed['003_pages.sql'] = true;
            $noTemplate = true;
        }
    }
    ksort($needed);

    if ($missing === []) {
        $detail = count($expected) . ' tablonun hepsi var';
    } else {
        $detail = count($missing) . ' tablo eksik (' . implode(', ', $missing) . ')';
        $other  = array_diff($missing, array_keys($migrationFor));
        if ($other !== []) {
            $detail .= ': phpMyAdmin\'de database/schema.sql dosyasını içe aktarın';
        } elseif ($needed !== []) {
            $detail .= ': phpMyAdmin\'de sırayla şu dosyaları içe aktarın: database/migrations/' . implode(', database/migrations/', array_keys($needed));
        }
    }
    $add('Tablolar (schema.sql)', $missing === [], $detail);
    if (!empty($noTemplate)) {
        $add('pages.template sütunu', false, 'yok: database/migrations/003_pages.sql dosyasını içe aktarın');
    }

    if (in_array('home_blocks', $tables, true)) {
        $blocks = (int) $pdo->query('SELECT COUNT(*) FROM home_blocks')->fetchColumn();
        $add('Başlangıç verisi (seed.sql)', $blocks > 0, $blocks . ' ana sayfa bölümü' . ($blocks === 0 ? ': phpMyAdmin\'de database/seed.sql dosyasını içe aktarın' : ''));
    }

    if (in_array('admins', $tables, true)) {
        $admins = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        $add('Yönetici hesabı', $admins > 0, $admins > 0
            ? $admins . ' yönetici var'
            : 'henüz yönetici yok: ilk yöneticiyi oluşturmak için bu bilgisayardan yonetim/kurulum adresini açın');
        $setupLink = $admins === 0;
    }
} catch (Throwable $e) {
    $add('Veritabanı bağlantısı', false, $e->getMessage());
}

$allOk = !in_array(false, array_column($rows, 1), true);
$setupLink = $setupLink ?? false;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>Kurulum kontrolü</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 52rem; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ccc; padding: .5rem .75rem; text-align: left; vertical-align: top; }
        .ok { color: #1d6b3a; font-weight: 700; }
        .no { color: #a32020; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Kurulum kontrolü</h1>
    <table>
        <tr><th>Kontrol</th><th>Durum</th><th>Ayrıntı</th></tr>
        <?php foreach ($rows as [$label, $ok, $detail]): ?>
        <tr>
            <td><?= htmlspecialchars($label) ?></td>
            <td class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? 'TAMAM' : 'SORUN' ?></td>
            <td><?= htmlspecialchars($detail) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if ($setupLink): ?>
    <p>İlk yönetici: <a href="yonetim/kurulum">yonetim/kurulum</a></p>
    <?php endif; ?>
    <p><?= $allOk ? 'Her şey hazır. <a href="./">Siteyi aç</a>' : 'Kırmızı satırları düzeltip sayfayı yenileyin.' ?></p>
</body>
</html>
