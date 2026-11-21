<?php /** @var array<int, array{path: string, title: string}> $items */ ?>
<div class="page-title">
    <div>
        <h1>Sertifikalar</h1>
        <p class="muted">Sitede <a href="<?= e(url('/sertifikalar')) ?>" target="_blank" rel="noopener">/sertifikalar ↗</a> sayfasında küçük kartlar hâlinde görünür; tıklayınca büyür.</p>
    </div>
</div>

<form class="form panel" method="post" action="<?= e(url('/yonetim/sertifikalar/ekle')) ?>" enctype="multipart/form-data">
    <?= App\Core\Csrf::field() ?>
    <label>Sertifika görselleri ekle (birden fazla seçebilirsiniz)
        <input type="file" name="files[]" accept="image/jpeg,image/png,image/webp" multiple required>
    </label>
    <p class="muted">JPG, PNG veya WebP; her biri en fazla <?= e(App\Core\Upload::limitText()) ?>.</p>
    <button class="btn" type="submit">Yükle</button>
</form>

<?php if ($items): ?>
<form class="form panel" method="post" action="<?= e(url('/yonetim/sertifikalar')) ?>">
    <?= App\Core\Csrf::field() ?>
    <div class="cert-admin-grid">
        <?php foreach ($items as $i => $item): ?>
        <div class="cert-admin-item">
            <img src="<?= e(url($item['path'])) ?>" alt="" loading="lazy">
            <input type="text" name="title[<?= $i ?>]" value="<?= e($item['title']) ?>" maxlength="150" aria-label="Başlık">
            <div class="cert-admin-row">
                <label>Sıra <input type="number" name="order[<?= $i ?>]" value="<?= $i + 1 ?>" min="1"></label>
                <label class="checkbox"><input type="checkbox" name="delete[<?= $i ?>]" value="1"> Sil</label>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="btn" type="submit">Kaydet</button>
</form>
<?php endif; ?>
