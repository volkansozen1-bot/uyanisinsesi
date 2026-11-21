<?php
/** @var array<int, array<string, mixed>> $items */
$platforms = App\Controllers\Admin\SocialAdminController::PLATFORMS;
?>
<div class="page-title page-title-row">
    <div>
        <h1>Sosyal Medya</h1>
        <p class="muted">Sitenin alt bilgisinde görünen sosyal medya bağlantıları. Spotify bağlantısı Meditasyon sayfasındaki düğmede de kullanılır.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/sosyal-medya/yeni')) ?>">+ Yeni bağlantı</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz bağlantı yok. <a href="<?= e(url('/yonetim/sosyal-medya/yeni')) ?>">İlk bağlantıyı ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th><span class="sr-only">İkon</span></th><th>Görünen ad</th><th>Platform</th><th>Adres</th><th>Sıra</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): $edit = url('/yonetim/sosyal-medya/' . $item['id']); ?>
                <tr>
                    <td class="cell-thumb">
                        <?php if ($item['icon']): ?>
                        <img class="thumb thumb-icon" src="<?= e(url($item['icon'])) ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <span class="muted" title="İkon yüklenmedi: sitede adı yazı olarak görünür">Yazı</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= e($edit) ?>"><?= e($item['label']) ?></a></td>
                    <td data-label="Platform"><?= e($platforms[$item['platform']] ?? $item['platform']) ?></td>
                    <td class="cell-email" data-label="Adres"><a href="<?= e($item['url']) ?>" target="_blank" rel="noopener"><?= e(mb_strimwidth((string) $item['url'], 0, 45, '…')) ?></a></td>
                    <td data-label="Sıra"><?= (int) $item['sort_order'] ?></td>
                    <td><?= $item['is_active'] ? '<span class="tag tag-ok">Görünür</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e($edit) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/sosyal-medya/' . $item['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($item['label']) ?>&quot; bağlantısı silinsin mi?">
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
