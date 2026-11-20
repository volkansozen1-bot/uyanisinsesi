<?php
/**
 * Sik sorulan soru formu (hizmete bagli).
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
    <h1><?= $child ? 'Soruyu düzenle' : 'Yeni soru' ?></h1>
    <p class="muted"><?= e($service['title']) ?> · Sık sorulan sorular</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Soru ve cevap</legend>
        <div class="field-grid">
            <?= $f->text('question', 'Soru', ['required' => true, 'maxlength' => 500, 'class' => 'wide']) ?>
            <?= $f->textarea('answer', 'Cevap', ['required' => true, 'maxlength' => 5000, 'rows' => 6, 'class' => 'wide', 'help' => 'Düz metin. Satır başları sitede korunur.']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button> <a class="btn-link" href="<?= e(url($backUrl)) ?>">Vazgeç</a></div>
</form>
