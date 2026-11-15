<?php
$whatsapp = preg_replace('/\D+/', '', setting('whatsapp_number'));
$legal    = menu('footer_legal');
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <strong><?= e(setting('site_name', 'Uyanışın Sesi')) ?></strong>
            <?php if (setting('contact_email')): ?>
            <p><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p>
            <?php endif; ?>
            <?php if (setting('contact_phone')): ?>
            <p><?= e(setting('contact_phone')) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($legal): ?>
        <nav aria-label="Yasal">
            <strong>Yasal</strong>
            <ul>
                <?php foreach ($legal as $item): ?>
                <li><a href="<?= e(url($item['url'])) ?>"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <div class="container footer-bottom">
        <small>&copy; <?= date('Y') ?> <?= e(setting('site_name', 'Uyanışın Sesi')) ?></small>
    </div>
</footer>
<?php if ($whatsapp): ?>
<a class="whatsapp-fab" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın">WhatsApp</a>
<?php endif; ?>
