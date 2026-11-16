<?php /** @var array $message */
$name = trim($message['first_name'] . ' ' . $message['last_name']);
?>
<p class="back-link"><a href="<?= e(url('/yonetim/mesajlar')) ?>">&larr; Gelen mesajlar</a></p>

<section class="panel">
    <div class="panel-head">
        <h1 class="h2"><?= e($name) ?></h1>
        <time class="muted" datetime="<?= e($message['created_at']) ?>"><?= e(tr_date($message['created_at'])) ?>, <?= e(substr((string) $message['created_at'], 11, 5)) ?></time>
    </div>
    <dl class="meta-list">
        <div><dt>E-posta</dt><dd><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></dd></div>
        <?php if (!empty($message['phone'])): ?>
        <div><dt>Telefon</dt><dd><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $message['phone'])) ?>"><?= e($message['phone']) ?></a></dd></div>
        <?php endif; ?>
    </dl>
    <div class="message-body"><?= nl2br(e($message['message'])) ?></div>

    <div class="actions">
        <a class="btn" href="mailto:<?= e($message['email']) ?>?subject=<?= e(rawurlencode('Mesajınız hakkında')) ?>">E-postayla yanıtla</a>
        <form method="post" action="<?= e(url('/yonetim/mesajlar/' . $message['id'] . '/sil')) ?>" data-confirm="Bu mesaj kalıcı olarak silinecek. Emin misiniz?">
            <?= App\Core\Csrf::field() ?>
            <button class="btn btn-danger" type="submit">Mesajı sil</button>
        </form>
    </div>
</section>
