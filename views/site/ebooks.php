<?php
use App\Core\View;

echo View::renderFile('partials/page_head', [
    'eyebrow' => 'E-Kitaplar',
    'title'   => 'Çok yakında',
    'lead'    => 'Kendi hızında okuyabileceğin e-kitaplar hazırlanıyor.',
]);

// Yer tutucu kapaklar: yalnizca susleme, gercek urun degil
$covers = [
    ['title' => 'Birinci kitap', 'motif' => 'moon'],
    ['title' => 'İkinci kitap', 'motif' => 'rings'],
    ['title' => 'Üçüncü kitap', 'motif' => 'leaf'],
];
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div class="ornament" aria-hidden="true"><span></span></div>
            <p class="lead">E-kitaplar hakkında kısa açıklama buraya gelecek: hangi konuları ele aldıkları ve kimler için hazırlandıkları bir iki cümleyle anlatılacak.</p>
        </div>

        <ul class="ebook-grid">
            <?php foreach ($covers as $i => $cover): ?>
            <li class="ebook">
                <div class="ebook-cover ebook-cover-<?= $i + 1 ?>">
                    <svg viewBox="0 0 200 290" aria-hidden="true">
                        <rect x="12" y="12" width="176" height="266" rx="4" fill="none" stroke="#c39a4b" stroke-opacity="0.6"/>
                        <?php if ($cover['motif'] === 'moon'): ?>
                        <mask id="ebookMoon">
                            <rect width="200" height="290" fill="#fff"/>
                            <circle cx="118" cy="108" r="40" fill="#000"/>
                        </mask>
                        <circle cx="100" cy="120" r="44" fill="#e6d3a7" opacity="0.9" mask="url(#ebookMoon)"/>
                        <?php elseif ($cover['motif'] === 'rings'): ?>
                        <g fill="none" stroke="#e6d3a7">
                            <circle cx="100" cy="120" r="18" stroke-opacity="0.9"/>
                            <circle cx="100" cy="120" r="34" stroke-opacity="0.6"/>
                            <circle cx="100" cy="120" r="50" stroke-opacity="0.35"/>
                        </g>
                        <circle cx="100" cy="120" r="5" fill="#c39a4b"/>
                        <?php else: ?>
                        <path d="M64 160c0-48 30-78 84-84-6 54-36 84-84 84z" fill="none" stroke="#e6d3a7" stroke-width="1.5"/>
                        <path d="M64 160l48-48" stroke="#e6d3a7" stroke-width="1.5"/>
                        <?php endif; ?>
                        <line x1="70" y1="206" x2="130" y2="206" stroke="#c39a4b" stroke-opacity="0.7"/>
                        <rect x="56" y="222" width="88" height="6" rx="3" fill="#fbf7f0" opacity="0.35"/>
                        <rect x="72" y="236" width="56" height="5" rx="2.500" fill="#fbf7f0" opacity="0.2"/>
                    </svg>
                    <span class="ebook-badge">Hazırlanıyor</span>
                </div>
                <h2 class="h3"><?= e($cover['title']) ?></h2>
                <p>Kitabın konusu buraya gelecek.</p>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="center ebook-notify">
            <p>Yayımlandığında ilk sen haberdar olmak ister misin?</p>
            <a class="btn" href="#bulten">Bültene Abone Ol</a>
        </div>
    </div>
</section>
