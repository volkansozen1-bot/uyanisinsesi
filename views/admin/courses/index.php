<?php /** @var array<int, array<string, mixed>> $items */ ?>
<div class="page-title page-title-row">
    <div>
        <h1>Eğitimler</h1>
        <p class="muted">Eğitimler sayfasındaki programlar. Modüller, katılım seçenekleri ve sık sorulan sorular eğitimin düzenleme sayfasından yönetilir.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/egitimler/yeni')) ?>">+ Yeni eğitim</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz eğitim yok. <a href="<?= e(url('/yonetim/egitimler/yeni')) ?>">İlk eğitimi ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Adres</th><th>Başlangıç</th><th>Seçenek</th><th>Durum</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php $promoted = true; foreach ($items as $item): ?>
                <tr>
                    <td>
                        <a href="<?= e(url('/yonetim/egitimler/' . $item['id'])) ?>"><?= e($item['title']) ?></a>
                        <?php if ($item['is_active'] && $promoted): $promoted = false; ?>
                        <br><small class="muted">Ana sayfada duyurulan eğitim</small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Adres" class="cell-text">/egitimler/<?= e($item['slug']) ?></td>
                    <td data-label="Başlangıç"><?= $item['start_date'] ? e(tr_date($item['start_date'])) : '<span class="muted">—</span>' ?></td>
                    <td data-label="Seçenek"><?= (int) $item['plan_count'] ?></td>
                    <td data-label="Durum"><?= $item['is_active'] ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td data-label="Sıra"><?= (int) $item['sort_order'] ?></td>
                    <td class="cell-actions">
                        <?php if ($item['is_active']): ?>
                        <a class="btn-small" href="<?= e(url('/egitimler/' . $item['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
                        <?php endif; ?>
                        <a class="btn-small" href="<?= e(url('/yonetim/egitimler/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/egitimler/' . $item['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($item['title']) ?>&quot; silinsin mi? Modülleri, katılım seçenekleri ve sık sorulan soruları da silinir.">
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
