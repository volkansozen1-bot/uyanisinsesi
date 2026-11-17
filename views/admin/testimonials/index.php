<?php /** @var array<int, array<string, mixed>> $items */ ?>
<div class="page-title page-title-row">
    <div>
        <h1>Yorumlar</h1>
        <p class="muted">Ana sayfada ve hizmet sayfalarında görünen katılımcı yorumları.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/yorumlar/yeni')) ?>">+ Yeni yorum</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz yorum yok. <a href="<?= e(url('/yonetim/yorumlar/yeni')) ?>">İlk yorumu ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ad</th><th>Yorum</th><th>Hizmet</th><th>Sıra</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><a href="<?= e(url('/yonetim/yorumlar/' . $item['id'])) ?>"><?= e($item['name']) ?></a></td>
                    <td class="cell-text"><?= e(mb_strimwidth((string) $item['text'], 0, 90, '…')) ?></td>
                    <td><?= $item['service_title'] ? e($item['service_title']) : '<span class="muted">Genel</span>' ?></td>
                    <td><?= (int) $item['sort_order'] ?></td>
                    <td><?= $item['is_active'] ? '<span class="tag tag-ok">Yayında</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url('/yonetim/yorumlar/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/yorumlar/' . $item['id'] . '/sil')) ?>" data-confirm="Bu yorum silinsin mi?">
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
