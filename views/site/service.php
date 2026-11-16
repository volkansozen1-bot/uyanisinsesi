<?php
/**
 * Hizmet detay sayfasi.
 * Akis: baslik (+ kitle butonlari) -> genel tanitim -> kitle sekmeleri -> ucretler -> randevu -> yorumlar -> SSS -> cagri.
 *
 * @var array<string, mixed> $service
 * @var array<int, array<string, mixed>> $sections
 * @var array<int, array<string, mixed>> $packages
 * @var array<int, array<string, mixed>> $faqs
 * @var array<int, array<string, mixed>> $testimonials
 */
use App\Core\View;

$whatsapp    = preg_replace('/\D+/', '', setting('whatsapp_number'));
$waMessage   = trim((string) ($service['whatsapp_message'] ?: 'Merhaba, ' . $service['title'] . ' için bilgi almak istiyorum.'));
$bookingUrl  = $whatsapp ? 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($waMessage) : url('/iletisim');
$hasBooking  = $service['booking_days'] || $service['booking_hours'] || $service['booking_place'];
$hasSections = count($sections) > 0;
?>
<header class="page-head">
    <div class="container narrow">
        <span class="eyebrow">Hizmetler</span>
        <h1><?= e($service['title']) ?></h1>
        <?php if ($service['summary']): ?><p class="lead"><?= e($service['summary']) ?></p><?php endif; ?>
        <?php if (count($sections) > 1): ?>
        <div class="audience-links">
            <?php foreach ($sections as $section): ?>
            <a class="btn btn-ghost" href="#<?= e($section['anchor']) ?>"><?= e($section['tab_label']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php elseif ($packages): ?>
        <p><a class="btn" href="#ucretler">Ücretleri Gör</a></p>
        <?php endif; ?>
    </div>
</header>

<?php if (trim(strip_tags((string) $service['body'])) !== '' || $service['image']): ?>
<article class="section">
    <div class="container narrow prose">
        <?php if ($service['image']): ?>
        <img class="prose-image" src="<?= e(url($service['image'])) ?>" alt="">
        <?php endif; ?>
        <?php /* body panelden girilecek HTML'dir. Panelden duzenleme eklenince kaydederken temizlenmeli (HTML temizleyici henuz yok); simdilik icerik yalnizca SQL dosyalarindan gelir. Burada oldugu gibi basilir. */ ?>
        <?= $service['body'] ?>
    </div>
</article>
<?php endif; ?>

<?php if ($hasSections): ?>
<section class="section section-alt audience" data-tabs>
    <div class="container">
        <?php if (count($sections) > 1): ?>
        <div class="tab-list" role="tablist" aria-label="Kime yönelik">
            <?php foreach ($sections as $i => $section): ?>
            <button type="button" role="tab" class="tab" id="tab-<?= e($section['anchor']) ?>"
                    aria-controls="<?= e($section['anchor']) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                    <?= $i === 0 ? '' : 'tabindex="-1"' ?>><?= e($section['tab_label']) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php foreach ($sections as $i => $section): ?>
        <?php $items = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $section['focus_items'])))); ?>
        <div class="tab-panel" id="<?= e($section['anchor']) ?>" role="tabpanel" aria-labelledby="tab-<?= e($section['anchor']) ?>" tabindex="-1">
            <div class="split audience-split">
                <div class="split-media">
                    <?php if ($section['image']): ?>
                    <img src="<?= e(url($section['image'])) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <svg viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                        <rect width="400" height="500" fill="#2e2547"/>
                        <circle cx="200" cy="230" r="170" fill="#5b3a6e" opacity="0.6"/>
                        <circle cx="200" cy="230" r="64" fill="none" stroke="#c39a4b" stroke-opacity="0.7"/>
                        <circle cx="200" cy="230" r="104" fill="none" stroke="#c39a4b" stroke-opacity="0.4"/>
                        <circle cx="200" cy="230" r="146" fill="none" stroke="#c39a4b" stroke-opacity="0.2"/>
                        <?php if ($i % 2 === 0): ?>
                        <path d="M200 176c18 30 46 44 46 80a46 46 0 0 1-92 0c0-36 28-50 46-80z" fill="#e6d3a7"/>
                        <?php else: ?>
                        <path d="M200 180l46 50-46 50-46-50z" fill="none" stroke="#e6d3a7" stroke-width="3"/>
                        <circle cx="200" cy="230" r="10" fill="#e6d3a7"/>
                        <?php endif; ?>
                    </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($section['eyebrow']): ?><span class="eyebrow"><?= e($section['eyebrow']) ?></span><?php endif; ?>
                    <h2><?= e($section['title']) ?></h2>
                    <?php /* section body panelden girilecek HTML'dir. Panelden duzenleme eklenince kaydederken temizlenmeli (HTML temizleyici henuz yok); simdilik icerik yalnizca SQL dosyalarindan gelir. Burada oldugu gibi basilir. */ ?>
                    <div class="prose"><?= $section['body'] ?></div>
                </div>
            </div>

            <?php if ($items): ?>
            <div class="focus">
                <h3><?= e($section['focus_title'] ?: 'Birlikte çalışabileceğimiz alanlar') ?></h3>
                <ul class="focus-list">
                    <?php foreach ($items as $item): ?>
                    <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($packages): ?>
            <p class="center"><a class="btn btn-outline" href="#ucretler">Seans Ücretleri</a></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if ($packages): ?>
