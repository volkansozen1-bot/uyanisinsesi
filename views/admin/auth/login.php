<?php /** @var string $email */ ?>
<h1>Giriş yap</h1>
<form class="form" method="post" action="<?= e(url('/yonetim/giris')) ?>">
    <?= App\Core\Csrf::field() ?>
    <label>E-posta
        <input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
    </label>
    <label>Parola
        <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button class="btn" type="submit">Giriş yap</button>
</form>
<p class="bare-foot"><a href="<?= e(url('/')) ?>">&larr; Siteye dön</a></p>
