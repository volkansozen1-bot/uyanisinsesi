<?php /** @var array<int, array<string, mixed>> $items */ ?>
<div class="page-title page-title-row">
    <div>
        <h1>Hizmetler</h1>
        <p class="muted">Hizmetler sayfasında ve menüde görünen hizmetler. Paketler, sekmeler ve sık sorulan sorular hizmetin düzenleme sayfasından yönetilir.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/hizmetler/yeni')) ?>">+ Yeni hizmet</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz hizmet yok. <a href="<?= e(url('/yonetim/hizmetler/yeni')) ?>">İlk hizmeti ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Adres</th><th>Paket</th><th>Durum</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><a href="<?= e(url('/yonetim/hizmetler/' . $item['id'])) ?>"><?= e($item['title']) ?></a></td>
                    <td data-label="Adres" class="cell-text">/hizmetler/<?= e($item['slug']) ?></td>
                    <td data-label="Paket"><?= (int) $item['package_count'] ?></td>
                    <td data-label="Durum"><?= $item['is_active'] ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td data-label="Sıra"><?= (int) $item['sort_order'] ?></td>
                    <td class="cell-actions">
                        <?php if ($item['is_active']): ?>
                        <a class="btn-small" href="<?= e(url('/hizmetler/' . $item['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
                        <?php endif; ?>
                        <a class="btn-small" href="<?= e(url('/yonetim/hizmetler/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/hizmetler/' . $item['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($item['title']) ?>&quot; silinsin mi? Paketleri, bölümleri ve sık sorulan soruları da silinir.">
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
