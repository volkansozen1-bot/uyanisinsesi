<?php
declare(strict_types=1);

/*
 * Yonetim paneli sol menusu. Yeni modul eklemek icin tek satir:
 *   ['label' => 'Hizmetler', 'url' => '/yonetim/hizmetler', 'icon' => 'services'],
 *
 * label: menude gorunen ad
 * url:   panel ici yol; null ise oge pasif ("yakinda") gorunur
 * icon:  views/admin/partials/icon.php icindeki ikon adi
 * badge: (istege bagli) sayi donduren fonksiyon; 0'dan buyukse rozet olarak gosterilir
 * group: (istege bagli) bu ogeden once ara baslik
 */

use App\Core\Database;

return [
    ['label' => 'Panel',            'url' => '/yonetim',          'icon' => 'home'],
    ['label' => 'Gelen Mesajlar',   'url' => '/yonetim/mesajlar', 'icon' => 'mail',
        'badge' => static function (): int {
            return (int) (Database::one('SELECT COUNT(*) AS n FROM contact_messages WHERE is_read = 0')['n'] ?? 0);
        }],
    ['label' => 'Bülten Aboneleri', 'url' => '/yonetim/aboneler', 'icon' => 'users'],

    ['label' => 'Ana Sayfa', 'url' => null, 'icon' => 'layout',  'group' => 'İçerik'],
    ['label' => 'Sayfalar',  'url' => null, 'icon' => 'file'],
    ['label' => 'Hizmetler', 'url' => null, 'icon' => 'star'],
    ['label' => 'Eğitimler', 'url' => null, 'icon' => 'book'],
    ['label' => 'Blog',      'url' => null, 'icon' => 'pen'],
    ['label' => 'Albümler',  'url' => null, 'icon' => 'music'],
    ['label' => 'Yorumlar',  'url' => null, 'icon' => 'quote'],
    ['label' => 'Medya',     'url' => null, 'icon' => 'image'],

    ['label' => 'Ayarlar',   'url' => '/yonetim/ayarlar', 'icon' => 'settings', 'group' => 'Hesap'],
    ['label' => 'Parolam',   'url' => '/yonetim/parola',  'icon' => 'key'],
];
