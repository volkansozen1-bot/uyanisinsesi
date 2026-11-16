<?php
/**
 * SSS akordeonu. <details> kullanir; JavaScript gerektirmez.
 * @var array<int, array<string, mixed>> $faqs
 */
if (!$faqs) {
    return;
}
?>
<section class="section section-alt">
    <div class="container narrow">
        <div class="section-head">
            <span class="eyebrow">Merak edilenler</span>
            <h2>Sık sorulan sorular</h2>
        </div>
        <div class="faq">
            <?php foreach ($faqs as $faq): ?>
            <details>
                <summary><?= e($faq['question']) ?></summary>
                <div class="faq-answer"><p><?= nl2br(e($faq['answer'])) ?></p></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
