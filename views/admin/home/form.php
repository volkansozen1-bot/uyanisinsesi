<?php
/**
 * @var array<string, mixed>|null $row
 * @var string $type
 * @var array<string, mixed> $info
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var array<int, array<string, mixed>> $stats
 * @var string $action
 */
$f = new App\Core\Form($values, $errors);
$hasImage = false;
foreach ($info['fields'] as $field) {
    $hasImage = $hasImage || $field['type'] === 'image';
}
?>
<p class="back-link"><a href="<?= e(url('/yonetim/ana-sayfa')) ?>">← Ana Sayfa</a></p>
<div class="page-title">
    <h1><?= $row ? 'Bloğu düzenle' : 'Yeni blok' ?>: <?= e($info['label']) ?></h1>
    <p class="muted"><?= e($info['desc']) ?></p>
</div>

<form class="form" method="post" action="<?= e(url($action)) ?>"<?= $hasImage ? ' enctype="multipart/form-data"' : '' ?> novalidate>
    <?= App\Core\Csrf::field() ?>
    <?php if (!$row): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
    <fieldset class="panel">
        <legend>İçerik</legend>
        <div class="field-grid">
        <?php foreach ($info['fields'] as $name => $field):
            $o = ['help' => $field['help'] ?? '', 'required' => !empty($field['required'])];
            if (isset($field['max'])) {
                $o['maxlength'] = $field['max'];
            }
            if (!empty($field['wide']) || in_array($field['type'], ['textarea', 'lines'], true)) {
                $o['class'] = 'wide';
            }
            switch ($field['type']) {
                case 'textarea':
                    echo $f->textarea($name, $field['label'], $o + ['rows' => $field['rows'] ?? 3]);
                    break;
                case 'lines':
                    unset($o['maxlength']);
                    echo $f->textarea($name, $field['label'], $o + ['rows' => 6]);
                    break;
                case 'image':
                    echo $f->image($name, $field['label'], array_filter(['help' => $field['help'] ?? null]));
                    break;
                case 'link':
                    $o['help'] = $o['help'] ?: 'Site içi yol (örnek: /hizmetler) ya da https:// ile başlayan adres.';
                    $o['placeholder'] = '/hizmetler';
                    echo $f->text($name, $field['label'], $o);
                    break;
                default:
                    echo $f->text($name, $field['label'], $o);
            }
        endforeach; ?>
        </div>
    </fieldset>
    <fieldset class="panel">
        <legend>Görünüm</legend>
        <?= $f->checkbox('is_active', 'Ana sayfada göster', ['help' => 'İşaretli değilse blok saklanır ama sitede görünmez. Sırayı Ana Sayfa listesindeki oklarla değiştirebilirsiniz.']) ?>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>

<?php if ($type === 'stats' && $row): ?>
<section class="panel">
    <div class="panel-head">
        <h2>Rakamlar</h2>
        <a href="<?= e(url('/yonetim/ana-sayfa/' . $row['id'] . '/rakam/yeni')) ?>">+ Rakam ekle</a>
    </div>
    <?php if (!$stats): ?>
    <p class="empty">Henüz rakam yok. Rakam eklenmezse bu blok sitede görünmez.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Sitede</th><th>Etiket</th><th>Sıra</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
            <tbody>
            <?php foreach ($stats as $stat): $sbase = '/yonetim/ana-sayfa/' . $row['id'] . '/rakam/' . $stat['id']; ?>
                <tr>
                    <td><a href="<?= e(url($sbase)) ?>"><?= e(number_format((int) $stat['value'], 0, ',', '.') . ($stat['suffix'] ?? '')) ?></a></td>
                    <td data-label="Etiket"><?= e($stat['label']) ?></td>
                    <td data-label="Sıra"><?= (int) $stat['sort_order'] ?></td>
                    <td class="cell-actions">
                        <a class="btn-small" href="<?= e(url($sbase)) ?>">Düzenle</a>
                        <form method="post" action="<?= e(url($sbase . '/sil')) ?>" data-confirm="Bu rakam silinsin mi?">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-small btn-small-danger" type="submit">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>
