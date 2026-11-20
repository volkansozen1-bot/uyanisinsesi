<?php
/**
 * @var array<int, array<string, mixed>> $items
 * @var array<string, int> $pager
 */
use App\Core\View;

$size = static function ($bytes): string {
    $bytes = (int) $bytes;
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
    return max(1, (int) round($bytes / 1024)) . ' KB';
};
?>
<div class="page-title">
    <h1>Medya</h1>
    <p class="muted">Panelden yüklenen görseller. Yazı ve sayfalarda görsel ya da bağlantı eklerken buradaki adresi kopyalayıp kullanabilirsiniz.</p>
</div>

<form class="form panel" method="post" action="<?= e(url('/yonetim/medya/yukle')) ?>" enctype="multipart/form-data">
    <?= App\Core\Csrf::field() ?>
    <div class="field-grid">
        <div class="field">
            <label for="f-image">Görsel seç</label>
            <input type="file" name="image" id="f-image" accept="image/jpeg,image/png,image/webp,image/gif" required>
            <small class="help">JPG, PNG, WebP veya GIF; en fazla 8 MB. Çok büyük görseller otomatik küçültülür.</small>
        </div>
        <div class="field">
            <label for="f-alt">Alt metin</label>
            <input type="text" name="alt" id="f-alt" maxlength="255">
            <small class="help">Görselde ne olduğunu kısaca yazın. Görme engelli ziyaretçiler ve arama motorları için.</small>
        </div>
    </div>
    <div><button class="btn" type="submit">Yükle</button></div>
</form>

<div class="panel">
    <div class="panel-head">
        <h2>Yüklenen görseller</h2>
        <?php if ($pager['total']): ?><span class="muted"><?= (int) $pager['total'] ?> görsel</span><?php endif; ?>
    </div>

    <?php if (!$items): ?>
    <p class="empty">Henüz görsel yüklenmedi. Yukarıdaki formdan ilk görseli yükleyebilirsiniz.</p>
    <?php else: ?>
    <ul class="media-grid">
        <?php foreach ($items as $item): $full = url($item['path']); ?>
        <li class="media-card">
            <a class="media-thumb" href="<?= e($full) ?>" target="_blank" rel="noopener" title="Büyük boy aç">
                <img src="<?= e($full) ?>" alt="<?= e((string) ($item['alt'] ?? '')) ?>" loading="lazy">
            </a>
            <p class="media-meta">
                <?= $size($item['size']) ?>
                <?php if ($item['width'] && $item['height']): ?> · <?= (int) $item['width'] ?>×<?= (int) $item['height'] ?><?php endif; ?>
                <br><span class="muted"><?= e(tr_date((string) $item['created_at'])) ?></span>
            </p>

            <div class="field">
                <label for="u-<?= (int) $item['id'] ?>">Adresi kopyala</label>
                <input type="text" id="u-<?= (int) $item['id'] ?>" class="copy-input" value="<?= e($full) ?>" readonly>
            </div>

            <form class="media-alt" method="post" action="<?= e(url('/yonetim/medya/' . $item['id'])) ?>">
                <?= App\Core\Csrf::field() ?>
                <div class="field">
                    <label for="a-<?= (int) $item['id'] ?>">Alt metin</label>
                    <input type="text" name="alt" id="a-<?= (int) $item['id'] ?>" maxlength="255" value="<?= e((string) ($item['alt'] ?? '')) ?>">
                </div>
                <div class="media-actions">
                    <button class="btn-small" type="submit">Kaydet</button>
                </div>
            </form>

            <form method="post" action="<?= e(url('/yonetim/medya/' . $item['id'] . '/sil')) ?>" data-confirm="Bu görsel ve dosyası silinsin mi?">
                <?= App\Core\Csrf::field() ?>
                <button class="btn-small btn-small-danger" type="submit">Sil</button>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>
    <?= View::renderFile('admin/partials/pagination', ['pager' => $pager, 'base' => '/yonetim/medya', 'query' => []]) ?>
    <?php endif; ?>
</div>
