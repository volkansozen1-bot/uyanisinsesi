<?php
/**
 * Hizmet formu. Duzenlemede altta paketler, kitle bolumleri ve SSS listeleri.
 * @var array<string, mixed>|null $row
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var string $action
 * @var array<int, array<string, mixed>> $packages
 * @var array<int, array<string, mixed>> $sections
 * @var array<int, array<string, mixed>> $faqs
 */
use App\Core\Csrf;

$f    = new App\Core\Form($values, $errors);
$base = $row ? '/yonetim/hizmetler/' . $row['id'] : '';
?>
<p class="back-link"><a href="<?= e(url('/yonetim/hizmetler')) ?>">← Hizmetler</a></p>
<div class="page-title page-title-row">
    <div>
        <h1><?= $row ? e($row['title']) : 'Yeni hizmet' ?></h1>
        <?php if ($row): ?><p class="muted">Adres: /hizmetler/<?= e($row['slug']) ?></p><?php endif; ?>
    </div>
    <?php if ($row && $row['is_active']): ?>
    <a class="btn-small" href="<?= e(url('/hizmetler/' . $row['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
    <?php endif; ?>
</div>

<?php if ($row): ?>
<nav class="subnav" aria-label="Bu sayfada">
    <a href="#hizmet-bilgileri">Hizmet bilgileri</a>
    <a href="#paketler">Paketler / ücretler <span class="count"><?= count($packages) ?></span></a>
    <a href="#bolumler">Kitle bölümleri <span class="count"><?= count($sections) ?></span></a>
    <a href="#sss">Sık sorulan sorular <span class="count"><?= count($faqs) ?></span></a>
</nav>
<?php else: ?>
<p class="notice notice-info">Paketler, kitle bölümleri (sekmeler) ve sık sorulan sorular, hizmet kaydedildikten sonra bu sayfanın altından eklenir.</p>
<?php endif; ?>

<form class="form" id="hizmet-bilgileri" method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" novalidate>
    <?= Csrf::field() ?>
    <fieldset class="panel">
        <legend>Hizmet</legend>
        <div class="field-grid">
            <?= $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]) ?>
            <?= $f->text('slug', 'Adres', ['maxlength' => 150, 'slug_from' => 'title', 'help' => 'Sayfanın adresi: /hizmetler/bireysel-seans gibi. Boş bırakılırsa başlıktan üretilir. Yayındaki bir hizmetin adresini değiştirirseniz eski bağlantılar çalışmaz.']) ?>
            <?= $f->textarea('summary', 'Kısa özet', ['maxlength' => 500, 'rows' => 3, 'class' => 'wide', 'help' => 'Hizmetler listesinde kartta ve hizmet sayfasının başında görünür. En fazla 500 karakter.']) ?>
            <?= $f->richtext('body', 'Metin', ['help' => 'Hizmet sayfasında başlığın altındaki tanıtım metni.']) ?>
            <?= $f->image('image', 'Görsel') ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Randevu bilgileri</legend>
        <p class="muted panel-intro">Günler, saatler ya da yer doldurulursa hizmet sayfasında "Randevu" bölümü ve WhatsApp butonu görünür. Hiçbiri doldurulmazsa yerine genel iletişim çağrısı çıkar.</p>
        <div class="field-grid">
            <?= $f->text('booking_days', 'Günler', ['maxlength' => 150, 'placeholder' => 'Salı ve Perşembe']) ?>
            <?= $f->text('booking_hours', 'Saatler', ['maxlength' => 100, 'placeholder' => '10:00 – 18:00']) ?>
            <?= $f->text('booking_place', 'Yer', ['maxlength' => 150, 'placeholder' => 'Online ya da yüz yüze (İzmir)']) ?>
            <?= $f->textarea('whatsapp_message', 'WhatsApp mesajı', ['maxlength' => 300, 'rows' => 2, 'class' => 'wide', 'help' => 'WhatsApp butonuna basınca hazır gelen mesaj. Boş bırakılırsa "Merhaba, <hizmet adı> için bilgi almak istiyorum." yazılır. WhatsApp numarası Ayarlar sayfasından girilir.']) ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Arama motorları (SEO)</legend>
        <div class="field-grid">
            <?= $f->text('meta_title', 'Arama başlığı', ['maxlength' => 200, 'help' => 'Google sonuçlarında ve tarayıcı sekmesinde görünen başlık. Boşsa hizmet başlığı kullanılır.']) ?>
            <?= $f->textarea('meta_description', 'Arama açıklaması', ['maxlength' => 300, 'rows' => 3, 'help' => 'Google sonuçlarında başlığın altındaki kısa açıklama (150–160 karakter idealdir). Boşsa kısa özet kullanılır.']) ?>
        </div>
    </fieldset>

    <fieldset class="panel">
        <legend>Görünüm</legend>
        <div class="field-grid">
            <?= $f->number('sort_order', 'Sıra', ['help' => 'Küçük sayı önce gösterilir.']) ?>
            <?= $f->checkbox('is_active', 'Sitede göster', ['help' => 'İşaret kaldırılırsa hizmet sitede görünmez, adresi "sayfa bulunamadı" verir.']) ?>
        </div>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>

