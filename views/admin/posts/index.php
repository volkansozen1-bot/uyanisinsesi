<?php
/**
 * @var array<int, array<string, mixed>> $items
 * @var array<string, int> $pager
 * @var array<string, string> $filters
 * @var array<int, string> $categories
 * @var string $now
 */
use App\Controllers\Admin\PostAdminController;

$filtered = $filters['q'] !== '' || $filters['durum'] !== '' || $filters['kategori'] !== '';
?>
<div class="page-title page-title-row">
    <div>
        <h1>Blog</h1>
        <p class="muted">Blog yazıları. Toplam <?= e($pager['total']) ?> yazı. <a href="<?= e(url('/yonetim/blog/kategoriler')) ?>">Kategorileri yönet</a></p>
    </div>
    <a class="btn" href="<?= e(url('/yonetim/blog/yeni')) ?>">+ Yeni yazı</a>
</div>

<div class="panel">
    <form class="filter-bar" method="get" action="<?= e(url('/yonetim/blog')) ?>" role="search">
        <label class="grow">Başlıkta ara
            <input type="search" name="q" value="<?= e($filters['q']) ?>">
        </label>
        <label>Durum
            <select name="durum">
                <option value="">Tümü</option>
                <?php foreach (PostAdminController::STATUSES as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $filters['durum'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Kategori
            <select name="kategori">
                <option value="">Tümü</option>
                <option value="yok"<?= $filters['kategori'] === 'yok' ? ' selected' : '' ?>>Kategorisiz</option>
                <?php foreach ($categories as $id => $name): ?>
                <option value="<?= e($id) ?>"<?= $filters['kategori'] === (string) $id ? ' selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-outline" type="submit">Süz</button>
        <?php if ($filtered): ?><a class="btn-small" href="<?= e(url('/yonetim/blog')) ?>">Temizle</a><?php endif; ?>
    </form>

    <?php if (!$items): ?>
    <p class="empty">
        <?php if ($filtered): ?>Aramanıza uyan yazı yok.
        <?php else: ?>Henüz yazı yok. <a href="<?= e(url('/yonetim/blog/yeni')) ?>">İlk yazıyı ekleyin.</a><?php endif; ?>
    </p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Kategori</th><th>Durum</th><th>Yayın tarihi</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): [$statusLabel, $statusClass] = PostAdminController::statusOf($item, $now); ?>
                <tr>
                    <td><a href="<?= e(url('/yonetim/blog/' . $item['id'])) ?>"><?= e($item['title']) ?></a></td>
                    <td data-label="Kategori"><?= $item['category_name'] !== null ? e($item['category_name']) : '<span class="muted">Yok</span>' ?></td>
                    <td data-label="Durum"><span class="tag <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                    <td data-label="Yayın tarihi" class="nowrap">
                        <?php if (!empty($item['published_at'])): ?>
                        <?= e(tr_date($item['published_at'])) ?><small class="muted d-block"><?= e(substr((string) $item['published_at'], 11, 5)) ?></small>
                        <?php else: ?><span class="muted">—</span><?php endif; ?>
                    </td>
                    <td class="cell-actions">
                        <?php if ($statusLabel === 'Yayında'): ?>
                        <a class="btn-small" href="<?= e(url('/blog/' . $item['slug'])) ?>" target="_blank" rel="noopener">Sitede gör</a>
                        <?php endif; ?>
                        <a class="btn-small" href="<?= e(url('/yonetim/blog/' . $item['id'])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url('/yonetim/blog/' . $item['id'] . '/sil')) ?>" data-confirm="&quot;<?= e($item['title']) ?>&quot; yazısı kalıcı olarak silinsin mi?">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= App\Core\View::renderFile('admin/partials/pagination', ['pager' => $pager, 'base' => '/yonetim/blog', 'query' => $filters]) ?>
    <?php endif; ?>
</div>
