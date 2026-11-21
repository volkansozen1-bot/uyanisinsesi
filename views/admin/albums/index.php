<?php
/** @var array<int, array<string, mixed>> $items */
$types = App\Controllers\Admin\AlbumAdminController::TYPES;
?>
<div class="page-title page-title-row">
    <div>
        <h1>Meditasyon</h1>
        <p class="muted">Meditasyon sayfasında görünen kayıtlar. <a href="<?= e(url('/albumler')) ?>" target="_blank" rel="noopener">Sitede gör ↗</a></p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/albumler/yeni')) ?>">+ Yeni albüm</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz albüm yok. <a href="<?= e(url('/yonetim/albumler/yeni')) ?>">İlk albümü ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th><span class="sr-only">Kapak</span></th><th>Ad</th><th>Tür</th><th>Yıl</th><th>Sıra</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): $edit = url('/yonetim/albumler/' . $item['id']); ?>
                <tr>
                    <td class="cell-thumb">
                        <?php if ($item['cover_image']): ?>
                        <img class="thumb" src="<?= e(url($item['cover_image'])) ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <span class="thumb" aria-hidden="true"></span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= e($edit) ?>"><?= e($item['title']) ?></a><?php if ($item['track_count']): ?><br><small class="muted"><?= (int) $item['track_count'] ?> parça</small><?php endif; ?></td>
                    <td data-label="Tür"><?= e($types[$item['type']] ?? $item['type']) ?></td>
                    <td data-label="Yıl"><?= $item['year'] ? (int) $item['year'] : '<span class="muted">—</span>' ?></td>
                    <td data-label="Sıra"><?= (int) $item['sort_order'] ?></td>
                    <td><?= $item['is_active'] ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e($edit) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/albumler/' . $item['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($item['title']) ?>&quot; silinsin mi?">
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
