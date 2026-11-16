<?php
/** @var array $subscribers @var array $pager @var array $filters */
use App\Controllers\Admin\SubscriberController;

$statuses = ['' => 'Tümü', 'bekliyor' => 'Onay bekliyor', 'onayli' => 'Onaylı', 'cikmis' => 'Çıkmış'];
$classes  = ['Onaylı' => 'tag-ok', 'Onay bekliyor' => 'tag-wait', 'Çıkmış' => 'tag-off'];
$exportQs = http_build_query(array_filter($filters, static function ($v) { return $v !== ''; }));
?>
<div class="page-title">
    <h1>Bülten Aboneleri</h1>
    <p class="muted"><?= e($pager['total']) ?> kayıt. KVKK kapsamında silme talebi gelen kişinin kaydını buradan kalıcı olarak silebilirsiniz.</p>
</div>

<section class="panel">
    <form class="filter-bar" method="get" action="<?= e(url('/yonetim/aboneler')) ?>" role="search">
        <label class="grow">E-posta ya da ad
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="ornek@eposta.com">
        </label>
        <label>Durum
            <select name="durum">
                <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $filters['durum'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-outline" type="submit">Ara</button>
        <a class="btn" href="<?= e(url('/yonetim/aboneler/disa-aktar') . ($exportQs !== '' ? '?' . $exportQs : '')) ?>">CSV indir</a>
    </form>

    <?php if ($subscribers === []): ?>
    <p class="empty"><?= $filters['q'] !== '' || $filters['durum'] !== '' ? 'Aramanıza uyan abone yok.' : 'Henüz abone yok.' ?></p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>E-posta</th><th>Ad</th><th>Durum</th><th>Kayıt</th><th><span class="sr-only">İşlem</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($subscribers as $s): $status = SubscriberController::statusLabel($s); ?>
                <tr>
                    <td data-label="E-posta" class="cell-email"><?= e($s['email']) ?></td>
                    <td data-label="Ad"><?= e($s['name'] ?: '—') ?></td>
                    <td data-label="Durum"><span class="tag <?= e($classes[$status]) ?>"><?= e($status) ?></span></td>
                    <td data-label="Kayıt" class="nowrap"><?= e(tr_date($s['created_at'])) ?></td>
                    <td class="cell-actions">
                        <form method="post" action="<?= e(url('/yonetim/aboneler/' . $s['id'] . '/sil')) ?>" data-confirm="<?= e($s['email']) ?> adresi ve tüm kayıt bilgileri kalıcı olarak silinecek. Emin misiniz?">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= App\Core\View::renderFile('admin/partials/pagination', ['pager' => $pager, 'base' => '/yonetim/aboneler', 'query' => $filters]) ?>
    <?php endif; ?>
</section>
