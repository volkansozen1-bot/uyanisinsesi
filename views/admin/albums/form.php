<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var array<string, string> $types
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/albumler')) ?>">← Meditasyon</a></p>
<div class="page-title">
    <h1><?= $row ? 'Albümü düzenle' : 'Yeni albüm' ?></h1>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Albüm</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Ad', ['required' => true, 'maxlength' => 200, 'class' => 'wide']) ?>
            <?= $f->select('type', 'Tür', $types) ?>
            <?= $f->number('year', 'Yıl', ['min' => 1900, 'max' => (int) date('Y') + 2, 'placeholder' => date('Y')]) ?>
            <?= $f->number('track_count', 'Parça sayısı', ['min' => 1, 'max' => 999, 'help' => 'İsteğe bağlı.']) ?>
            <?= $f->url('listen_url', 'Dinleme bağlantısı', ['maxlength' => 255, 'help' => 'Spotify, YouTube Music, Apple Music gibi bir platformdaki adresi. https:// ile başlamalı.']) ?>
            <?= $f->image('cover_image', 'Kapak görseli', ['help' => 'Kare görsel önerilir (örnek: 1000×1000). JPG, PNG, WebP; en fazla 8 MB.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
            <?= $f->checkbox('is_active', 'Sitede göster') ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
