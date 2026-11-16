<?php
/** @var array $errors @var array $old @var bool $keyRequired */
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<span class="field-error">' . e($errors[$key]) . '</span>' : '';
};
?>
<h1>İlk yöneticiyi oluşturun</h1>
<p class="muted">Bu sayfa yalnızca henüz hiç yönetici yokken ve bu bilgisayardan açıldığında çalışır. Hesap oluşturulunca kendiliğinden kapanır.</p>
<p class="muted"><small>Bu sayfa yalnızca ilk yönetici oluşturulana kadar açıktır.</small></p>
<form class="form" method="post" action="<?= e(url('/yonetim/kurulum')) ?>" novalidate>
    <?= App\Core\Csrf::field() ?>
    <?php if (!empty($keyRequired)): ?>
    <label>Kurulum anahtarı <small>(.env dosyasındaki SETUP_KEY değeri)</small>
        <input type="password" name="setup_key" autocomplete="off" required>
        <?= $err('setup_key') ?>
    </label>
    <?php endif; ?>
    <label>Ad soyad
        <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>" maxlength="100" autocomplete="name" required>
        <?= $err('name') ?>
    </label>
    <label>E-posta
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="190" autocomplete="username" required>
        <?= $err('email') ?>
    </label>
    <label>Parola <small>(en az <?= App\Core\Auth::MIN_PASSWORD ?> karakter)</small>
        <input type="password" name="password" minlength="<?= App\Core\Auth::MIN_PASSWORD ?>" autocomplete="new-password" required>
        <?= $err('password') ?>
    </label>
    <label>Parola (tekrar)
        <input type="password" name="password_confirm" minlength="<?= App\Core\Auth::MIN_PASSWORD ?>" autocomplete="new-password" required>
    </label>
    <button class="btn" type="submit">Hesabı oluştur</button>
</form>
