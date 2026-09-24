<?php

declare(strict_types=1);

/**
 * Fonctionnalités — composition asymétrique volontairement irrégulière.
 *
 * @var list<array<string, mixed>> $features
 */

$spans = [
    'hero'  => 'feature--hero-card',
    'wide'  => 'feature--wide',
    'half'  => 'feature--half',
    'third' => 'feature--third',
];
?>
<section class="section" id="fonctionnalites">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Fonctionnalités</span>
            <h2>Six raisons de basculer votre pilotage sur AFFECTA.</h2>
            <p>
                Chaque module répond à un irritant identifié sur le terrain — pas à une case à cocher
                dans un comparatif commercial.
            </p>
        </div>

        <div class="features">
            <?php foreach ($features as $index => $feature): ?>
                <article class="feature card--spotlight <?= e($spans[$feature['size']] ?? 'feature--third') ?>"
                         data-reveal
                         data-reveal-delay="<?= (int) ($index % 3) * 90 ?>">
                    <span class="feature__num"><?= e($feature['num']) ?></span>

                    <span class="feature__icon" aria-hidden="true"><?= icon($feature['icon'], 20) ?></span>

                    <h3><?= e($feature['title']) ?></h3>
                    <p><?= e($feature['text']) ?></p>

                    <?php if (!empty($feature['rows'])): ?>
                        <div class="feature__visual mini-flow" aria-hidden="true">
                            <?php foreach ($feature['rows'] as $row): ?>
                                <div class="mini-flow__row">
                                    <span><?= e($row[0]) ?></span>
                                    <b><?= e($row[1]) ?> · <?= e($row[2]) ?></b>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
