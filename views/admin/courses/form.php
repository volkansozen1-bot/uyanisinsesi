<?php
/**
 * Egitim formu. Duzenlemede altta moduller, katilim secenekleri ve SSS listeleri.
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 * @var bool $isPromoted
 * @var array<int, array<string, mixed>> $modules
 * @var array<int, array<string, mixed>> $plans
 * @var array<int, array<string, mixed>> $faqs
 */
use App\Core\Csrf;

$f    = new App\Core\Form($values, $errors);
$base = $row ? '/yonetim/egitimler/' . $row['id'] : '';
?>
<p class="back-link"><a href="<?= e(url('/yonetim/egitimler')) ?>">← Eğitimler</a></p>
<div class="page-title page-title-row">
    <div>
        <h1><?= $row ? e($row['title']) : 'Yeni eğitim' ?></h1>
        <?php if ($row): ?><p class="muted">Adres: /egitimler/<?= e($row['slug']) ?></p><?php endif; ?>
    </div>
    <?php if ($row && $row['is_active']): ?>
    <a class="btn-small" href="<?= e(url('/egitimler/' . $row['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
    <?php endif; ?>
</div>

<?php if ($row): ?>
<nav class="subnav" aria-label="Bu sayfada">
    <a href="#egitim-bilgileri">Eğitim bilgileri</a>
    <a href="#moduller">Modüller <span class="count"><?= count($modules) ?></span></a>
    <a href="#secenekler">Katılım seçenekleri <span class="count"><?= count($plans) ?></span></a>
    <a href="#sss">Sık sorulan sorular <span class="count"><?= count($faqs) ?></span></a>
</nav>
<?php if ($isPromoted): ?>
<p class="notice notice-info">Ana sayfadaki eğitim duyurusunda şu anda bu eğitim gösteriliyor.</p>
<?php endif; ?>
<?php else: ?>
<p class="notice notice-info">Modüller, katılım seçenekleri ve sık sorulan sorular, eğitim kaydedildikten sonra bu sayfanın altından eklenir.</p>
<?php endif; ?>

