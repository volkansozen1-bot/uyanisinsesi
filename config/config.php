<?php
declare(strict_types=1);

use App\Core\Env;

// Degerler .env dosyasindan gelir; .env.example dosyasini .env olarak kopyalayip doldurun.
return [
    'app' => [
        'url'      => Env::get('APP_URL', 'http://localhost:8000'),
        'debug'    => Env::get('APP_DEBUG', 'false') === 'true',
        'timezone' => Env::get('APP_TIMEZONE', 'Europe/Istanbul'),
    ],
    'db' => [
        'dsn'  => Env::get('DB_DSN', ''), // bos birakilirsa asagidaki MySQL bilgileri kullanilir
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::get('DB_PORT', '3306'),
        'name' => Env::get('DB_NAME', 'uyanisinsesi'),
        'user' => Env::get('DB_USER', 'root'),
        'pass' => Env::get('DB_PASS', ''),
    ],
    'mail' => [
        'host'      => Env::get('MAIL_HOST', ''),
        'port'      => (int) Env::get('MAIL_PORT', '587'),
        'user'      => Env::get('MAIL_USER', ''),
        'pass'      => Env::get('MAIL_PASS', ''),
        'from'      => Env::get('MAIL_FROM', ''),
        'from_name' => Env::get('MAIL_FROM_NAME', ''),
    ],
];
