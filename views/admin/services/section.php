<?php
/**
 * Hizmetin kitle bolumu (sekme) formu.
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
    <h1><?= $child ? 'Bölümü düzenle' : 'Yeni bölüm' ?></h1>
    <p class="muted"><?= e($service['title']) ?> · İki veya daha fazla bölüm varsa hizmet sayfasında sekme olarak görünür.</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Sekme</legend>
        <div class="field-grid">
            <?= $f->text('tab_label', 'Sekme adı', ['required' => true, 'maxlength' => 100, 'placeholder' => 'Kadınlar için', 'help' => 'Sekmede ve sayfa başındaki butonlarda görünür.']) ?>
            <?= $f->text('anchor', 'Çapa', ['maxlength' => 50, 'slug_from' => 'tab_label', 'placeholder' => 'kadinlar-icin', 'help' => 'Bu bölüme doğrudan bağlantı: /hizmetler/' . $service['slug'] . '#kadinlar-icin. Küçük harf, rakam ve tire. Boş bırakılırsa sekme adından üretilir.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>İçerik</legend>
        <div class="field-grid">
            <?= $f->text('eyebrow', 'Üst başlık', ['maxlength' => 150, 'placeholder' => 'Kadınlar için bireysel seans', 'help' => 'Başlığın üstündeki küçük, aralıklı yazı. İsteğe bağlı.']) ?>
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->richtext('body', 'Metin') ?>
            <?= $f->image('image', 'Görsel', ['help' => 'Metnin yanında görünür. Dikey (portre) görsel en iyi sonucu verir. JPG, PNG, WebP veya GIF; en fazla 8 MB. Görsel yoksa süsleme çizimi gösterilir.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Odak alanları</legend>
        <div class="field-grid">
            <?= $f->text('focus_title', 'Odak başlığı', ['maxlength' => 200, 'placeholder' => 'Birlikte çalışabileceğimiz alanlar', 'help' => 'Boşsa "Birlikte çalışabileceğimiz alanlar" yazar.']) ?>
            <?= $f->textarea('focus_items', 'Odak maddeleri', ['rows' => 7, 'class' => 'wide', 'help' => 'Her satır bir madde olarak listelenir. Boş satırlar atlanır. Hiç madde yoksa bu liste gösterilmez.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir; ilk bölüm açık gelir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button> <a class="btn-link" href="<?= e(url($backUrl)) ?>">Vazgeç</a></div>
</form>
