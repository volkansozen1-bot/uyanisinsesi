<?php
$whatsapp = preg_replace('/\D+/', '', setting('whatsapp_number'));
$columns  = [
    'Keşfet'    => menu('footer_explore'),
    'Hizmetler' => menu('footer_services'),
    'Yasal'     => menu('footer_legal'),
];
// Sosyal medya baglantilari panelden yonetilir (social_links). Ikon yuklenmisse gorsel,
// yuklenmemisse yazi baglantisi gosterilir. Tablo henuz olusmamissa (guncelleme yapilmamis
// kurulum) alt bilgi yine de calisir.
try {
    $social = App\Core\Database::all(
        'SELECT label, url, icon FROM social_links WHERE is_active = 1 ORDER BY sort_order, id'
    );
} catch (Throwable $e) {
    $social = [];
}
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
            <ul class="footer-social">
                <?php foreach ($social as $link): ?>
                <li>
                    <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($link['label']) ?>"<?= $link['icon'] ? ' class="is-icon"' : '' ?>>
                        <?php if ($link['icon']): ?>
                        <img src="<?= e(url($link['icon'])) ?>" alt="<?= e($link['label']) ?>" width="24" height="24" loading="lazy">
                        <?php else: ?>
                        <?= e($link['label']) ?>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
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
