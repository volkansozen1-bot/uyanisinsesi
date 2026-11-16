<?php
/**
 * Hero. Mobilde sade ve ortali; masaustunde metin solda, sagda hareketli kemer kompozisyonu.
 * extra_json: eyebrow, button2_text, button2_url, keywords (dizi), note_title, note_text
 * image doluysa kemerin icinde fotograf olarak gosterilir.
 * @var array<string, mixed> $block
 */
$extra    = json_decode((string) ($block['extra_json'] ?? ''), true) ?: [];
$keywords = array_filter((array) ($extra['keywords'] ?? []), 'is_string');
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-text">
            <?php if (!empty($extra['eyebrow'])): ?><span class="eyebrow"><?= e($extra['eyebrow']) ?></span><?php endif; ?>
            <h1><?= e($block['title']) ?></h1>
            <div class="ornament" aria-hidden="true"><span></span></div>
            <?php if ($block['subtitle']): ?><p class="lead"><?= e($block['subtitle']) ?></p><?php endif; ?>
            <div class="hero-actions">
                <?php if ($block['button_text'] && $block['button_url']): ?>
                <a class="btn" href="<?= e(url($block['button_url'])) ?>"><?= e($block['button_text']) ?></a>
                <?php endif; ?>
                <?php if (!empty($extra['button2_text']) && !empty($extra['button2_url'])): ?>
                <a class="btn btn-ghost" href="<?= e(url($extra['button2_url'])) ?>"><?= e($extra['button2_text']) ?></a>
                <?php endif; ?>
            </div>
        </div>

        <?php /* Yalnizca genis ekranda gorunur; tamamen susleme oldugu icin ekran okuyuculardan gizli */ ?>
        <div class="hero-art" aria-hidden="true">
            <svg class="hero-rings" viewBox="0 0 600 600">
                <defs>
                    <radialGradient id="heroGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0" stop-color="#c39a4b" stop-opacity="0.55"/>
                        <stop offset="0.55" stop-color="#c39a4b" stop-opacity="0.12"/>
                        <stop offset="1" stop-color="#c39a4b" stop-opacity="0"/>
                    </radialGradient>
                </defs>
                <circle cx="300" cy="300" r="300" fill="url(#heroGlow)"/>
                <g class="ring ring-slow">
                    <circle cx="300" cy="300" r="286" fill="none" stroke="#c39a4b" stroke-opacity="0.35" stroke-dasharray="2 14"/>
                    <circle cx="300" cy="14" r="5" fill="#e6d3a7"/>
                </g>
                <g class="ring ring-reverse">
                    <circle cx="300" cy="300" r="246" fill="none" stroke="#c39a4b" stroke-opacity="0.5"/>
                    <circle cx="546" cy="300" r="4" fill="#c39a4b"/>
                    <circle cx="54" cy="300" r="2.500" fill="#e6d3a7"/>
                </g>
                <g class="stars" fill="#fbf7f0">
                    <circle cx="70" cy="110" r="2"/><circle cx="520" cy="90" r="1.600"/><circle cx="560" cy="430" r="2.200"/>
                    <circle cx="40" cy="470" r="1.600"/><circle cx="470" cy="545" r="1.800"/><circle cx="150" cy="560" r="1.400"/>
                </g>
            </svg>

            <div class="hero-arch">
                <?php if ($block['image']): ?>
                <img src="<?= e(url($block['image'])) ?>" alt="">
                <?php else: ?>
                <svg viewBox="0 0 340 460" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <linearGradient id="archSky" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#3a2a52"/>
                            <stop offset="0.55" stop-color="#5b3a6e"/>
                            <stop offset="1" stop-color="#b9824f"/>
                        </linearGradient>
                        <radialGradient id="archSun" cx="50%" cy="50%" r="50%">
                            <stop offset="0" stop-color="#fbf7f0"/>
                            <stop offset="0.45" stop-color="#e6d3a7"/>
                            <stop offset="1" stop-color="#c39a4b" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                    <rect width="340" height="460" fill="url(#archSky)"/>
                    <circle cx="170" cy="250" r="150" fill="url(#archSun)" opacity="0.9"/>
                    <circle cx="170" cy="250" r="46" fill="#fbf7f0" opacity="0.92"/>
                    <g class="waves" fill="none" stroke="#fbf7f0" stroke-linecap="round">
                        <circle cx="170" cy="250" r="70" stroke-opacity="0.55"/>
                        <circle cx="170" cy="250" r="100" stroke-opacity="0.38"/>
                        <circle cx="170" cy="250" r="132" stroke-opacity="0.24"/>
                        <circle cx="170" cy="250" r="166" stroke-opacity="0.14"/>
                    </g>
                    <path d="M0 360 C60 330 110 350 170 338 C235 325 280 352 340 330 L340 460 L0 460 Z" fill="#2e2547" opacity="0.75"/>
                    <path d="M0 400 C70 372 130 398 200 384 C255 373 300 392 340 380 L340 460 L0 460 Z" fill="#1d1730"/>
                </svg>
                <?php endif; ?>
            </div>

            <?php if (!empty($extra['note_title'])): ?>
            <div class="hero-note">
                <strong><?= e($extra['note_title']) ?></strong>
                <?php if (!empty($extra['note_text'])): ?><span><?= e($extra['note_text']) ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($keywords): ?>
    <div class="hero-band" aria-hidden="true">
        <div class="hero-band-track">
            <?php for ($i = 0; $i < 2; $i++): foreach ($keywords as $word): ?>
            <span><?= e($word) ?></span>
            <?php endforeach; endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
