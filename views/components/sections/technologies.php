<?php

declare(strict_types=1);

/**
 * Socle technique.
 *
 * @var list<array<string,string>> $technologies
 */
?>
<section class="section" id="technologies">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Technologies</span>
            <h2>Un socle éprouvé, choisi pour durer.</h2>
            <p>
                PHP 8.3 côté serveur, JavaScript moderne côté navigateur, MySQL pour les données :
                aucune technologie exotique qui enfermerait votre organisation.
            </p>
        </div>

        <div class="tech-grid">
            <?php foreach ($technologies as $index => $tech): ?>
                <div class="tech" data-reveal data-reveal-delay="<?= (int) ($index % 4) * 70 ?>">
                    <span class="tech__glyph" aria-hidden="true"><?= e($tech['glyph']) ?></span>
                    <div>
                        <strong><?= e($tech['name']) ?></strong>
                        <small style="display:block"><?= e($tech['note']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
