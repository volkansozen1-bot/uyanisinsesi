<?php
$whatsapp = preg_replace('/\D+/', '', setting('whatsapp_number'));
$columns  = [
    'Keşfet'    => menu('footer_explore'),
    'Hizmetler' => menu('footer_services'),
    'Yasal'     => menu('footer_legal'),
];
$social = array_filter([
    'Instagram' => setting('instagram_url'),
    'YouTube'   => setting('youtube_url'),
    'Spotify'   => setting('spotify_url'),
]);
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <span class="footer-brand"><?= e(setting('site_name', 'Perihan Ateş')) ?></span>
            <?php if (setting('site_tagline')): ?><p><?= e(setting('site_tagline')) ?></p><?php endif; ?>
            <?php if (setting('contact_email')): ?>
            <p><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p>
            <?php endif; ?>
            <?php if (setting('contact_phone')): ?><p><?= e(setting('contact_phone')) ?></p><?php endif; ?>
            <?php if ($social): ?>
            <p>
                <?php $i = 0; foreach ($social as $name => $link): ?><?= $i++ ? ' · ' : '' ?><a href="<?= e($link) ?>" target="_blank" rel="noopener"><?= e($name) ?></a><?php endforeach; ?>
            </p>
            <?php endif; ?>
        </div>
        <?php foreach ($columns as $title => $items): ?>
        <?php if (!$items) { continue; } ?>
        <nav aria-label="<?= e($title) ?>">
            <span class="footer-title"><?= e($title) ?></span>
            <ul>
                <?php foreach ($items as $item): ?>
                <li><a href="<?= e(url($item['url'])) ?>"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endforeach; ?>
    </div>
    <div class="container footer-bottom">
        <small>&copy; <?= date('Y') ?> <?= e(setting('site_name', 'Perihan Ateş')) ?>. Tüm hakları saklıdır.</small>
    </div>
</footer>
<?php if ($whatsapp): ?>
<a class="whatsapp-fab" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın">WhatsApp</a>
<?php endif; ?>