<section class="section" id="ucretler">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Ücretler</span>
            <h2>Sana uygun olanı seç</h2>
            <?php if (count($sections) > 1): ?><p class="lead">Ücretler <?= e(mb_strtolower(implode(' ve ', array_column($sections, 'tab_label')))) ?> aynıdır.</p><?php endif; ?>
        </div>
        <div class="card-grid price-grid">
            <?php foreach ($packages as $package): ?>
            <div class="card price-card">
                <h3><?= e($package['name']) ?></h3>
                <?php if ($package['price'] !== null): ?>
                <div class="price"><?= e(tr_price($package['price'])) ?></div>
                <?php else: ?>
                <div class="price price-ask">Fiyat için iletişime geçin</div>
                <?php endif; ?>
                <?php if ($package['description']): ?><p><?= e($package['description']) ?></p><?php endif; ?>
                <?php
                $buttonUrl = (string) $package['button_url'];
                $external  = (bool) preg_match('#^https?://#i', $buttonUrl);
                ?>
                <a class="btn" href="<?= e($buttonUrl !== '' ? url($buttonUrl) : $bookingUrl) ?>"<?= $external || $buttonUrl === '' && $whatsapp ? ' target="_blank" rel="noopener"' : '' ?>><?= e($package['button_text'] ?: 'Bilgi Al') ?></a>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="center price-note">Ödemeler güvenli ödeme sayfası üzerinden alınır; kart bilgileriniz bu sitede saklanmaz.</p>
    </div>
</section>
<?php endif; ?>

<?php if ($hasBooking): ?>
<section class="section section-dark promo" id="randevu">
    <div class="container narrow">
        <span class="eyebrow">Randevu</span>
        <h2>Sana uygun bir zaman bulalım</h2>
        <dl class="booking">
            <?php if ($service['booking_days']): ?><div><dt>Günler</dt><dd><?= e($service['booking_days']) ?></dd></div><?php endif; ?>
            <?php if ($service['booking_hours']): ?><div><dt>Saatler</dt><dd><?= e($service['booking_hours']) ?></dd></div><?php endif; ?>
            <?php if ($service['booking_place']): ?><div><dt>Nerede</dt><dd><?= e($service['booking_place']) ?></dd></div><?php endif; ?>
        </dl>
        <p><a class="btn" href="<?= e($bookingUrl) ?>"<?= $whatsapp ? ' target="_blank" rel="noopener"' : '' ?>><?= $whatsapp ? 'WhatsApp ile Randevu Al' : 'Randevu İçin Yaz' ?></a></p>
    </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<?= View::renderFile('blocks/testimonials', [
    'block' => ['title' => 'Katılımcılar ne diyor?', 'subtitle' => 'Geri bildirimler'],
    'data'  => ['testimonials' => $testimonials],
]) ?>
<?php endif; ?>

<?= View::renderFile('partials/faq', ['faqs' => $faqs]) ?>
<?php if (!$hasBooking): ?><?= View::renderFile('partials/cta') ?><?php endif; ?>
