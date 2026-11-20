<?php
/**
 * @var array<string, mixed> $block
 * @var array<string, mixed>|null $stat
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/ana-sayfa/' . $block['id'])) ?>">← Rakamlar bloğu</a></p>
<div class="page-title">
    <h1><?= $stat ? 'Rakamı düzenle' : 'Yeni rakam' ?></h1>
    <p class="muted">Sitede büyük sayı ve altında etiket olarak görünür. Örnek: <strong>250+</strong> Danışan</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Rakam</legend>
        <div class="field-grid">
            <?= $f->text('label', 'Etiket', ['required' => true, 'maxlength' => 150, 'help' => 'Sayının altındaki yazı. Örnek: Yıllık deneyim']) ?>
            <?= $f->number('value', 'Sayı', ['required' => true, 'min' => 0, 'help' => 'Yalnızca tam sayı. Sayfada sıfırdan bu sayıya kadar sayılır.']) ?>
            <?= $f->text('suffix', 'Ek', ['maxlength' => 20, 'help' => 'Sayının hemen sağındaki işaret. Örnek: + ya da %']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
