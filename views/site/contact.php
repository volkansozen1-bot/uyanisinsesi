<?php
/**
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */
use App\Core\Csrf;

$success = flash('success');
$error   = flash('error');
$field   = static fn (string $key): string => e($old[$key] ?? '');
?>
<section class="section">
    <div class="container narrow">
        <h1>İletişim</h1>
        <?php if ($success): ?><p class="notice notice-success" role="status"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>

        <form method="post" action="<?= e(url('/iletisim')) ?>" class="form" novalidate>
            <?= Csrf::field() ?>
            <div class="hp" aria-hidden="true">
                <label>Web siteniz <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="form-row">
                <label>Ad
                    <input type="text" name="first_name" value="<?= $field('first_name') ?>" maxlength="100" required autocomplete="given-name">
                    <?php if (isset($errors['first_name'])): ?><span class="field-error"><?= e($errors['first_name']) ?></span><?php endif; ?>
                </label>
                <label>Soyad
                    <input type="text" name="last_name" value="<?= $field('last_name') ?>" maxlength="100" autocomplete="family-name">
                    <?php if (isset($errors['last_name'])): ?><span class="field-error"><?= e($errors['last_name']) ?></span><?php endif; ?>
                </label>
            </div>
            <div class="form-row">
                <label>E-posta
                    <input type="email" name="email" value="<?= $field('email') ?>" maxlength="190" required autocomplete="email">
                    <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
                </label>
                <label>Telefon
                    <input type="tel" name="phone" value="<?= $field('phone') ?>" maxlength="30" autocomplete="tel">
                    <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                </label>
            </div>
            <label>Mesajınız
                <textarea name="message" rows="6" maxlength="5000" required><?= $field('message') ?></textarea>
                <?php if (isset($errors['message'])): ?><span class="field-error"><?= e($errors['message']) ?></span><?php endif; ?>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="consent" value="1" required>
                <span><a href="<?= e(url('/kvkk-aydinlatma-metni')) ?>" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>'ni okudum.</span>
            </label>
            <?php if (isset($errors['consent'])): ?><span class="field-error"><?= e($errors['consent']) ?></span><?php endif; ?>

            <button type="submit" class="btn">Gönder</button>
        </form>
    </div>
</section>
