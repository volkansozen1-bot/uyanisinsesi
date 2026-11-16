<?php
/**
 * Bulten cift onay e-postasi.
 * Degiskenler: $confirmUrl
 * Formdaki ad bilerek kullanilmaz (spam/oltalama metni tasiyabilir); cikis baglantisi yok (henuz abone degil).
 */
?>
<h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-size:24px;line-height:30px;font-weight:normal;color:#1d1730;">Bülten kaydınızı onaylayın</h1>
<p style="margin:0 0 16px;">Merhaba,</p>
<p style="margin:0 0 24px;">Bültenimize kaydolmak için bu adres kullanıldı. Kaydı tamamlamak için aşağıdaki düğmeye tıklayın.</p>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
  <tr>
    <td align="center" style="background-color:#c39a4b;border-radius:4px;">
      <a href="<?= e($confirmUrl) ?>" style="display:inline-block;padding:14px 28px;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;color:#1d1730;text-decoration:none;">Aboneliğimi onayla</a>
    </td>
  </tr>
</table>

<p style="margin:0 0 8px;font-size:14px;color:#7a7088;">Düğme çalışmazsa bu bağlantıyı tarayıcınıza yapıştırın:</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all;"><a href="<?= e($confirmUrl) ?>" style="color:#8a6a2c;"><?= e($confirmUrl) ?></a></p>

<p style="margin:0;font-size:14px;color:#7a7088;">Bu kaydı siz yapmadıysanız bu e-postayı yok sayabilirsiniz; onay vermeden size bülten gönderilmez.</p>
