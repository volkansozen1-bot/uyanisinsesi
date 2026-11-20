<?php
/**
 * @var array<string, mixed> $page
 * @var array<string, mixed>|null $item
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 */
use App\Controllers\Admin\PageAdminController;

$f = new App\Core\Form($values, $errors);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/sayfalar/' . $page['id'] . '#yaklasim')) ?>">← <?= e($page['title']) ?></a></p>
<div class="page-title">
    <h1><?= $item ? 'Yaklaşım kartını düzenle' : 'Yeni yaklaşım kartı' ?></h1>
    <p class="muted">"Yaklaşımım" bölümündeki ikonlu kart.</p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Kart</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->select('icon', 'İkon', PageAdminController::ICONS) ?>
            <?= $f->textarea('text', 'Metin', ['maxlength' => 2000, 'rows' => 4, 'class' => 'wide']) ?>
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
