<?php
/**
 * Yonetim paneli duzeni.
 * @var string $content
 * @var string|null $pageTitle
 * @var bool|null $bare  true: giris/kurulum gibi menusuz sayfa
 * @var array|null $admin
 */
use App\Core\Csrf;
use App\Core\View;

$siteName = setting('site_name', 'Perihan Ateş');
$bare     = !empty($bare);
$success  = flash('admin_success');
$error    = flash('admin_error');
$icon     = static function (string $name): string {
    return View::renderFile('admin/partials/icon', ['name' => $name]);
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e(($pageTitle ?? 'Yönetim') . ' | Yönetim | ' . $siteName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/logo-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<?php if ($bare): ?>
<body class="admin-bare">
    <main class="bare-card">
        <div class="bare-brand">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="44" height="44">
            <span><?= e($siteName) ?></span>
            <small>Yönetim paneli</small>
        </div>
        <?php if ($success): ?><p class="notice notice-success" role="status"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <?= $content ?>
    </main>
</body>
<?php else:
    $menu    = require BASE_PATH . '/config/admin_menu.php';
    $current = current_path();
?>
<body class="admin">
    <a class="skip-link" href="#icerik">İçeriğe geç</a>
    <aside class="sidebar" id="yonetim-menu">
        <a class="sidebar-brand" href="<?= e(url('/yonetim')) ?>">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="34" height="34">
            <span><?= e($siteName) ?></span>
        </a>
        <nav aria-label="Yönetim menüsü">
            <ul>
            <?php foreach ($menu as $item):
                $url    = $item['url'] ?? null;
                $active = $url !== null && ($current === $url || ($url !== '/yonetim' && str_starts_with($current, $url . '/')));
                $badge  = isset($item['badge']) && $url !== null ? (int) ($item['badge'])() : 0;
            ?>
                <?php if (!empty($item['group'])): ?>
                <li class="menu-group"><?= e($item['group']) ?></li>
                <?php endif; ?>
                <li>
                <?php if ($url === null): ?>
                    <span class="menu-link is-disabled" aria-disabled="true">
                        <?= $icon($item['icon'] ?? 'file') ?><span class="menu-label"><?= e($item['label']) ?></span>
                        <span class="soon">yakında</span>
                    </span>
                <?php else: ?>
                    <a class="menu-link<?= $active ? ' is-active' : '' ?>" href="<?= e(url($url)) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                        <?= $icon($item['icon'] ?? 'file') ?><span class="menu-label"><?= e($item['label']) ?></span>
                        <?php if ($badge > 0): ?><span class="badge" title="<?= e($badge) ?> okunmamış"><?= e($badge) ?></span><?php endif; ?>
                    </a>
                <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="topbar">
            <button class="menu-toggle" type="button" aria-controls="yonetim-menu" aria-expanded="false">
                <?= $icon('menu') ?><span>Menü</span>
            </button>
            <span class="topbar-title"><?= e($pageTitle ?? 'Yönetim') ?></span>
            <div class="topbar-user">
                <span class="user-name"><?= e($admin['name'] ?? '') ?></span>
                <a class="topbar-link" href="<?= e(url('/')) ?>" target="_blank" rel="noopener" title="Siteyi yeni sekmede aç"><?= $icon('external') ?><span>Siteyi görüntüle</span></a>
                <form method="post" action="<?= e(url('/yonetim/cikis')) ?>">
                    <?= Csrf::field() ?>
                    <button class="topbar-link" type="submit" title="Çıkış yap"><?= $icon('logout') ?><span>Çıkış</span></button>
                </form>
            </div>
        </header>

        <main id="icerik" class="admin-content">
            <?php if ($success): ?><p class="notice notice-success" role="status"><?= e($success) ?></p><?php endif; ?>
            <?php if ($error): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>
            <?= $content ?>
        </main>
    </div>
    <div class="sidebar-backdrop" hidden></div>
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
<?php endif; ?>
</html>
