<?php
/**
 * E-posta iskeleti. Degiskenler: $content (alt sablonun HTML ciktisi), $title, $preheader (istege bagli).
 * E-posta istemcileri harici CSS'i desteklemedigi icin tum stiller satir ici ve tablo tabanli.
 */
$siteName  = setting('site_name', 'Site');
$title     = $title ?? $siteName;
$preheader = $preheader ?? '';
// Host basligina guvenilmez; adres yalnizca APP_URL'den (ya da yerel gelistirmede istekten) gelir
$siteUrl   = trusted_url('/');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
</head>
<body style="margin:0;padding:0;background-color:#fbf7f0;">
<?php if ($preheader !== ''): ?>
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#fbf7f0;"><?= e($preheader) ?></div>
<?php endif; ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fbf7f0;">
  <tr>
    <td align="center" style="padding:32px 12px;">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #eadfcb;">
        <tr>
          <td align="center" style="background-color:#1d1730;padding:28px 32px;border-bottom:3px solid #c39a4b;">
            <span style="font-family:Georgia,'Times New Roman',serif;font-size:26px;line-height:32px;color:#c39a4b;letter-spacing:1px;"><?= e($siteName) ?></span>
          </td>
        </tr>
        <tr>
          <td style="padding:36px 40px 32px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:26px;color:#2b2540;">
            <?= $content /* alt sablon kendi degiskenlerini e() ile kacirir */ ?>
          </td>
        </tr>
        <tr>
          <td align="center" style="background-color:#1d1730;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#cfc6dd;">
            <?= e($siteName) ?><?php if ($siteUrl !== null): ?> &middot; <a href="<?= e($siteUrl) ?>" style="color:#c39a4b;text-decoration:none;"><?= e($siteUrl) ?></a><?php endif; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
