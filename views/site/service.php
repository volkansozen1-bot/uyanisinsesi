<?php
/**
 * @var array<string, mixed> $service
 * @var array<int, array<string, mixed>> $packages
 * @var array<int, array<string, mixed>> $faqs
 * @var array<int, array<string, mixed>> $testimonials
 */
use App\Core\View;

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'Hizmetler',
    'title'   => $service['title'],
    'lead'    => $service['summary'],
]);
?>
<article class="section">
    <div class="container narrow prose">
        <?php if ($service['image']): ?>
        <img class="prose-image" src="<?= e(url($service['image'])) ?>" alt="">
        <?php endif; ?>
        <?php /* body panelden girilen HTML'dir; kaydedilirken temizlenir */ ?>
        <?= $service['body'] ?>
    </div>
</article>

<?php if ($packages): ?>
<section class="section section-alt" id="paketler">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Paketler</span>
            <h2>Sana uygun olanı seç</h2>
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
                <a class="btn" href="<?= e(url($package['button_url'] ?: '/iletisim')) ?>"><?= e($package['button_text'] ?: 'Bilgi Al') ?></a>
            </div>
            <?php endforeach; ?>
        </div>
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
<?= View::renderFile('partials/cta') ?>
