<?php

declare(strict_types=1);

/**
 * Méthode : timeline verticale avec ligne de progression animée.
 *
 * @var list<array<string,string>> $process
 */
?>
<section class="section" id="process">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Méthode</span>
            <h2>Six étapes, aucune zone d'ombre.</h2>
            <p>
                Vous savez à tout moment où en est le projet, ce qui est livré et ce qui reste à valider.
            </p>
        </div>

        <div class="timeline" data-timeline>
            <span class="timeline__progress" aria-hidden="true"></span>

            <?php foreach ($process as $step): ?>
                <article class="step">
                    <span class="step__num"><?= e($step['num']) ?></span>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                    <div class="step__meta">
                        <span class="badge"><?= icon('calendar', 13) ?> <?= e($step['duration']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
