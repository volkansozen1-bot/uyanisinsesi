<?php
/**
 * Yoneticiye iletisim formu bildirimi.
 * Degiskenler: $firstName, $lastName, $email, $phone, $message, $ip, $sentAt
 */
$label = 'font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#7a7088;padding:6px 12px 6px 0;vertical-align:top;white-space:nowrap;';
$value = 'font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#2b2540;padding:6px 0;vertical-align:top;';
?>
<h1 style="margin:0 0 8px;font-family:Georgia,'Times New Roman',serif;font-size:24px;line-height:30px;font-weight:normal;color:#1d1730;">Yeni iletişim mesajı</h1>
<p style="margin:0 0 24px;color:#7a7088;font-size:14px;">Sitedeki iletişim formundan bir mesaj geldi. Bu e-postayı yanıtladığınızda yanıtınız doğrudan ziyaretçiye gider.</p>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
  <tr><td style="<?= $label ?>">Ad Soyad</td><td style="<?= $value ?>"><?= e(trim($firstName . ' ' . $lastName)) ?></td></tr>
  <tr><td style="<?= $label ?>">E-posta</td><td style="<?= $value ?>"><a href="mailto:<?= e($email) ?>" style="color:#8a6a2c;"><?= e($email) ?></a></td></tr>
  <?php if ($phone !== ''): ?>
  <tr><td style="<?= $label ?>">Telefon</td><td style="<?= $value ?>"><?= e($phone) ?></td></tr>
  <?php endif; ?>
  <tr><td style="<?= $label ?>">Tarih</td><td style="<?= $value ?>"><?= e($sentAt) ?></td></tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td style="background-color:#fbf7f0;border-left:3px solid #c39a4b;padding:16px 20px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#2b2540;">
      <?= nl2br(e($message)) ?>
    </td>
  </tr>
</table>

<p style="margin:24px 0 0;color:#7a7088;font-size:12px;">IP adresi: <?= e($ip) ?></p>
