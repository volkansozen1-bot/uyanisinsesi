<?php
/** @var array<int, array<string, mixed>> $items */
use App\Controllers\Admin\HomeBlockAdminController;

$last = count($items) - 1;
?>
<div class="page-title page-title-row">
    <div>
        <h1>Ana Sayfa</h1>
        <p class="muted">Ana sayfadaki bölümler yukarıdan aşağıya bu sırayla görünür. <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Sitede gör ↗</a></p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/ana-sayfa/yeni')) ?>">+ Yeni blok</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Ana sayfada hiç blok yok. <a href="<?= e(url('/yonetim/ana-sayfa/yeni')) ?>">İlk bloğu ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table table-blocks">
            <thead><tr><th>Sıra</th><th>Tür</th><th>Başlık</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $i => $item):
                $info  = HomeBlockAdminController::typeInfo((string) $item['type']);
                $title = trim((string) ($item['title'] ?: $item['subtitle']));
                $base  = '/yonetim/ana-sayfa/' . $item['id'];
            ?>
                <tr id="blok-<?= (int) $item['id'] ?>">
                    <td class="cell-move">
                        <form method="post" action="<?= e(url($base . '/yukari')) ?>">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-move" type="submit"<?= $i === 0 ? ' disabled' : '' ?> aria-label="<?= e($info['label']) ?> bloğunu yukarı taşı" title="Yukarı taşı">↑</button>
                        </form>
                        <form method="post" action="<?= e(url($base . '/asagi')) ?>">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-move" type="submit"<?= $i === $last ? ' disabled' : '' ?> aria-label="<?= e($info['label']) ?> bloğunu aşağı taşı" title="Aşağı taşı">↓</button>
                        </form>
                    </td>
                    <td data-label="Tür"><a href="<?= e(url($base)) ?>"><?= e($info['label']) ?></a></td>
                    <td class="cell-text" data-label="Başlık"><?= $title !== '' ? e(mb_strimwidth($title, 0, 80, '…')) : '<span class="muted">—</span>' ?></td>
                    <td data-label="Durum"><?= $item['is_active'] ? '<span class="tag tag-ok">Görünür</span>' : '<span class="tag tag-off">Gizli</span>' ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($base)) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($base . '/gorunurluk')) ?>">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small" type="submit"><?= $item['is_active'] ? 'Gizle' : 'Göster' ?></button>
                        </form>
                        <form method="post" action="<?= e(url($base . '/sil')) ?>" data-confirm="&quot;<?= e($info['label']) ?>&quot; bloğu ana sayfadan silinsin mi? Bunun yerine Gizle de diyebilirsiniz.">
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
