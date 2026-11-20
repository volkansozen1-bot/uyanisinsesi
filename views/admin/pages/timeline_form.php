<?php
/**
 * @var array<string, mixed> $page
 * @var array<string, mixed>|null $item
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/sayfalar/' . $page['id'] . '#yolculuk')) ?>">← <?= e($page['title']) ?></a></p>
<div class="page-title">
    <h1><?= $item ? 'Yolculuk adımını düzenle' : 'Yeni yolculuk adımı' ?></h1>
    <p class="muted">"Yolculuğum" zaman çizelgesindeki bir adım.</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Adım</legend>
        <div class="field-grid">
            <?= $f->text('year_label', 'Yıl / dönem', ['required' => true, 'maxlength' => 50, 'help' => 'Serbest metin: 2012, 2015-2018 ya da Bugün gibi.']) ?>
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->textarea('text', 'Metin', ['maxlength' => 2000, 'rows' => 4, 'class' => 'wide']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
