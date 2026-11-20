<?php
/**
 * Egitim modulu formu.
 * @var array<string, mixed> $course
 * @var array<string, mixed>|null $child
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 * @var string $backUrl
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url($backUrl)) ?>">← <?= e($course['title']) ?></a></p>
<div class="page-title">
    <h1><?= $child ? 'Modülü düzenle' : 'Yeni modül' ?></h1>
    <p class="muted"><?= e($course['title']) ?> · "Adım adım içerik" listesinde bir adım</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Modül</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200, 'class' => 'wide', 'placeholder' => 'Gölgeyle tanışma']) ?>
            <?= $f->textarea('description', 'Açıklama', ['maxlength' => 5000, 'rows' => 5, 'class' => 'wide', 'help' => 'Düz metin. Satır başları sitede korunur.']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir; numaralar sıraya göre otomatik verilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button> <a class="btn-link" href="<?= e(url($backUrl)) ?>">Vazgeç</a></div>
</form>