<?php if ($row): ?>
<section class="panel" id="paketler" aria-labelledby="h-paketler">
    <div class="panel-head">
        <h2 id="h-paketler">Paketler / ücretler</h2>
        <a class="btn-small" href="<?= e(url($base . '/paket/yeni')) ?>">+ Paket ekle</a>
    </div>
    <p class="muted panel-intro">Hizmet sayfasındaki "Ücretler" bölümünde kart olarak görünür.</p>
    <?php if (!$packages): ?>
    <p class="empty">Henüz paket yok; ücretler bölümü sitede gösterilmez.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ad</th><th>Fiyat</th><th>Buton</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($packages as $p): ?>
                <tr>
                    <td><a href="<?= e(url($base . '/paket/' . $p['id'])) ?>"><?= e($p['name']) ?></a></td>
                    <td data-label="Fiyat"><?= $p['price'] !== null ? e(tr_price($p['price'])) : '<span class="muted">İletişime geçin</span>' ?></td>
                    <td data-label="Buton" class="cell-text">
                        <?= e($p['button_text'] ?: 'Bilgi Al') ?><br>
                        <small><?= $p['button_url'] ? e(mb_strimwidth((string) $p['button_url'], 0, 50, '…')) : 'WhatsApp / iletişim' ?></small>
                    </td>
                    <td data-label="Sıra"><?= (int) $p['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base . '/paket/' . $p['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/paket/' . $p['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($p['name']) ?>&quot; paketi silinsin mi?">
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

<section class="panel" id="bolumler" aria-labelledby="h-bolumler">
    <div class="panel-head">
        <h2 id="h-bolumler">Kitle bölümleri / sekmeler</h2>
        <a class="btn-small" href="<?= e(url($base . '/bolum/yeni')) ?>">+ Bölüm ekle</a>
    </div>
    <p class="muted panel-intro">Örneğin "Kadınlar için" ve "Erkekler için". İki veya daha fazla bölüm varsa hizmet sayfasında sekme olarak görünür; tek bölüm sekmesiz gösterilir.</p>
    <?php if (!$sections): ?>
    <p class="empty">Henüz bölüm yok.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Sekme adı</th><th>Başlık</th><th>Çapa</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($sections as $s): ?>
                <tr>
                    <td><a href="<?= e(url($base . '/bolum/' . $s['id'])) ?>"><?= e($s['tab_label']) ?></a></td>
                    <td data-label="Başlık" class="cell-text"><?= e($s['title']) ?></td>
                    <td data-label="Çapa"><code>#<?= e($s['anchor']) ?></code></td>
                    <td data-label="Sıra"><?= (int) $s['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base . '/bolum/' . $s['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/bolum/' . $s['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($s['tab_label']) ?>&quot; bölümü silinsin mi?">
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
    <p class="muted panel-intro">Hizmet sayfasının sonunda açılır-kapanır liste olarak görünür.</p>
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
