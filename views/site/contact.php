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
<header class="page-head">
    <div class="container narrow">
        <h1>İletişim</h1>
    </div>
</header>
<?php
$mail  = setting('contact_email');
$phone = setting('contact_phone');
$wa    = preg_replace('/\D+/', '', setting('whatsapp_number'));
$ico   = static fn (string $p): string => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
?>
<section class="section contact-wrap">
    <div class="container contact-grid">
        <aside class="contact-info">
            <span class="eyebrow">Bağlantıda kal</span>
            <h2>Sana nasıl destek olabilirim?</h2>
            <p class="muted">Sorunu, merak ettiğini ya da almak istediğin hizmeti yaz; en kısa sürede dönüş yapayım.</p>
            <ul class="contact-items">
                <?php if ($mail): ?>
                <li><span class="contact-ico"><?= $ico('<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>') ?></span>
                    <div><strong>E-posta</strong><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></div></li>
                <?php endif; ?>
                <?php if ($phone): ?>
                <li><span class="contact-ico"><?= $ico('<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>') ?></span>
                    <div><strong>Telefon</strong><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a></div></li>
                <?php endif; ?>
                <?php if ($wa): ?>
                <li><span class="contact-ico"><?= $ico('<path d="M4 20l1.300-4A8 8 0 1 1 8 18.700z"/><path d="M9 9.500c.5 2 2 3.500 4 4l1-1 1.500.8c-.3 1.200-1.300 1.700-2.500 1.400A6 6 0 0 1 8.500 10c-.3-1.200.2-2.200 1.400-2.500l.8 1.500z"/>') ?></span>
                    <div><strong>WhatsApp</strong><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">Hemen mesaj gönder</a></div></li>
                <?php endif; ?>
            </ul>
            <?php if ($social = social_links()): ?>
            <div class="contact-social">
                <?php foreach ($social as $link): ?>
                <a class="social-btn social-<?= e($link['platform']) ?>" href="<?= e($link['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($link['label']) ?>"><?= $link['icon'] ? '<img src="' . e(url($link['icon'])) . '" alt="">' : social_glyph($link['platform']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </aside>

        <div class="contact-card">
        <h2>Bana yaz</h2>
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
    </div>
</section>
