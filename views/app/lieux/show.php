<?php

declare(strict_types=1);

/**
 * Fiche lieu : effectifs rattachés et mouvements.
 *
 * @var array<string, mixed> $lieu
 * @var list<array<string, mixed>> $equipe
 * @var list<array<string, mixed>> $arrivees
 * @var list<array<string, mixed>> $departs
 */

$effectif = count($equipe);
$capacite = $lieu['capacite'] !== null ? (int) $lieu['capacite'] : null;
$taux = $capacite !== null && $capacite > 0 ? round($effectif / $capacite * 100) : null;
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/lieux"><?= icon('arrow-left', 15) ?> Tous les lieux</a>
</div>

<div class="kpis">
    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Effectif rattaché</span>
            <span class="kpi__icon"><?= icon('users', 16) ?></span>
        </div>
        <div class="kpi__value"><?= $effectif ?></div>
        <div class="kpi__foot"><span>agents actifs sur ce site</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Capacité d'accueil</span>
            <span class="kpi__icon"><?= icon('building', 16) ?></span>
        </div>
        <div class="kpi__value"><?= $capacite !== null ? $capacite : '—' ?></div>
        <div class="kpi__foot">
            <?php if ($taux !== null): ?>
                <span class="badge <?= $taux > 100 ? 'badge--danger' : ($taux > 85 ? 'badge--warn' : 'badge--ok') ?>">
                    <?= $taux ?> % occupé
                </span>
            <?php else: ?>
                <span>Capacité non définie</span>
            <?php endif; ?>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Arrivées enregistrées</span>
            <span class="kpi__icon"><?= icon('trend-up', 16) ?></span>
        </div>
        <div class="kpi__value"><?= count($arrivees) ?></div>
        <div class="kpi__foot"><span>dont <?= count(array_filter($arrivees, static fn (array $a): bool => $a['statut'] === 'planifie')) ?> planifiées</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Départs enregistrés</span>
            <span class="kpi__icon"><?= icon('trend-down', 16) ?></span>
        </div>
        <div class="kpi__value"><?= count($departs) ?></div>
        <div class="kpi__foot"><span>mouvements sortants</span></div>
    </article>
</div>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Équipe rattachée</div>
                <div class="text-muted" style="font-size:.8rem"><?= e($lieu['design']) ?> · <?= e($lieu['province']) ?></div>
            </div>
            <span class="badge mono"><?= e($lieu['idlieu']) ?></span>
        </header>

        <?php if ($equipe === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon"><?= icon('users', 20) ?></span>
                <h3>Aucun agent rattaché</h3>
                <p>Ce site n'a pas encore d'effectif affecté.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Agent</th>
                            <th scope="col">Poste</th>
                            <th scope="col">Ancienneté</th>
                            <th scope="col" class="cell-actions">Fiche</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipe as $agent): ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <span class="person__avatar" aria-hidden="true"><?= e(initials($agent['prenom'] . ' ' . $agent['nom'])) ?></span>
                                        <div>
                                            <div class="person__name"><?= e($agent['prenom'] . ' ' . $agent['nom']) ?></div>
                                            <div class="person__meta"><?= e($agent['numEmp']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($agent['poste']) ?></td>
                                <td><?= e(format_date((string) $agent['date_embauche'], 'M Y')) ?></td>
                                <td class="cell-actions">
                                    <a class="icon-btn" href="/app/employes/<?= e($agent['numEmp']) ?>" aria-label="Voir la fiche">
                                        <?= icon('eye', 15) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Mouvements récents</div>
        </header>

        <div class="panel__body">
            <div class="timeline-list">
                <?php foreach (array_merge(
                    array_map(static fn (array $a): array => $a + ['sens' => 'arrivee'], $arrivees),
                    array_map(static fn (array $a): array => $a + ['sens' => 'depart'], $departs),
                ) as $mouvement): ?>
                    <div class="timeline-list__item <?= $mouvement['sens'] === 'arrivee' ? 'timeline-list__item--accent' : '' ?>">
                        <span class="timeline-list__icon">
                            <?= icon($mouvement['sens'] === 'arrivee' ? 'trend-up' : 'trend-down', 15) ?>
                        </span>
                        <div>
                            <div class="timeline-list__text">
                                <strong><?= e($mouvement['prenom'] . ' ' . $mouvement['nom']) ?></strong>
                                <?= $mouvement['sens'] === 'arrivee' ? 'arrive de ' : 'part vers ' ?>
                                <?= e($mouvement['sens'] === 'arrivee' ? $mouvement['ancien_design'] : $mouvement['nouveau_design']) ?>
                            </div>
                            <div class="timeline-list__meta">
                                <?= e($mouvement['numAffect']) ?> · <?= e(format_date((string) $mouvement['dateAffect'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($arrivees === [] && $departs === []): ?>
                    <p class="text-muted">Aucun mouvement enregistré pour ce site.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
