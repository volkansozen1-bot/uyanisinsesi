<?php /** @var array $errors */
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<span class="field-error">' . e($errors[$key]) . '</span>' : '';
};
?>
<div class="page-title">
    <h1>Parolam</h1>
    <p class="muted">Parolanızı değiştirdiğinizde diğer cihazlardaki oturumlarınız kapanır.</p>
</div>

<section class="panel panel-narrow">
    <form class="form" method="post" action="<?= e(url('/yonetim/parola')) ?>">
        <?= App\Core\Csrf::field() ?>
        <input type="text" name="username" value="<?= e($admin['email']) ?>" autocomplete="username" hidden>
        <label>Mevcut parola
            <input type="password" name="current_password" autocomplete="current-password" required>
            <?= $err('current_password') ?>
        </label>
        <label>Yeni parola <small>(en az <?= App\Core\Auth::MIN_PASSWORD ?> karakter)</small>
            <input type="password" name="password" minlength="<?= App\Core\Auth::MIN_PASSWORD ?>" autocomplete="new-password" required>
            <?= $err('password') ?>
        </label>
        <label>Yeni parola (tekrar)
            <input type="password" name="password_confirm" minlength="<?= App\Core\Auth::MIN_PASSWORD ?>" autocomplete="new-password" required>
        </label>
        <button class="btn" type="submit">Parolayı değiştir</button>
    </form>
</section>
