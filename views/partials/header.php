<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= e(url('/')) ?>">
            <img class="brand-logo" src="<?= e(site_logo()['url']) ?>" alt="" height="36">
            <span><?= e(setting('site_name', 'Perihan Ateş')) ?></span>
        </a>
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
