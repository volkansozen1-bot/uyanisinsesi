<?php
/**
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var bool $isAbout
 * @var array<int, string> $menuLinks
 * @var array<int, array<string, mixed>> $timeline
 * @var array<int, array<string, mixed>> $aboutValues
 * @var string $action
 */
use App\Controllers\Admin\PageAdminController;

$f = new App\Core\Form($values, $errors);
$base = $row ? '/yonetim/sayfalar/' . $row['id'] : '';
$slugHelp = 'Sayfa adresi: /adres. Boş bırakılırsa başlıktan üretilir.';
if ($menuLinks) {
    $slugHelp .= ' Bu sayfaya menüden bağlantı var (' . implode('; ', $menuLinks) . '); adresi değiştirirseniz o bağlantı çalışmaz.';
}
?>
<p class="back-link"><a href="<?= e(url('/yonetim/sayfalar')) ?>">← Sayfalar</a></p>
<div class="page-title page-title-row">
    <div>
        <h1><?= $row ? 'Sayfayı düzenle' : 'Yeni sayfa' ?></h1>
        <?php if ($row): ?>
        <p><?= $row['status'] === 'published' ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Taslak</span>' ?>
            <span class="muted">/<?= e($row['slug']) ?></span></p>
        <?php endif; ?>
    </div>
    <?php if ($row && $row['status'] === 'published'): ?>
    <a class="btn btn-outline" href="<?= e(url('/' . $row['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
    <?php endif; ?>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= App\Core\Csrf::field() ?>
    <fieldset class="panel">
        <legend>Sayfa</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->text('slug', 'Adres', ['slug_from' => 'title', 'maxlength' => 150, 'help' => $slugHelp]) ?>
            <?= $f->select('template', 'Şablon', PageAdminController::TEMPLATES, ['help' => '"Hakkımda düzeni": portre, Yolculuğum, Yaklaşımım ve alıntı bölümleri olan sayfa. Bu bölümler kaydettikten sonra bu ekranda açılır.']) ?>
            <?= $f->select('status', 'Durum', ['draft' => 'Taslak (sitede görünmez)', 'published' => 'Yayında']) ?>
            <?= $f->image('hero_image', $isAbout ? 'Kapak fotoğrafı' : 'Üst görsel', ['help' => $isAbout
                ? 'Sayfanın en üstünde tam genişlikte görünür, başlık üzerine yazılır. Yatay ve en az 1600 px genişlikte bir fotoğraf seçin.'
                : 'Sayfa paylaşıldığında görünebilir; Hakkımda düzeninde portre olarak gösterilir. JPG, PNG, WebP veya GIF; en fazla 8 MB.']) ?>
            <?= $f->richtext('body', 'Metin') ?>
        </div>
    </fieldset>

    <?php if ($isAbout): ?>
    <fieldset class="panel">
        <legend>Tanıtım bölümü</legend>
        <p class="muted">Kapak fotoğrafının altında: solda portre, sağda başlık ve yukarıdaki sayfa metni, altında "Hizmetleri keşfet" ve "İletişime geç" butonları.</p>
        <div class="field-grid">
            <?= $f->image('about_portrait', 'Portre görseli', ['help' => 'Dikey fotoğraf önerilir (örnek: 900×1200).']) ?>
            <?= $f->text('about_eyebrow', 'Üst küçük yazı', ['maxlength' => 100, 'help' => 'Örnek: Perihan Ateş Kimdir?']) ?>
            <?= $f->text('about_heading', 'Başlık', ['maxlength' => 150, 'help' => 'Örnek: Sadece Varolanım']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Sertifikalar</legend>
        <p class="muted">Metnin altında QR kodlu kart olarak görünür. Başlık ve QR görseli boşsa bölüm gösterilmez.</p>
        <div class="field-grid">
            <?= $f->text('about_cert_title', 'Başlık', ['maxlength' => 150, 'help' => 'Örnek: Sertifikalarım']) ?>
            <?= $f->textarea('about_cert_text', 'Açıklama', ['maxlength' => 600, 'rows' => 3, 'class' => 'wide']) ?>
            <?= $f->image('about_cert_qr', 'QR kod görseli', ['help' => 'Sertifika sayfanızın QR kodunu PNG olarak yükleyin.']) ?>
            <?= $f->text('about_cert_url', 'Sertifikalar bağlantısı', ['maxlength' => 300, 'help' => 'Butonun gideceği adres (Google Drive klasörü, PDF vb.).']) ?>
            <?= $f->text('about_cert_button', 'Buton yazısı', ['maxlength' => 60, 'help' => 'Boşsa: Tüm Sertifikaları Gör']) ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Alıntı</legend>
        <p class="muted">Sayfanın altında koyu bant içinde görünür. Boş bırakılırsa bant gösterilmez.</p>
        <div class="field-grid">
            <?= $f->textarea('about_quote', 'Alıntı metni', ['maxlength' => 500, 'rows' => 3, 'class' => 'wide']) ?>
            <?= $f->text('about_quote_author', 'Kimin sözü', ['maxlength' => 150, 'help' => 'Örnek: Mevlânâ. Boş bırakılabilir.']) ?>
        </div>
    </fieldset>
    <?php endif; ?>

    <fieldset class="panel">
        <legend>Arama motorları (SEO)</legend>
        <div class="field-grid">
            <?= $f->text('meta_title', 'Sayfa başlığı', ['maxlength' => 200, 'class' => 'wide', 'help' => 'Tarayıcı sekmesinde ve Google sonuçlarında görünür. Boşsa sayfa başlığı kullanılır.']) ?>
            <?= $f->textarea('meta_description', 'Açıklama', ['maxlength' => 300, 'rows' => 2, 'class' => 'wide', 'help' => $isAbout
                ? 'Google sonuçlarında görünür. Hakkımda düzeninde başlığın altındaki giriş cümlesi olarak da gösterilir.'
                : 'Google sonuçlarında başlığın altında görünen 1-2 cümle.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>

<?php if ($isAbout): ?>
<section class="panel" id="yolculuk">
    <div class="panel-head">
        <h2>Yolculuğum</h2>
        <a href="<?= e(url($base . '/yolculuk/yeni')) ?>">+ Adım ekle</a>
    </div>
    <p class="muted">Sayfadaki zaman çizelgesi. Sıra numarası küçük olan önce gösterilir.</p>
    <?php if (!$timeline): ?>
    <p class="empty">Henüz adım yok; bölüm sitede gösterilmez.</p>
    <?php else: ?>
    <div class="rows">
        <?php foreach ($timeline as $t): ?>
        <div class="row-card">
            <div class="row-head">
                <span><span class="tag tag-wait"><?= e($t['year_label']) ?></span> <?= e($t['title']) ?></span>
                <span class="cell-actions">
                    <span class="muted">Sıra <?= (int) $t['sort_order'] ?></span>
                    <a class="btn-small" href="<?= e(url($base . '/yolculuk/' . $t['id'])) ?>">Düzenle</a>
                    <form method="post" action="<?= e(url($base . '/yolculuk/' . $t['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($t['title']) ?>&quot; adımı silinsin mi?">
                        <?= App\Core\Csrf::field() ?>
                        <button class="btn-small btn-small-danger" type="submit">Sil</button>
                    </form>
                </span>
            </div>
            <?php if ($t['text']): ?><p class="muted page-row-text"><?= e(mb_strimwidth((string) $t['text'], 0, 160, '…')) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="panel" id="yaklasim">
    <div class="panel-head">
        <h2>Yaklaşımım</h2>
        <a href="<?= e(url($base . '/yaklasim/yeni')) ?>">+ Kart ekle</a>
    </div>
    <p class="muted">"Nasıl çalışırım?" başlıklı ikonlu kartlar.</p>
    <?php if (!$aboutValues): ?>
    <p class="empty">Henüz kart yok; bölüm sitede gösterilmez.</p>
    <?php else: ?>
    <div class="rows">
        <?php foreach ($aboutValues as $v): ?>
        <div class="row-card">
            <div class="row-head">
                <span><?= e($v['title']) ?> <small class="muted">(<?= e(PageAdminController::ICONS[$v['icon']] ?? $v['icon']) ?>)</small></span>
                <span class="cell-actions">
                    <span class="muted">Sıra <?= (int) $v['sort_order'] ?></span>
                    <a class="btn-small" href="<?= e(url($base . '/yaklasim/' . $v['id'])) ?>">Düzenle</a>
                    <form method="post" action="<?= e(url($base . '/yaklasim/' . $v['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($v['title']) ?>&quot; kartı silinsin mi?">
                        <?= App\Core\Csrf::field() ?>
                        <button class="btn-small btn-small-danger" type="submit">Sil</button>
                    </form>
                </span>
            </div>
            <?php if ($v['text']): ?><p class="muted page-row-text"><?= e(mb_strimwidth((string) $v['text'], 0, 160, '…')) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>
