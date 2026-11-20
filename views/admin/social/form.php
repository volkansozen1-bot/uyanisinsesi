<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var array<string, string> $platforms
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/sosyal-medya')) ?>">← Sosyal Medya</a></p>
<div class="page-title">
    <h1><?= $row ? 'Bağlantıyı düzenle' : 'Yeni bağlantı' ?></h1>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Bağlantı</legend>
        <div class="field-grid">
            <?= $f->select('platform', 'Platform', $platforms, ['required' => true, 'help' => 'Listede yoksa "Diğer" seçip görünen adı kendiniz yazın.']) ?>
            <?= $f->text('label', 'Görünen ad', ['maxlength' => 60, 'help' => 'Boş bırakırsanız platformun adı kullanılır. "Diğer" seçtiyseniz zorunludur.']) ?>
            <?= $f->url('url', 'Adres', ['required' => true, 'maxlength' => 255, 'class' => 'wide', 'help' => 'Profil adresiniz. https:// ile başlamalı.']) ?>
            <?= $f->image('icon', 'İkon', ['help' => 'Platformun resmî marka sayfasından indirdiğiniz ikonu yükleyin (kare, PNG veya WebP). Yüklenmezse platformun adı yazı olarak görünür.']) ?>
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
