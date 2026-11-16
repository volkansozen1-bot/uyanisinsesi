<?php
/** @var array<string, int> $stats @var array $latest @var array $admin */
$cards = [
    ['Okunmamış mesaj', $stats['unread'], 'Toplam ' . $stats['messages'] . ' mesaj', '/yonetim/mesajlar'],
    ['Onaylı abone', $stats['confirmed'], 'Toplam ' . $stats['subscribers'] . ' kayıt', '/yonetim/aboneler'],
    ['Yayındaki yazı', $stats['posts'], 'Blog', null],
    ['Aktif hizmet', $stats['services'], 'Hizmetler', null],
];
?>
<div class="page-title">
    <h1>Hoş geldiniz, <?= e(explode(' ', (string) $admin['name'])[0]) ?></h1>
    <p class="muted">Sitenizin kısa özeti.</p>
</div>

<div class="stat-grid">
    <?php foreach ($cards as [$label, $value, $note, $link]): ?>
    <div class="stat-card">
        <span class="stat-label"><?= e($label) ?></span>
        <span class="stat-value"><?= e($value) ?></span>
        <?php if ($link): ?>
        <a class="stat-note" href="<?= e(url($link)) ?>"><?= e($note) ?> &rarr;</a>
        <?php else: ?>
        <span class="stat-note"><?= e($note) ?></span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<section class="panel">
    <div class="panel-head">
        <h2>Son mesajlar</h2>
        <a href="<?= e(url('/yonetim/mesajlar')) ?>">Tümünü gör</a>
    </div>
    <?php if ($latest === []): ?>
    <p class="empty">Henüz mesaj yok.</p>
    <?php else: ?>
    <ul class="message-list">
        <?php foreach ($latest as $m): ?>
        <li class="<?= (int) $m['is_read'] === 0 ? 'is-unread' : '' ?>">
            <a href="<?= e(url('/yonetim/mesajlar/' . $m['id'])) ?>">
                <span class="ml-from"><?= e(trim($m['first_name'] . ' ' . $m['last_name'])) ?></span>
                <span class="ml-text"><?= e(mb_strimwidth((string) $m['message'], 0, 110, '…')) ?></span>
                <time class="ml-date" datetime="<?= e($m['created_at']) ?>"><?= e(tr_date($m['created_at'])) ?></time>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</section>
