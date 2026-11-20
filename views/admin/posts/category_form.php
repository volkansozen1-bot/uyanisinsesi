<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/blog/kategoriler')) ?>">← Blog kategorileri</a></p>
<div class="page-title">
    <h1><?= $row ? 'Kategoriyi düzenle' : 'Yeni kategori' ?></h1>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Kategori</legend>
        <div class="field-grid">
            <?= $f->text('name', 'Ad', ['required' => true, 'maxlength' => 150]) ?>
            <?= $f->text('slug', 'Adres', ['slug_from' => 'name', 'maxlength' => 150, 'help' => 'Kategori sayfası: /blog/kategori/adres. Boş bırakılırsa addan üretilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
