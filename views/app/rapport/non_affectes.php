<?php

declare(strict_types=1);

/**
 * Agents sans affectation active — file de traitement prioritaire.
 *
 * @var list<array<string, mixed>> $employes
 */
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/rapports"><?= icon('arrow-left', 15) ?> Retour aux rapports</a>
    <a class="btn btn--primary btn--sm" href="/app/affectations"><?= icon('route', 15) ?> Créer une affectation</a>
</div>

<div class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title"><?= count($employes) ?> agent(s) sans affectation</div>
            <div class="text-muted" style="font-size:.8rem">
                Agents actifs sans affectation planifiée ni appliquée
            </div>
        </div>
        <?php if ($employes !== []): ?>
            <span class="badge badge--warn"><?= icon('alert', 13) ?> Action requise</span>
        <?php endif; ?>
    </header>

    <?php if ($employes === []): ?>
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('check-circle', 22) ?></span>
            <h3>Situation nominale</h3>
            <p>Tous les agents actifs disposent d'une affectation documentée.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Agent</th>
                        <th scope="col">Matricule</th>
                        <th scope="col">Poste</th>
                        <th scope="col">Lieu actuel</th>
                        <th scope="col">Ancienneté</th>
                        <th scope="col" class="cell-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employes as $employe): ?>
                        <tr>
                            <td>
                                <div class="person">
                                    <span class="person__avatar person__avatar--cool" aria-hidden="true">
                                        <?= e(initials($employe['prenom'] . ' ' . $employe['nom'])) ?>
                                    </span>
                                    <div>
                                        <div class="person__name"><?= e($employe['prenom'] . ' ' . $employe['nom']) ?></div>
                                        <div class="person__meta"><?= e($employe['mail']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="mono"><?= e($employe['numEmp']) ?></td>
                            <td><?= e($employe['poste']) ?></td>
                            <td><?= e((string) ($employe['nom_lieu'] ?? '—')) ?></td>
                            <td><?= e(format_date((string) $employe['date_embauche'], 'M Y')) ?></td>
                            <td class="cell-actions">
                                <span class="row-actions">
                                    <a class="icon-btn" href="/app/employes/<?= e($employe['numEmp']) ?>" aria-label="Voir la fiche">
                                        <?= icon('eye', 15) ?>
                                    </a>
                                    <a class="icon-btn icon-btn--accent" href="/app/affectations" aria-label="Créer une affectation">
                                        <?= icon('plus', 15) ?>
                                    </a>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
