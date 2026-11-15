<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= e(url('/')) ?>"><?= e(setting('site_name', 'Uyanışın Sesi')) ?></a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="ana-menu">Menü</button>
        <nav id="ana-menu" class="site-nav" aria-label="Ana menü">
            <ul>
                <?php foreach (menu('header') as $item): ?>
                <li><a href="<?= e(url($item['url'])) ?>"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>
