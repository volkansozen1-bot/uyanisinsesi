<?php
declare(strict_types=1);

use App\Controllers\Admin\TestimonialController;

/** @var App\Core\Router $router */
// Ornek icerik modulu rotalari. Diger moduller ayni kalibi izler:
//   GET  /yonetim/<modul>            liste
//   GET  /yonetim/<modul>/yeni       bos form       POST ayni adres: kaydet
//   GET  /yonetim/<modul>/{id}       duzenle formu  POST ayni adres: guncelle
//   POST /yonetim/<modul>/{id}/sil   sil
$router->get('/yonetim/yorumlar', [TestimonialController::class, 'index']);
$router->get('/yonetim/yorumlar/yeni', [TestimonialController::class, 'create']);
$router->post('/yonetim/yorumlar/yeni', [TestimonialController::class, 'store']);
$router->get('/yonetim/yorumlar/{id}', [TestimonialController::class, 'edit']);
$router->post('/yonetim/yorumlar/{id}', [TestimonialController::class, 'update']);
$router->post('/yonetim/yorumlar/{id}/sil', [TestimonialController::class, 'delete']);
