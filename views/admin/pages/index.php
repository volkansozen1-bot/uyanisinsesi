<?php
/**
 * @var array<int, array<string, mixed>> $items
 * @var array<int, array<int, string>> $menuLinks sayfa id => menu baglantilari
 */
use App\Controllers\Admin\PageAdminController;
?>
<div class="page-title page-title-row">
    <div>
        <h1>Sayfalar</h1>
        <p class="muted">Hakkımda, KVKK, gizlilik gibi tek başına duran sayfalar.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/sayfalar/yeni')) ?>">+ Yeni sayfa</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz sayfa yok. <a href="<?= e(url('/yonetim/sayfalar/yeni')) ?>">İlk sayfayı ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Adres</th><th>Şablon</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item):
                $links   = $menuLinks[$item['id']] ?? [];
                $confirm = '"' . $item['title'] . '" sayfası kalıcı olarak silinsin mi?';
                if ($links) {
                    $confirm .= "\n\nDİKKAT: Bu sayfaya sitenin menüsünden bağlantı var (" . implode('; ', $links)
                        . "). Silerseniz bu bağlantı \"sayfa bulunamadı\" gösterir. Yasal metinler (KVKK, gizlilik) sitede bulunmak zorundadır.";
                }
            ?>
                <tr>
                    <td>
                        <a href="<?= e(url('/yonetim/sayfalar/' . $item['id'])) ?>"><?= e($item['title']) ?></a>
                        <?php if ($links): ?><small class="muted d-block">Menüde bağlantılı</small><?php endif; ?>
                    </td>
                    <td data-label="Adres" class="muted">/<?= e($item['slug']) ?></td>
                    <td data-label="Şablon"><?= e(PageAdminController::TEMPLATES[$item['template']] ?? $item['template']) ?></td>
                    <td data-label="Durum"><?= $item['status'] === 'published' ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Taslak</span>' ?></td>
                    <td class="cell-actions">
                        <?php if ($item['status'] === 'published'): ?>
                        <a class="btn-small" href="<?= e(url('/' . $item['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
                        <?php endif; ?>
                        <a class="btn-small" href="<?= e(url('/yonetim/sayfalar/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/sayfalar/' . $item['id'] . '/sil')) ?>" data-confirm="<?= e($confirm) ?>">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
