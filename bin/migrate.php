<?php
declare(strict_types=1);

// Bekleyen veritabani guncellemelerini calistirir.
// Kullanim (proje klasorunde): php bin/migrate.php
//   XAMPP: C:\xampp\php\php.exe bin\migrate.php

if (PHP_SAPI !== 'cli') {
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$result = App\Core\Migrator::run();

foreach ($result['applied'] as $v) {
    echo "Uygulandi : $v\n";
}
foreach ($result['skipped'] as $v) {
    echo "Zaten var  : $v\n";
}
if ($result['error'] !== null) {
    fwrite(STDERR, "HATA       : {$result['error']}\n");
    exit(1);
}
echo $result['applied'] ? "Tamam.\n" : "Bekleyen guncelleme yok.\n";
