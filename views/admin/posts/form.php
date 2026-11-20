<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var array<int, string> $categories
 * @var string $action
 * @var string $now
 */
use App\Controllers\Admin\PostAdminController;

$f = new App\Core\Form($values, $errors);
$status = $row ? PostAdminController::statusOf($row, $now) : null;
?>
<p class="back-link"><a href="<?= e(url('/yonetim/blog')) ?>">← Blog</a></p>
<div class="page-title page-title-row">
    <div>
        <h1><?= $row ? 'Yazıyı düzenle' : 'Yeni yazı' ?></h1>
        <?php if ($status): ?><p><span class="tag <?= e($status[1]) ?>"><?= e($status[0]) ?></span></p><?php endif; ?>
    </div>
    <?php if ($status && $status[0] === 'Yayında'): ?>
    <a class="btn btn-outline" href="<?= e(url('/blog/' . $row['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
    <?php endif; ?>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Yazı</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 255, 'class' => 'wide']) ?>
            <?= $f->text('slug', 'Adres', ['slug_from' => 'title', 'maxlength' => 200, 'help' => 'Sayfa adresi: /blog/adres. Boş bırakılırsa başlıktan üretilir. Yayınlandıktan sonra değiştirirseniz eski bağlantılar çalışmaz.']) ?>
            <?= $f->select('category_id', 'Kategori', $categories, ['empty' => 'Kategorisiz']) ?>
            <?= $f->textarea('excerpt', 'Özet', ['maxlength' => 500, 'rows' => 3, 'class' => 'wide', 'help' => 'Blog listesinde ve yazının başında görünen kısa giriş. En fazla 500 karakter.']) ?>
            <?= $f->richtext('body', 'Metin') ?>
            <?= $f->image('cover_image', 'Kapak görseli', ['help' => 'Blog listesinde ve yazının başında görünür. Yatay görsel önerilir. JPG, PNG, WebP veya GIF; en fazla 8 MB.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Yayın</legend>
        <div class="field-grid">
            <?= $f->select('status', 'Durum', ['draft' => 'Taslak (sitede görünmez)', 'published' => 'Yayında']) ?>
            <?= $f->datetime('published_at', 'Yayın tarihi', ['help' => 'Yayında seçip boş bırakırsanız şimdiki zaman yazılır. İleri bir tarih seçerseniz yazı o zamana kadar sitede görünmez.']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Arama motorları (SEO)</legend>
        <div class="field-grid">
            <?= $f->text('meta_title', 'Sayfa başlığı', ['maxlength' => 200, 'class' => 'wide', 'help' => 'Tarayıcı sekmesinde ve Google sonuçlarında görünür. Boşsa yazı başlığı kullanılır.']) ?>
            <?= $f->textarea('meta_description', 'Açıklama', ['maxlength' => 300, 'rows' => 2, 'class' => 'wide', 'help' => 'Google sonuçlarında başlığın altında görünen 1-2 cümle. Boşsa özet kullanılır.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
