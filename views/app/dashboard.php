<?php

declare(strict_types=1);

/**
 * Tableau de bord opérationnel.
 *
 * @var array<string, mixed> $dashboard
 * @var array<string, int> $activity
 * @var list<array<string, mixed>> $lieux
 * @var int $maxEffectif
 * @var array<string, int> $totaux
 * @var list<array<string, mixed>> $recentes
 */

$volume = $dashboard['volume'];
$maxVolume = max(array_values($volume) ?: [1]);
$statuts = [
    ['label' => 'Appliquées', 'value' => $dashboard['appliquees'], 'color' => 'var(--accent-500)'],
    ['label' => 'Planifiées', 'value' => $dashboard['planifiees'], 'color' => 'var(--cool-500)'],
    ['label' => 'Annulées', 'value' => $dashboard['annulees'], 'color' => 'var(--warn-400)'],
];
$totalStatuts = max(array_sum(array_column($statuts, 'value')), 1);
?>
<div class="kpis">
    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Agents actifs</span>
            <span class="kpi__icon"><?= icon('users', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['employes'] ?>">0</div>
        <div class="kpi__foot">
            <span class="trend trend--up"><?= icon('trend-up', 13) ?> +4,2 %</span>
            <span>vs mois dernier · <?= (int) $totaux['postes'] ?> postes distincts</span>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Lieux d'affectation</span>
            <span class="kpi__icon"><?= icon('building', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['lieux'] ?>">0</div>
        <div class="kpi__foot">
            <span><?= (int) $totaux['inactifs'] ?> agent(s) inactif(s)</span>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Affectations</span>
            <span class="kpi__icon"><?= icon('route', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $dashboard['total'] ?>">0</div>
        <div class="kpi__bar" aria-hidden="true">
            <?php foreach ($statuts as $statut): ?>
                <i style="width:<?= round($statut['value'] / $totalStatuts * 100, 1) ?>%;background:<?= $statut['color'] ?>"></i>
            <?php endforeach; ?>
        </div>
        <div class="kpi__foot">
            <span><?= (int) $dashboard['planifiees'] ?> en attente · <?= (int) $dashboard['mois_courant'] ?> ce mois</span>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Agents non affectés</span>
            <span class="kpi__icon"><?= icon('alert', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['non_affectes'] ?>">0</div>
        <div class="kpi__foot">
            <a class="text-accent" href="/app/rapports/non-affectes">Traiter la liste <?= icon('arrow-right', 13) ?></a>
        </div>
    </article>
</div>

<div class="split">
    <section class="panel" data-reveal>
        <header class="panel__head">
            <div>
                <div class="panel__title">Volume des affectations</div>
                <div class="text-muted" style="font-size:.8rem">12 derniers mois, toutes statuts confondus</div>
            </div>
            <span class="badge"><?= icon('chart', 13) ?> <?= array_sum($volume) ?> mouvements</span>
        </header>

        <div class="panel__body">
            <div class="chart" data-chart>
                <?php foreach ($volume as $month => $count): ?>
                    <div class="chart__col">
                        <span class="chart__value"><?= (int) $count ?></span>
                        <div class="chart__bar <?= ((int) substr($month, 5, 2)) % 2 === 0 ? 'chart__bar--alt' : '' ?>"
                             data-value="<?= (int) $count ?>"></div>
                        <span class="chart__label"><?= e(date('M', strtotime($month . '-01'))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chart-legend">
                <span><i></i> Mois impair</span>
                <span><i class="alt"></i> Mois pair</span>
                <span class="text-muted">Maximum : <?= (int) $maxVolume ?> mouvements</span>
            </div>
        </div>
    </section>

    <section class="panel" data-reveal>
        <header class="panel__head">
            <div class="panel__title">Répartition par statut</div>
        </header>

        <div class="panel__body">
            <div class="donut"
                 style="--donut-a:<?= round($dashboard['appliquees'] / $totalStatuts * 100, 1) ?>%;
                        --donut-b:<?= round(($dashboard['appliquees'] + $dashboard['planifiees']) / $totalStatuts * 100, 1) ?>%"
                 role="img"
                 aria-label="Répartition des affectations par statut"></div>

            <div class="donut-legend">
                <?php foreach ($statuts as $statut): ?>
                    <div>
                        <i style="background:<?= $statut['color'] ?>"></i>
                        <?= e($statut['label']) ?>
                        <b><?= (int) $statut['value'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<div class="split">
    <section class="panel" data-reveal>
        <header class="panel__head">
            <div class="panel__title">Effectifs par site</div>
            <a class="btn btn--ghost btn--sm" href="/app/lieux">Gérer les lieux <?= icon('arrow-right', 14) ?></a>
        </header>

        <div class="panel__body">
            <div class="coverage">
                <?php foreach ($lieux as $lieu): ?>
                    <div class="coverage__row">
                        <span class="coverage__name">
                            <?= e($lieu['design']) ?>
                            <span class="text-muted" style="font-size:.78rem">· <?= e($lieu['province']) ?></span>
                        </span>
                        <span class="coverage__value">
                            <?= (int) $lieu['effectif'] ?> agents
                            <?php if ((int) $lieu['entrantes'] > 0): ?>
                                <span class="badge badge--accent" style="margin-left:.4rem">+<?= (int) $lieu['entrantes'] ?> à venir</span>
                            <?php endif; ?>
                        </span>
                        <span class="coverage__bar">
                            <i data-reveal="scale" style="width:<?= round((int) $lieu['effectif'] / max($maxEffectif, 1) * 100) ?>%"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel" data-reveal>
        <header class="panel__head">
            <div class="panel__title">Activité récente</div>
            <a class="btn btn--ghost btn--sm" href="/app/historique">Tout voir</a>
        </header>

        <div class="panel__body">
            <div class="timeline-list">
                <?php foreach (array_slice($recentes, 0, 6) as $index => $affectation): ?>
                    <div class="timeline-list__item <?= $index === 0 ? 'timeline-list__item--accent' : '' ?>">
                        <span class="timeline-list__icon"><?= icon('route', 15) ?></span>
                        <div>
                            <div class="timeline-list__text">
                                <strong><?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></strong>
                                — <?= e($affectation['ancien_design']) ?> → <?= e($affectation['nouveau_design']) ?>
                            </div>
                            <div class="timeline-list__meta">
                                <?= e($affectation['numAffect']) ?> · <?= e(format_date($affectation['dateAffect'])) ?>
                                · <?= e(\App\Models\Affectation::STATUTS[$affectation['statut']] ?? $affectation['statut']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<section class="panel" data-reveal>
    <header class="panel__head">
        <div>
            <div class="panel__title">Derniers mouvements enregistrés</div>
            <div class="text-muted" style="font-size:.8rem">Les huit affectations les plus récentes</div>
        </div>
        <a class="btn btn--outline btn--sm" href="/app/affectations">
            <?= icon('route', 14) ?>
            Toutes les affectations
        </a>
    </header>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Référence</th>
                    <th scope="col">Agent</th>
                    <th scope="col">Trajet</th>
                    <th scope="col">Date</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentes as $affectation): ?>
                    <tr>
                        <td class="mono"><?= e($affectation['numAffect']) ?></td>
                        <td class="cell-strong"><?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></td>
                        <td><?= e($affectation['ancien_design']) ?> → <?= e($affectation['nouveau_design']) ?></td>
                        <td><?= e(format_date($affectation['dateAffect'])) ?></td>
                        <td>
                            <?php $statut = $affectation['statut']; require BASE_PATH . '/views/components/status-badge.php'; ?>
                        </td>
                        <td class="cell-actions">
                            <span class="row-actions">
                                <a class="icon-btn" href="/app/affectations/<?= (int) $affectation['id'] ?>"
                                   aria-label="Voir le détail de <?= e($affectation['numAffect']) ?>">
                                    <?= icon('eye', 15) ?>
                                </a>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
