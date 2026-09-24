<?php

declare(strict_types=1);

/**
 * Services : liste interactive, l'aperçu visuel suit le survol.
 *
 * @var list<array<string, mixed>> $services
 */
?>
<section class="section" id="services">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Services</span>
            <h2>Un accompagnement complet, du cadrage à la maintenance.</h2>
            <p>
                Nous intervenons sur l'ensemble du cycle de vie : diagnostic, intégration,
                développements spécifiques, formation et maintien en condition opérationnelle.
            </p>
        </div>

        <div class="services">
            <div class="services__list" data-services>
                <?php foreach ($services as $service): ?>
                    <article class="service"
                             data-service="<?= e($service['key']) ?>"
                             data-title="<?= e($service['title']) ?>"
                             data-subtitle="<?= e($service['subtitle']) ?>"
                             tabindex="0">
                        <span class="service__num"><?= e($service['num']) ?></span>

                        <div>
                            <h3><?= icon($service['icon'], 19) ?> <?= e($service['title']) ?></h3>
                            <p><?= e($service['text']) ?></p>
                            <div class="service__tags">
                                <?php foreach ($service['tags'] as $tag): ?>
                                    <span class="badge"><?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <span class="service__arrow" aria-hidden="true"><?= icon('arrow-right', 18) ?></span>
                    </article>
                <?php endforeach; ?>

                <a class="btn btn--outline" href="#contact" style="margin-top:1.6rem">
                    Demander un accompagnement
                    <?= icon('arrow-right', 16) ?>
                </a>
            </div>

            <aside class="services__preview" aria-hidden="true">
                <div class="services__preview-media">
                    <?php foreach ($services as $service): ?>
                        <img src="<?= e(asset('images/' . $service['image'])) ?>"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             width="800" height="600"
                             data-preview="<?= e($service['key']) ?>">
                    <?php endforeach; ?>
                </div>
                <div class="services__preview-caption">
                    <strong data-preview-caption></strong>
                    <span data-preview-subcaption></span>
                </div>
            </aside>
        </div>
    </div>
</section>
