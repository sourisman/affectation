<?php

declare(strict_types=1);

/**
 * Page « Références ».
 *
 * @var list<array<string, mixed>> $testimonials
 */

$cases = [
    [
        'client' => 'Groupe Horizon', 'sector' => 'Distribution — 8 sites',
        'challenge' => 'Effectifs suivis sur tableurs indépendants par site, aucune vision consolidée.',
        'solution' => 'Déploiement AFFECTA, reprise de 12 ans d\'historique et formation de 24 encadrants.',
        'results' => ['-62 % de délai de traitement', '100 % des mouvements tracés', '3 semaines de déploiement'],
    ],
    [
        'client' => 'Transport Océan Indien', 'sector' => 'Logistique — 1 200 agents',
        'challenge' => 'Mutations inter-provinces fréquentes, impact direct sur la paie et les plannings.',
        'solution' => 'Workflow de validation à trois niveaux et interfaçage avec le logiciel de paie.',
        'results' => ['4 jours au lieu de 3 semaines', '0 écart de paie constaté', '2 provinces couvertes'],
    ],
    [
        'client' => 'Banque Centrale du Sud', 'sector' => 'Services financiers — 640 agents',
        'challenge' => 'Exigences d\'audit fortes et besoin de justifier chaque décision d\'affectation.',
        'solution' => 'Journal d\'audit complet, exports signés et politique de conservation paramétrable.',
        'results' => ['Audit annuel sans réserve', 'Exports en 2 clics', 'Conformité documentée'],
    ],
];
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Références</span>
        <h1 style="margin-top:1.1rem;font-size:clamp(2.2rem,5vw,3.6rem)">Des résultats vérifiables.</h1>
        <p class="text-muted" style="margin-top:1.4rem;font-size:1.08rem;max-width:64ch">
            Trois exemples représentatifs de nos interventions, avec les indicateurs constatés
            après six mois d'utilisation.
        </p>
    </div>
</section>

<section class="section">
    <div class="container grid" style="gap:1.2rem">
        <?php foreach ($cases as $index => $case): ?>
            <article class="card card--spotlight" data-reveal data-reveal-delay="<?= $index * 80 ?>">
                <div class="cluster" style="justify-content:space-between;align-items:flex-start">
                    <div>
                        <span class="plan__name"><?= e($case['client']) ?></span>
                        <h3 style="font-size:1.35rem;margin-top:.5rem"><?= e($case['sector']) ?></h3>
                    </div>
                    <?= icon('building', 26, 'text-accent') ?>
                </div>

                <div class="grid grid-2" style="margin-top:1.4rem;gap:1.4rem">
                    <div>
                        <div class="mono text-muted" style="font-size:.64rem">Enjeu</div>
                        <p class="text-muted" style="margin-top:.4rem;font-size:.93rem"><?= e($case['challenge']) ?></p>
                    </div>
                    <div>
                        <div class="mono text-muted" style="font-size:.64rem">Intervention</div>
                        <p class="text-muted" style="margin-top:.4rem;font-size:.93rem"><?= e($case['solution']) ?></p>
                    </div>
                </div>

                <div class="service__tags" style="margin-top:1.4rem">
                    <?php foreach ($case['results'] as $result): ?>
                        <span class="badge badge--accent"><?= icon('trend-up', 13) ?> <?= e($result) ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require BASE_PATH . '/views/components/sections/testimonials.php'; ?>
