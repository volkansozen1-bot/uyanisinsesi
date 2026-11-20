<?php /** @var array $fields @var array $values @var array $errors */
$inputType = ['text' => 'text', 'email' => 'email', 'tel' => 'tel', 'url' => 'url'];
$groups = [
    'Genel'           => ['site_name', 'site_tagline'],
    'İletişim'        => ['contact_email', 'contact_phone', 'whatsapp_number', 'admin_notify_email'],
];
?>
<div class="page-title">
    <h1>Ayarlar</h1>
    <p class="muted">Sitenin genelinde kullanılan bilgiler.</p>
</div>

<form class="form" method="post" action="<?= e(url('/yonetim/ayarlar')) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <?php foreach ($groups as $title => $keys): ?>
    <fieldset class="panel">
        <legend><?= e($title) ?></legend>
        <div class="field-grid">
        <?php foreach ($keys as $key): [$label, $type, $help] = $fields[$key]; ?>
            <label>
                <?= e($label) ?>
                <input type="<?= e($inputType[$type]) ?>" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>" maxlength="255"
                    <?= $type === 'url' ? 'placeholder="https://"' : '' ?><?= isset($errors[$key]) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors[$key])): ?>
                <span class="field-error"><?= e($errors[$key]) ?></span>
                <?php else: ?>
                <small class="help"><?= e($help) ?></small>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
        </div>
    </fieldset>
    <?php endforeach; ?>
    <fieldset class="panel">
        <legend>Sosyal medya</legend>
        <p class="muted">Instagram, YouTube, Spotify ve diğer bağlantılar artık
            <a href="<?= e(url('/yonetim/sosyal-medya')) ?>">Sosyal Medya</a> bölümünden yönetiliyor.</p>
    </fieldset>
    <div class="form-actions"><button class="btn" type="submit">Kaydet</button></div>
</form>
