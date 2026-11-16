<?php
/**
 * @var array<string, int> $pager  AdminController::paginate() ciktisi
 * @var string $base  ornek: '/yonetim/mesajlar'
 * @var array<string, string>|null $query  sayfa disindaki GET parametreleri
 */
if ($pager['pages'] <= 1) {
    return;
}
$link = static function (int $page) use ($base, $query): string {
    $params = array_filter($query ?? [], static function ($v) { return $v !== ''; });
    if ($page > 1) {
        $params['sayfa'] = $page;
    }
    return url($base) . ($params ? '?' . http_build_query($params) : '');
};
?>
<nav class="pager" aria-label="Sayfalar">
    <?php if ($pager['page'] > 1): ?>
    <a href="<?= e($link($pager['page'] - 1)) ?>" rel="prev">&larr; Önceki</a>
    <?php endif; ?>
    <span>Sayfa <?= e($pager['page']) ?> / <?= e($pager['pages']) ?></span>
    <?php if ($pager['page'] < $pager['pages']): ?>
    <a href="<?= e($link($pager['page'] + 1)) ?>" rel="next">Sonraki &rarr;</a>
    <?php endif; ?>
</nav>
