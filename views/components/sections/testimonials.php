<?php

declare(strict_types=1);

/**
 * Témoignages : carrousel accessible (clavier, glisser-déposer, défilement auto).
 *
 * @var list<array<string, mixed>> $testimonials
 */
?>
<section class="section testimonials" id="temoignages">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Témoignages</span>
            <h2>Ce que les équipes en disent.</h2>
            <p>Des retours de directions des ressources humaines, de directions générales et d'équipes projet.</p>
        </div>

        <div class="carousel" data-carousel tabindex="0" role="group" aria-roledescription="carrousel" aria-label="Témoignages clients">
            <div class="carousel__track">
                <?php foreach ($testimonials as $testimonial): ?>
                    <figure class="quote">
                        <div class="quote__stars" aria-label="Note : <?= (int) $testimonial['rating'] ?> sur 5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span style="opacity:<?= $i <= (int) $testimonial['rating'] ? '1' : '.25' ?>" aria-hidden="true">
                                    <?= icon('star', 15) ?>
                                </span>
                            <?php endfor; ?>
                        </div>

                        <blockquote>
                            <p><?= e($testimonial['quote']) ?></p>
                        </blockquote>

                        <figcaption class="quote__author">
                            <span class="quote__avatar" aria-hidden="true"><?= e(initials($testimonial['name'])) ?></span>
                            <div>
                                <strong><?= e($testimonial['name']) ?></strong>
                                <span><?= e($testimonial['role']) ?> — <?= e($testimonial['company']) ?></span>
                            </div>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="carousel__controls">
            <div class="carousel__dots" role="tablist" aria-label="Sélection du témoignage">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <button type="button"
                            role="tab"
                            aria-label="Témoignage <?= $index + 1 ?>"
                            aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel__nav">
                <button class="carousel__btn" type="button" data-carousel-prev aria-label="Témoignages précédents">
                    <?= icon('chevron-left', 18) ?>
                </button>
                <button class="carousel__btn" type="button" data-carousel-next aria-label="Témoignages suivants">
                    <?= icon('chevron-right', 18) ?>
                </button>
            </div>
        </div>
    </div>
</section>
