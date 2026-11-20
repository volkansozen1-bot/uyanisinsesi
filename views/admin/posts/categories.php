<?php /** @var array<int, array<string, mixed>> $items */ ?>
<p class="back-link"><a href="<?= e(url('/yonetim/blog')) ?>">← Blog</a></p>
<div class="page-title page-title-row">
    <div>
        <h1>Blog kategorileri</h1>
        <p class="muted">Blog sayfasındaki kategori bağlantıları. Her yazı en fazla bir kategoride olabilir.</p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/blog/kategoriler/yeni')) ?>">+ Yeni kategori</a>
</div>

<div class="panel">
    <?php if (!$items): ?>
    <p class="empty">Henüz kategori yok. <a href="<?= e(url('/yonetim/blog/kategoriler/yeni')) ?>">İlk kategoriyi ekleyin.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ad</th><th>Adres</th><th>Yazı sayısı</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item):
                $count = (int) $item['post_count'];
                $confirm = '"' . $item['name'] . '" kategorisi silinsin mi?'
                    . ($count > 0 ? ' Bu kategorideki ' . $count . ' yazı silinmez, kategorisiz kalır.' : '');
            ?>
                <tr>
                    <td><a href="<?= e(url('/yonetim/blog/kategoriler/' . $item['id'])) ?>"><?= e($item['name']) ?></a></td>
                    <td data-label="Adres" class="muted">/blog/kategori/<?= e($item['slug']) ?></td>
                    <td data-label="Yazı sayısı">
                        <?php if ($count > 0): ?>
                        <a href="<?= e(url('/yonetim/blog') . '?kategori=' . (int) $item['id']) ?>"><?= $count ?></a>
                        <?php else: ?>0<?php endif; ?>
                    </td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url('/blog/kategori/' . $item['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
                        <a class="btn-small" href="<?= e(url('/yonetim/blog/kategoriler/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/blog/kategoriler/' . $item['id'] . '/sil')) ?>" data-confirm="<?= e($confirm) ?>">
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
