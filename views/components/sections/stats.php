<?php

declare(strict_types=1);

/**
 * Statistiques animées (compteurs déclenchés à l'apparition).
 *
 * @var list<array<string, mixed>> $stats
 */
?>
<section class="section section--tight" id="chiffres">
    <div class="container">
        <div class="section-head section-head--center">
            <span class="eyebrow eyebrow--boxed">En chiffres</span>
            <h2>Des résultats mesurés, pas des promesses.</h2>
        </div>

        <div class="stats">
            <?php foreach ($stats as $stat): ?>
                <div class="stat" data-reveal="scale">
                    <div class="stat__value">
                        <span data-counter="<?= e((string) $stat['value']) ?>"
                              data-counter-decimals="<?= (int) $stat['decimals'] ?>"
                              data-counter-duration="1800">0</span><span class="suffix"><?= e($stat['suffix']) ?></span>
                    </div>
                    <div class="stat__label"><?= e($stat['label']) ?></div>
                    <div class="stat__meta"><?= e($stat['meta']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
