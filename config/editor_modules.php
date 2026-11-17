<?php
declare(strict_types=1);

return [
    'hizmetler' => ['services', 'Hizmetler', [
        'title' => ['Başlık', 200],
        'slug' => ['Adres', 150],
        'summary' => ['Özet', 500],
        'body' => ['İçerik', 0],
        'image' => ['Görsel adresi', 255],
    ]],
    'egitimler' => ['courses', 'Eğitimler', [
        'title' => ['Başlık', 200],
        'slug' => ['Adres', 150],
        'summary' => ['Özet', 500],
        'body' => ['İçerik', 0],
        'image' => ['Görsel adresi', 255],
        'start_date' => ['Başlangıç tarihi', 10],
    ]],
    'blog' => ['posts', 'Blog', [
        'title' => ['Başlık', 255],
        'slug' => ['Adres', 200],
        'excerpt' => ['Özet', 500],
        'body' => ['İçerik', 0],
        'cover_image' => ['Görsel adresi', 255],
    ]],
    'albumler' => ['albums', 'Albümler', [
        'title' => ['Başlık', 200],
        'cover_image' => ['Kapak adresi', 255],
        'listen_url' => ['Dinleme bağlantısı', 255],
    ]],
    'yorumlar' => ['testimonials', 'Yorumlar', [
        'name' => ['İsim', 150],
        'text' => ['Yorum', 0],
        'rating' => ['Puan (1–5)', 1],
    ]],
];
