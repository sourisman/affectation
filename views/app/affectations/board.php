<?php

declare(strict_types=1);

/**
 * Vue kanban du pipeline de mouvements.
 *
 * @var array<string, list<array<string, mixed>>> $grouped
 */

$columns = [
    'planifie' => ['label' => 'Planifiées', 'icon' => 'calendar', 'hint' => 'En attente d\'application'],
    'applique' => ['label' => 'Appliquées', 'icon' => 'check-circle', 'hint' => 'Agent transféré'],
    'annule'   => ['label' => 'Annulées', 'icon' => 'x-circle', 'hint' => 'Dossier clos'],
];
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/affectations"><?= icon('grid', 15) ?> Vue tableau</a>
    <a class="btn btn--primary btn--sm" href="/app/affectations#modal-affectation"><?= icon('plus', 15) ?> Nouvelle affectation</a>
</div>

<div class="kanban">
    <?php foreach ($columns as $key => $column): ?>
        <section class="kanban__col" aria-label="<?= e($column['label']) ?>">
            <header class="kanban__head">
                <span><?= icon($column['icon'], 14) ?> <?= e($column['label']) ?></span>
                <span class="badge"><?= count($grouped[$key] ?? []) ?></span>
            </header>

            <p class="text-muted" style="font-size:.76rem"><?= e($column['hint']) ?></p>

            <?php if (empty($grouped[$key])): ?>
                <p class="text-muted" style="font-size:.84rem;padding:.6rem 0">Aucun dossier dans cette colonne.</p>
            <?php endif; ?>

            <?php foreach ($grouped[$key] ?? [] as $affectation): ?>
                <article class="kanban__card">
                    <div class="kanban__route">
                        <?= icon('user', 14) ?>
                        <strong><?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></strong>
                    </div>

                    <div class="kanban__route">
                        <?= icon('route', 14) ?>
                        <?= e($affectation['ancien_design']) ?> → <?= e($affectation['nouveau_design']) ?>
                    </div>

                    <div class="kanban__meta">
                        <span class="mono"><?= e($affectation['numAffect']) ?></span>
                        <span><?= e(format_date($affectation['dateAffect'])) ?></span>
                    </div>

                    <div class="row-actions" style="justify-content:flex-start">
                        <a class="icon-btn" href="/app/affectations/<?= (int) $affectation['id'] ?>" aria-label="Voir le détail">
                            <?= icon('eye', 15) ?>
                        </a>

                        <?php if ($key === 'planifie'): ?>
                            <form action="/app/affectations/<?= (int) $affectation['id'] ?>/appliquer" method="post" data-ajax>
                                <?= csrf_field() ?>
                                <button class="icon-btn icon-btn--accent" type="submit" aria-label="Appliquer">
                                    <?= icon('check', 15) ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
