<?php /** Tam ekran giris: fotograf (istege bagli) + "oze donus" illuzyonu, buyuk soz, kaynak, buton */ ?>
<section class="hero-full<?= $block['image'] ? ' has-photo' : '' ?>" data-illusion>
    <?php if ($block['image']): ?>
    <img class="hero-full-image" src="<?= e(url($block['image'])) ?>" alt="" fetchpriority="high">
    <?php endif; ?>
    <canvas class="hero-full-particles" aria-hidden="true"></canvas>
    <div class="hero-full-illusion" aria-hidden="true">
        <svg viewBox="-200 -200 400 400">
            <defs>
                <radialGradient id="hfCore">
                    <stop offset="0" stop-color="#fff6dc"/>
                    <stop offset=".35" stop-color="#e6d3a7" stop-opacity=".85"/>
                    <stop offset="1" stop-color="#c39a4b" stop-opacity="0"/>
                </radialGradient>
            </defs>
            <g class="hf-ring hf-r1"><circle r="190" stroke-dasharray="1 7"/></g>
            <g class="hf-ring hf-r2"><circle r="160" stroke-dasharray="40 14 4 14"/></g>
            <g class="hf-ring hf-r3"><circle r="130" stroke-dasharray="2 5"/><ellipse rx="130" ry="46"/></g>
            <g class="hf-ring hf-r4"><circle r="100" stroke-dasharray="60 30"/><ellipse rx="46" ry="100"/></g>
            <g class="hf-ring hf-r5"><circle r="70" stroke-dasharray="1 4"/></g>
            <g class="hf-ring hf-r6"><circle r="42"/></g>
            <circle class="hf-core" r="60" fill="url(#hfCore)"/>
        </svg>
    </div>
    <div class="container hero-full-content">
        <h1 class="hero-full-quote">“<?= e(trim((string) $block['subtitle'])) ?>”</h1>
        <?php if ($block['title']): ?><p class="hero-full-author">— <?= e($block['title']) ?></p><?php endif; ?>
        <?php if ($block['button_text'] && $block['button_url']): ?>
        <a class="hero-full-btn" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a>
        <?php endif; ?>
    </div>
</section>
