<?php /** @var array $messages @var array $pager */ ?>
<div class="page-title">
    <h1>Gelen Mesajlar</h1>
    <p class="muted">İletişim formundan gelen mesajlar. Toplam <?= e($pager['total']) ?> mesaj.</p>
</div>

<section class="panel">
    <?php if ($messages === []): ?>
    <p class="empty">Henüz mesaj yok.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table table-messages">
            <thead>
                <tr><th>Gönderen</th><th>Mesaj</th><th>Tarih</th><th><span class="sr-only">İşlem</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $m): $unread = (int) $m['is_read'] === 0; ?>
                <tr class="<?= $unread ? 'is-unread' : '' ?>">
                    <td data-label="Gönderen">
                        <a href="<?= e(url('/yonetim/mesajlar/' . $m['id'])) ?>"><?= e(trim($m['first_name'] . ' ' . $m['last_name'])) ?></a>
                        <?php if ($unread): ?><span class="dot" title="Okunmadı"></span><span class="sr-only">(okunmadı)</span><?php endif; ?>
                        <small class="muted d-block"><?= e($m['email']) ?></small>
                    </td>
                    <td data-label="Mesaj" class="cell-text"><?= e(mb_strimwidth((string) $m['message'], 0, 120, '…')) ?></td>
                    <td data-label="Tarih" class="nowrap"><?= e(tr_date($m['created_at'])) ?><small class="muted d-block"><?= e(substr((string) $m['created_at'], 11, 5)) ?></small></td>
                    <td class="cell-actions"><a class="btn-small" href="<?= e(url('/yonetim/mesajlar/' . $m['id'])) ?>">Aç</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= App\Core\View::renderFile('admin/partials/pagination', ['pager' => $pager, 'base' => '/yonetim/mesajlar', 'query' => []]) ?>
    <?php endif; ?>
</section>
