<?php /** @var array<string, mixed> $page */ ?>
<header class="page-head page-cover">
    <?php if (!empty($page['hero_image'])): ?>
    <img class="page-cover-image" src="<?= e(url($page['hero_image'])) ?>" alt="">
    <?php endif; ?>
    <div class="container narrow">
        <h1><?= e($page['title']) ?></h1>
    </div>
</header>
<article class="section page-<?= e($page['slug']) ?>">
    <div class="container narrow prose">
        <?php /* Panelden girilen zengin metin; kaydedilirken HtmlSanitizer ile temizlenir, burada oldugu gibi basilir */ ?>
        <?= $page['body'] ?>
    </div>
</article>
<?php if ($page['slug'] === 'sertifikalar' && ($certs = certificates())): ?>
<section class="section cert-gallery-wrap">
    <div class="container">
        <div class="cert-gallery">
            <?php foreach ($certs as $i => $c): ?>
            <a class="cert-thumb" href="#sertifika-<?= $i ?>">
                <img src="<?= e(url($c['path'])) ?>" alt="<?= e($c['title']) ?>" loading="lazy">
                <?php if ($c['title'] !== ''): ?><span><?= e($c['title']) ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php foreach ($certs as $i => $c): ?>
<a class="cert-lightbox" id="sertifika-<?= $i ?>" href="#_" aria-label="Kapat"><img src="<?= e(url($c['path'])) ?>" alt="<?= e($c['title']) ?>" loading="lazy"></a>
<?php endforeach; ?>
<?php endif; ?>