<form class="form" id="egitim-bilgileri" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= Csrf::field() ?>
    <fieldset class="panel">
        <legend>Eğitim</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->text('slug', 'Adres', ['maxlength' => 150, 'slug_from' => 'title', 'help' => 'Sayfanın adresi: /egitimler/golge-calismasi gibi. Boş bırakılırsa başlıktan üretilir. Yayındaki bir eğitimin adresini değiştirirseniz eski bağlantılar çalışmaz.']) ?>
            <?= $f->textarea('summary', 'Kısa özet', ['maxlength' => 500, 'rows' => 3, 'class' => 'wide', 'help' => 'Eğitimler listesinde kartta, eğitim sayfasının başında ve ana sayfadaki duyuruda görünür. En fazla 500 karakter.']) ?>
            <?= $f->richtext('body', 'Metin', ['help' => 'Eğitim sayfasındaki tanıtım metni.']) ?>
            <?= $f->image('image', 'Görsel') ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Tarihler</legend>
        <div class="field-grid">
            <?= $f->date('start_date', 'Başlangıç tarihi', ['help' => 'Listede ve sayfa başında "Başlangıç: 1 Aralık 2026" olarak görünür. Boşsa yazılmaz.']) ?>
            <?= $f->datetime('countdown_until', 'Geri sayım bitişi', ['help' => 'Eğitim sayfasında gün/saat/dakika sayan geri sayım bu ana kadar görünür. Boşsa geri sayım gösterilmez; geçmiş bir tarihte de gösterilmez.']) ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Arama motorları (SEO)</legend>
        <div class="field-grid">
            <?= $f->text('meta_title', 'Arama başlığı', ['maxlength' => 200, 'help' => 'Google sonuçlarında ve tarayıcı sekmesinde görünen başlık. Boşsa eğitim başlığı kullanılır.']) ?>
            <?= $f->textarea('meta_description', 'Arama açıklaması', ['maxlength' => 300, 'rows' => 3, 'help' => 'Google sonuçlarında başlığın altındaki kısa açıklama (150–160 karakter idealdir). Boşsa kısa özet kullanılır.']) ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir. Ana sayfadaki eğitim duyurusu, sırada en önde olan yayındaki eğitimi gösterir; duyurulmasını istediğiniz eğitime en küçük sayıyı verin.']) ?>
            <?= $f->checkbox('is_active', 'Sitede göster', ['help' => 'İşaret kaldırılırsa eğitim sitede görünmez, adresi "sayfa bulunamadı" verir ve ana sayfada duyurulmaz.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>

<?php if ($row): ?>
<section class="panel" id="moduller" aria-labelledby="h-moduller">
    <div class="panel-head">
        <h2 id="h-moduller">Modüller</h2>
        <a class="btn-small" href="<?= e(url($base . '/modul/yeni')) ?>">+ Modül ekle</a>
    </div>
    <p class="muted panel-intro">Eğitim sayfasındaki "Adım adım içerik" listesi. Numaralar sıraya göre otomatik verilir.</p>
    <?php if (!$modules): ?>
    <p class="empty">Henüz modül yok; program bölümü sitede gösterilmez.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Açıklama</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($modules as $m): ?>
                <tr>
                    <td><a href="<?= e(url($base . '/modul/' . $m['id'])) ?>"><?= e($m['title']) ?></a></td>
                    <td data-label="Açıklama" class="cell-text"><?= e(mb_strimwidth((string) $m['description'], 0, 90, '…')) ?></td>
                    <td data-label="Sıra"><?= (int) $m['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base . '/modul/' . $m['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/modul/' . $m['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($m['title']) ?>&quot; modülü silinsin mi?">
                            <?= Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section class="panel" id="secenekler" aria-labelledby="h-secenekler">
    <div class="panel-head">
        <h2 id="h-secenekler">Katılım seçenekleri</h2>
        <a class="btn-small" href="<?= e(url($base . '/secenek/yeni')) ?>">+ Seçenek ekle</a>
    </div>
    <p class="muted panel-intro">Eğitim sayfasındaki "Kayıt" bölümünde kart olarak görünür.</p>
    <?php if (!$plans): ?>
    <p class="empty">Henüz katılım seçeneği yok; kayıt bölümü sitede gösterilmez.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ad</th><th>Fiyat</th><th>Buton</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($plans as $p): ?>
                <tr>
                    <td><a href="<?= e(url($base . '/secenek/' . $p['id'])) ?>"><?= e($p['name']) ?></a></td>
                    <td data-label="Fiyat">
                        <?php if ($p['price'] !== null): ?>
                        <?php if ($p['list_price'] !== null && (float) $p['list_price'] > (float) $p['price']): ?><del class="muted"><?= e(tr_price($p['list_price'])) ?></del> <?php endif; ?>
                        <?= e(tr_price($p['price'])) ?>
                        <?php else: ?>
                        <span class="muted">İletişime geçin</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Buton" class="cell-text">
                        <?= e($p['button_text'] ?: 'Kayıt İçin Yaz') ?><br>
                        <small><?= $p['button_url'] ? e(mb_strimwidth((string) $p['button_url'], 0, 50, '…')) : 'İletişim sayfası' ?></small>
                    </td>
                    <td data-label="Sıra"><?= (int) $p['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base . '/secenek/' . $p['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/secenek/' . $p['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($p['name']) ?>&quot; seçeneği silinsin mi?">
                            <?= Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section class="panel" id="sss" aria-labelledby="h-sss">
    <div class="panel-head">
        <h2 id="h-sss">Sık sorulan sorular</h2>
        <a class="btn-small" href="<?= e(url($base . '/sss/yeni')) ?>">+ Soru ekle</a>
    </div>
    <p class="muted panel-intro">Eğitim sayfasının sonunda açılır-kapanır liste olarak görünür.</p>
    <?php if (!$faqs): ?>
    <p class="empty">Henüz soru yok.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Soru</th><th>Cevap</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($faqs as $q): ?>
                <tr>
                    <td><a href="<?= e(url($base . '/sss/' . $q['id'])) ?>"><?= e($q['question']) ?></a></td>
                    <td data-label="Cevap" class="cell-text"><?= e(mb_strimwidth((string) $q['answer'], 0, 90, '…')) ?></td>
                    <td data-label="Sıra"><?= (int) $q['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base . '/sss/' . $q['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/sss/' . $q['id'] . '/sil')) ?>" data-confirm="Bu soru silinsin mi?">
                            <?= Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>
