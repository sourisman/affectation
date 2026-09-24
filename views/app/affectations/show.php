<?php

declare(strict_types=1);

/**
 * Détail d'une affectation : informations, chronologie et actions.
 *
 * @var array<string, mixed> $affectation
 * @var list<array<string, mixed>> $parcours
 */

$statut = (string) $affectation['statut'];
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/affectations"><?= icon('arrow-left', 15) ?> Retour</a>

    <?php if ($statut === 'planifie'): ?>
        <form action="/app/affectations/<?= (int) $affectation['id'] ?>/appliquer" method="post" data-ajax>
            <?= csrf_field() ?>
            <button class="btn btn--primary btn--sm" type="submit">
                <?= icon('check', 15) ?> Appliquer l'affectation
            </button>
        </form>
    <?php endif; ?>

    <?php if ($statut !== 'annule'): ?>
        <form action="/app/affectations/<?= (int) $affectation['id'] ?>/annuler" method="post" data-ajax
              data-confirm="Confirmer l'annulation de cette affectation ?">
            <?= csrf_field() ?>
            <button class="btn btn--danger btn--sm" type="submit"><?= icon('x', 15) ?> Annuler</button>
        </form>
    <?php endif; ?>
</div>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Dossier <?= e($affectation['numAffect']) ?></div>
                <div class="text-muted" style="font-size:.8rem">
                    Enregistré le <?= e(format_date((string) $affectation['created_at'])) ?>
                    <?php if (!empty($affectation['auteur'])): ?> par <?= e($affectation['auteur']) ?><?php endif; ?>
                </div>
            </div>
            <?php require BASE_PATH . '/views/components/status-badge.php'; ?>
        </header>

        <div class="panel__body">
            <div class="kv">
                <div class="kv__row">
                    <span class="kv__key">Agent</span>
                    <span class="kv__value">
                        <strong><?= e($affectation['civilite'] . ' ' . $affectation['prenom'] . ' ' . $affectation['nom']) ?></strong>
                        <span class="text-muted"> · <?= e($affectation['numEmp']) ?> · <?= e($affectation['poste']) ?></span>
                    </span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Email</span>
                    <span class="kv__value"><?= e((string) $affectation['mail']) ?></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Trajet</span>
                    <span class="kv__value">
                        <?= e($affectation['ancien_design']) ?> <span class="text-muted">(<?= e($affectation['ancien_province']) ?>)</span>
                        <?= icon('arrow-right', 14) ?>
                        <strong><?= e($affectation['nouveau_design']) ?></strong>
                        <span class="text-muted">(<?= e($affectation['nouveau_province']) ?>)</span>
                    </span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Date d'affectation</span>
                    <span class="kv__value"><?= e(format_date((string) $affectation['dateAffect'], 'd F Y')) ?></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Prise de service</span>
                    <span class="kv__value"><?= e(format_date((string) $affectation['datePriseService'], 'd F Y')) ?></span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Motif</span>
                    <span class="kv__value">
                        <?= e(\App\Models\Affectation::MOTIFS[$affectation['motif']] ?? 'Non précisé') ?>
                    </span>
                </div>
                <div class="kv__row">
                    <span class="kv__key">Observation</span>
                    <span class="kv__value"><?= nl2br(e((string) ($affectation['observation'] ?? '—'))) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Chronologie du dossier</div>
        </header>

        <div class="panel__body">
            <div class="steps-vertical">
                <div class="steps-vertical__item is-done">
                    <span class="steps-vertical__marker"><?= icon('file', 15) ?></span>
                    <div>
                        <div class="steps-vertical__title">Dossier créé</div>
                        <div class="steps-vertical__meta">
                            <?= e(format_date((string) $affectation['created_at'], 'd/m/Y H:i')) ?>
                            <?php if (!empty($affectation['auteur'])): ?> — <?= e($affectation['auteur']) ?><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="steps-vertical__item <?= $statut === 'applique' ? 'is-done' : '' ?>">
                    <span class="steps-vertical__marker"><?= icon('check-circle', 15) ?></span>
                    <div>
                        <div class="steps-vertical__title">Application et transfert</div>
                        <div class="steps-vertical__meta">
                            <?= $statut === 'applique' ? 'Affectation appliquée, agent transféré au ' . e($affectation['nouveau_design']) . '.' : 'En attente d\'application.' ?>
                        </div>
                    </div>
                </div>

                <div class="steps-vertical__item <?= $statut === 'applique' ? 'is-done' : '' ?>">
                    <span class="steps-vertical__marker"><?= icon('calendar', 15) ?></span>
                    <div>
                        <div class="steps-vertical__title">Prise de service effective</div>
                        <div class="steps-vertical__meta"><?= e(format_date((string) $affectation['datePriseService'], 'd/m/Y')) ?></div>
                    </div>
                </div>

                <?php if ($statut === 'annule'): ?>
                    <div class="steps-vertical__item">
                        <span class="steps-vertical__marker"><?= icon('x-circle', 15) ?></span>
                        <div>
                            <div class="steps-vertical__title">Dossier annulé</div>
                            <div class="steps-vertical__meta">L'agent reste rattaché à <?= e($affectation['ancien_design']) ?>.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<section class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title">Parcours complet de l'agent</div>
            <div class="text-muted" style="font-size:.8rem">Toutes les affectations de <?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></div>
        </div>
        <a class="btn btn--ghost btn--sm" href="/app/employes/<?= e($affectation['numEmp']) ?>">
            Fiche agent <?= icon('arrow-right', 14) ?>
        </a>
    </header>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Référence</th>
                    <th scope="col">Trajet</th>
                    <th scope="col">Date</th>
                    <th scope="col">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parcours as $item): ?>
                    <tr>
                        <td class="mono"><?= e($item['numAffect']) ?></td>
                        <td><?= e($item['ancien_design']) ?> → <?= e($item['nouveau_design']) ?></td>
                        <td><?= e(format_date((string) $item['dateAffect'])) ?></td>
                        <td>
                            <?php $statut = (string) $item['statut']; require BASE_PATH . '/views/components/status-badge.php'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
