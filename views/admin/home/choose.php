<?php /** @var array<string, array<string, mixed>> $types */ ?>
<p class="back-link"><a href="<?= e(url('/yonetim/ana-sayfa')) ?>">← Ana Sayfa</a></p>
<div class="page-title">
    <h1>Yeni blok</h1>
    <p class="muted">Eklemek istediğiniz bölümün türünü seçin. Yeni blok ana sayfanın en altına eklenir; sonra yukarı taşıyabilirsiniz.</p>
</div>

<div class="panel">
    <ul class="block-types">
        <?php foreach ($types as $key => $type): ?>
        <li>
            <a href="<?= e(url('/yonetim/ana-sayfa/yeni') . '?tur=' . rawurlencode($key)) ?>">
                <strong><?= e($type['label']) ?></strong>
                <span class="muted"><?= e($type['desc']) ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
