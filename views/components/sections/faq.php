<?php

declare(strict_types=1);

/**
 * FAQ en accordéon — une seule réponse ouverte à la fois.
 *
 * @var list<array{question:string,answer:string}> $faq
 */
?>
<section class="section" id="faq">
    <div class="container">
        <div class="section-head section-head--center">
            <span class="eyebrow eyebrow--boxed">Questions fréquentes</span>
            <h2>Tout ce que vous voulez savoir avant de démarrer.</h2>
        </div>

        <div class="faq" data-accordion>
            <?php foreach ($faq as $index => $item): ?>
                <div class="faq__item <?= $index === 0 ? 'is-open' : '' ?>" data-accordion-item>
                    <h3>
                        <button class="faq__question" type="button" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                            <span><?= e($item['question']) ?></span>
                            <span class="faq__icon" aria-hidden="true"></span>
                        </button>
                    </h3>
                    <div class="faq__answer">
                        <div>
                            <p><?= e($item['answer']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
