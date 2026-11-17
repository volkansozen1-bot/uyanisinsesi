<?php use App\Controllers\Admin\CollectionController; ?>
<div class="page-title"><h1><?= e($pageTitle) ?></h1></div>
<?php foreach ([['id' => 'new'], ...$rows] as $row): ?>
<?php $creating = $row['id'] === 'new'; ?>
<details class="panel content-editor">
    <summary><strong><?= e($creating ? 'Yeni kayıt ekle' : ($row['title'] ?? $row['name'])) ?></strong></summary>
    <form class="form" method="post" action="<?= e(url($path . '/' . $row['id'])) ?>">
        <?= App\Core\Csrf::field() ?>
        <div class="field-grid">
            <?php foreach ($fields as $key => [$label, $limit]): ?>
            <?php $value = (string) ($row[$key] ?? ($key === 'rating' ? '5' : '')); ?>
            <label><?= e($label) ?>
                <?php if ($limit === 0): ?>
                <textarea name="<?= e($key) ?>" rows="6"><?php echo "\n", e($key === 'body' ? CollectionController::plainBody($value) : $value); ?></textarea>
                <?php else: ?>
                <input name="<?= e($key) ?>" type="<?= $key === 'start_date' ? 'date' : 'text' ?>" maxlength="<?= $limit ?>" value="<?= e($value) ?>" <?= in_array($key, ['title', 'name', 'slug'], true) ? 'required' : '' ?>>
                <?php endif; ?>
            </label>
            <?php endforeach; ?>
            <label>Yayın durumu
                <?php $active = isset($row['status']) ? $row['status'] === 'published' : !empty($row['is_active']); ?>
                <select name="active">
                    <option value="0" <?= $active ? '' : 'selected' ?>>Taslak</option>
                    <option value="1" <?= $active ? 'selected' : '' ?>>Yayında</option>
                </select>
            </label>
        </div>
        <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
    </form>
</details>
<?php endforeach; ?>
