<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var array<int, string> $services
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/yorumlar')) ?>">← Yorumlar</a></p>
<div class="page-title">
    <h1><?= $row ? 'Yorumu düzenle' : 'Yeni yorum' ?></h1>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Yorum</legend>
        <div class="field-grid">
            <?= $f->text('name', 'Ad', ['required' => true, 'maxlength' => 150, 'help' => 'Yayın izni alınmış olmalı. İstenirse yalnızca baş harfler: "A. K."']) ?>
            <?= $f->select('rating', 'Puan', [5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']) ?>
            <?= $f->textarea('text', 'Yorum metni', ['required' => true, 'maxlength' => 2000, 'rows' => 5, 'class' => 'wide']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->select('service_id', 'Hizmet', $services, ['empty' => 'Genel (ana sayfa)', 'help' => 'Bir hizmet seçilirse o hizmetin sayfasında da görünür.']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
            <?= $f->checkbox('is_active', 'Sitede göster') ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
