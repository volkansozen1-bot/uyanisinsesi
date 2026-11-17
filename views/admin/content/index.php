<?php
$types = [
    'hero' => 'Karşılama', 'intro' => 'Tanıtım', 'story' => 'Hikâye',
    'services' => 'Hizmetler', 'course_promo' => 'Öne çıkan eğitim',
    'quote' => 'Alıntı', 'stats' => 'Rakamlar', 'testimonials' => 'Geri bildirimler',
    'latest_posts' => 'Son yazılar',
];
?>
<div class="page-title"><h1><?= e($pageTitle) ?></h1></div>
<?php if (!$rows): ?><p>Henüz içerik yok.</p><?php endif; ?>
<?php foreach ($rows as $row): ?>
<details class="panel content-editor">
    <summary>
        <strong><?= e($isHome ? ($types[$row['type']] ?? $row['type']) : $row['title']) ?></strong>
        <span class="muted"><?= e($isHome ? $row['sort_order'] : '/' . $row['slug']) ?></span>
    </summary>
    <form class="form" method="post" action="<?= e(url($path . '/' . $row['id'])) ?>">
        <?= App\Core\Csrf::field() ?>
        <div class="field-grid">
            <?php foreach (($isHome ? $row['editor_fields'] : $fields) as $name => [$label, $limit]): ?>
            <label>
                <?= e($label) ?>
                <?php if ($limit === 0): ?>
                <textarea name="<?= e($name) ?>" rows="8"><?php echo "\n", e($row[$name] ?? ''); ?></textarea>
                <?php if (!$isHome): ?><small class="help">Metni değiştirirseniz içerik düz metin olarak kaydedilir.</small><?php endif; ?>
                <?php else: ?>
                <input name="<?= e($name) ?>" value="<?= e($row[$name] ?? '') ?>" maxlength="<?= $limit ?>" <?= !$isHome && $name === 'title' ? 'required' : '' ?>>
                <?php endif; ?>
            </label>
            <?php endforeach; ?>
            <?php if ($isHome): ?>
            <label>Sıra <input type="number" name="sort_order" value="<?= e($row['sort_order']) ?>" required></label>
            <?php endif; ?>
            <label><?= $isHome ? 'Görünürlük' : 'Yayın durumu' ?>
                <?php $active = $isHome ? (bool) $row['is_active'] : $row['status'] === 'published'; ?>
                <select name="active">
                    <option value="1" <?= $active ? 'selected' : '' ?>><?= $isHome ? 'Görünür' : 'Yayında' ?></option>
                    <option value="0" <?= !$active ? 'selected' : '' ?>><?= $isHome ? 'Gizli' : 'Taslak' ?></option>
                </select>
            </label>
        </div>
        <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
    </form>
</details>
<?php endforeach; ?>
