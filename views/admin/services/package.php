<?php
/**
 * Hizmet paketi (ucret karti) formu.
 * @var array<string, mixed> $service
 * @var array<string, mixed>|null $child
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 * @var string $backUrl
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url($backUrl)) ?>">← <?= e($service['title']) ?></a></p>
<div class="page-title">
    <h1><?= $child ? 'Paketi düzenle' : 'Yeni paket' ?></h1>
    <p class="muted"><?= e($service['title']) ?> · Ücretler bölümünde bir kart</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Paket</legend>
        <div class="field-grid">
            <?= $f->text('name', 'Paket adı', ['required' => true, 'maxlength' => 150, 'placeholder' => 'Tek Seans']) ?>
            <?= $f->text('price', 'Fiyat (TL)', ['maxlength' => 20, 'placeholder' => '7500', 'help' => 'Yalnızca rakam: 7500 ya da 7.500,50. Boş bırakılırsa sitede "Fiyat için iletişime geçin" yazar.']) ?>
            <?= $f->textarea('description', 'Açıklama', ['maxlength' => 500, 'rows' => 3, 'class' => 'wide', 'help' => 'Kartta fiyatın altında görünen kısa açıklama. En fazla 500 karakter.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Buton</legend>
        <div class="field-grid">
            <?= $f->text('button_text', 'Buton yazısı', ['maxlength' => 100, 'placeholder' => 'Bilgi Al', 'help' => 'Boşsa "Bilgi Al" yazar.']) ?>
            <?= $f->text('button_url', 'Buton bağlantısı', ['maxlength' => 255, 'placeholder' => 'https://', 'help' => 'Ödeme linki (PayTR, iyzico vb.) buraya yapıştırılır; https:// ile başlamalı. Site içi bir sayfa için /iletisim gibi yazın. Boş bırakılırsa buton WhatsApp\'a (numara yoksa iletişim sayfasına) gider.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button> <a class="btn-link" href="<?= e(url($backUrl)) ?>">Vazgeç</a></div>
</form>
