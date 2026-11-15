<?php
use App\Core\Csrf;

$success = flash('newsletter_success');
$error   = flash('newsletter_error');
$path    = current_path();
?>
<section class="newsletter" id="bulten">
    <div class="container narrow">
        <h2>Bülten</h2>
        <?php if ($success): ?><p class="notice notice-success" role="status"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="post" action="<?= e(url('/bulten')) ?>#bulten" class="newsletter-form">
            <?= Csrf::field() ?>
            <input type="hidden" name="back" value="<?= e($path) ?>">
            <div class="hp" aria-hidden="true">
                <label>Web siteniz <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <label>Adınız
                <input type="text" name="name" maxlength="150" autocomplete="name">
            </label>
            <label>E-posta
                <input type="email" name="email" maxlength="190" required autocomplete="email">
            </label>
            <button type="submit" class="btn">Abone Ol</button>
        </form>
    </div>
</section>
