<?php

declare(strict_types=1);

/**
 * Fiche agent : identité, rattachement et parcours.
 *
 * @var array<string, mixed> $employe
 * @var list<array<string, mixed>> $historique
 */

$encours = $historique[0] ?? null;
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/employes"><?= icon('arrow-left', 15) ?> Tous les agents</a>
    <a class="btn btn--primary btn--sm" href="/app/affectations"><?= icon('route', 15) ?> Créer une affectation</a>
</div>

<div class="split">
    <section class="panel">
        <div class="panel__body">
            <div class="person" style="gap:1rem">
                <span class="person__avatar" style="width:56px;height:56px;font-size:1rem" aria-hidden="true">
                    <?= e(initials($employe['prenom'] . ' ' . $employe['nom'])) ?>
                </span>
                <div>
                    <h2 style="font-size:1.4rem"><?= e($employe['civilite'] . ' ' . $employe['prenom'] . ' ' . $employe['nom']) ?></h2>
                    <p class="text-muted" style="font-size:.9rem"><?= e($employe['poste']) ?></p>
                    <div class="cluster" style="margin-top:.6rem;gap:.5rem">
                        <span class="badge mono"><?= e($employe['numEmp']) ?></span>
                        <?php if ((int) $employe['is_active'] === 1): ?>
                            <span class="badge badge--ok"><?= icon('check', 12) ?> Actif</span>
                        <?php else: ?>
                            <span class="badge"><?= icon('x', 12) ?> Inactif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="kv" style="margin-top:1.6rem">
                <div class="kv__row">
                    <span class="kv__key">Email</span>
                    <span class="kv__value"><a href="mailto:<?= e($employe['mail']) ?>"><?= e($employe['mail']) ?></a></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Téléphone</span>
                    <span class="kv__value"><?= e((string) ($employe['telephone'] ?: '—')) ?></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Lieu actuel</span>
                    <span class="kv__value">
                        <?= e((string) $employe['nom_lieu']) ?>
                        <span class="text-muted"> · <?= e((string) $employe['province']) ?></span>
                    </span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Date d'embauche</span>
                    <span class="kv__value"><?= e(format_date((string) $employe['date_embauche'], 'd F Y')) ?></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Affectations enregistrées</span>
                    <span class="kv__value"><?= count($historique) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Situation actuelle</div>
        </header>

        <div class="panel__body">
            <?php if ($encours === null): ?>
                <div class="empty-state" style="padding:2rem 0">
                    <span class="empty-state__icon"><?= icon('info', 20) ?></span>
                    <h3>Aucune affectation enregistrée</h3>
                    <p>Cet agent n'a jamais fait l'objet d'un mouvement documenté.</p>
                </div>
            <?php else: ?>
                <div class="stack-sm">
                    <div class="cluster" style="justify-content:space-between">
                        <span class="mono"><?= e($encours['numAffect']) ?></span>
                        <?php $statut = (string) $encours['statut']; require BASE_PATH . '/views/components/status-badge.php'; ?>
                    </div>

                    <div class="kanban__route" style="font-size:1rem">
                        <?= icon('route', 16) ?>
                        <?= e($encours['ancien_design']) ?> → <?= e($encours['nouveau_design']) ?>
                    </div>

                    <div class="kpi-line"><span>Date d'affectation</span><b><?= e(format_date((string) $encours['dateAffect'])) ?></b></div>
                    <div class="kpi-line"><span>Prise de service</span><b><?= e(format_date((string) $encours['datePriseService'])) ?></b></div>
                    <div class="kpi-line">
                        <span>Motif</span>
                        <b><?= e(\App\Models\Affectation::MOTIFS[$encours['motif']] ?? 'Non précisé') ?></b>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title">Parcours professionnel</div>
            <div class="text-muted" style="font-size:.8rem">Historique complet, du plus récent au plus ancien</div>
        </div>
        <a class="btn btn--outline btn--sm" href="/app/rapports/impression" target="_blank" rel="noopener">
            <?= icon('printer', 14) ?> Imprimer
        </a>
    </header>

    <div class="panel__body">
        <?php if ($historique === []): ?>
            <p class="text-muted">Aucun mouvement enregistré pour cet agent.</p>
        <?php else: ?>
            <div class="steps-vertical">
                <?php foreach ($historique as $item): ?>
                    <div class="steps-vertical__item <?= $item['statut'] === 'applique' ? 'is-done' : '' ?>">
                        <span class="steps-vertical__marker"><?= icon('route', 15) ?></span>
                        <div>
                            <div class="steps-vertical__title">
                                <?= e($item['ancien_design']) ?> → <?= e($item['nouveau_design']) ?>
                            </div>
                            <div class="steps-vertical__meta">
                                <span class="mono"><?= e($item['numAffect']) ?></span> ·
                                <?= e(format_date((string) $item['dateAffect'])) ?> ·
                                <?= e(\App\Models\Affectation::STATUTS[$item['statut']] ?? $item['statut']) ?> ·
                                <?= e(\App\Models\Affectation::MOTIFS[$item['motif']] ?? 'motif non précisé') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
