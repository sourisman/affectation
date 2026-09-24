<?php

declare(strict_types=1);

/**
 * Affectations : liste filtrable, création et actions de cycle de vie.
 *
 * @var list<array<string, mixed>> $affectations
 * @var list<array<string, mixed>> $lieux
 * @var list<array<string, mixed>> $employes
 * @var array<string, string> $statuts
 * @var array<string, string> $motifs
 * @var array<string, mixed> $filters
 * @var array<string, mixed> $pagination
 * @var string $reference
 */
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/app/affectations/export?<?= e(http_build_query(array_filter($filters))) ?>">
        <?= icon('download', 15) ?>
        Exporter (CSV)
    </a>
    <button class="btn btn--primary btn--sm" type="button" data-modal-open="modal-affectation">
        <?= icon('route', 15) ?>
        Nouvelle affectation
    </button>
</div>

<form class="filters" method="get" action="/app/affectations" role="search">
    <div class="field">
        <label class="field__label" for="q">Recherche</label>
        <input class="input" type="search" id="q" name="q" value="<?= e((string) $filters['term']) ?>"
               placeholder="Référence, agent, motif…">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="statut">Statut</label>
        <select class="select" id="statut" name="statut" data-auto-submit>
            <option value="">Tous les statuts</option>
            <?php foreach ($statuts as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['statut'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field field--sm">
        <label class="field__label" for="lieu">Lieu concerné</label>
        <select class="select" id="lieu" name="lieu" data-auto-submit>
            <option value="">Tous les lieux</option>
            <?php foreach ($lieux as $lieu): ?>
                <option value="<?= e($lieu['idlieu']) ?>" <?= $filters['lieu'] === $lieu['idlieu'] ? 'selected' : '' ?>>
                    <?= e($lieu['design']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field field--sm">
        <label class="field__label" for="from">Du</label>
        <input class="input" type="date" id="from" name="from" value="<?= e((string) $filters['from']) ?>">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="to">Au</label>
        <input class="input" type="date" id="to" name="to" value="<?= e((string) $filters['to']) ?>">
    </div>

    <div class="filters__actions">
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('search', 15) ?> Filtrer</button>
        <a class="btn btn--ghost btn--sm" href="/app/affectations">Réinitialiser</a>
    </div>
</form>

<?php if ($affectations === []): ?>
    <div class="panel">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('route', 22) ?></span>
            <h3>Aucune affectation pour ces critères</h3>
            <p>Élargissez la période ou créez un nouveau mouvement.</p>
        </div>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Référence</th>
                    <th scope="col">Agent</th>
                    <th scope="col">Trajet</th>
                    <th scope="col">Affectation</th>
                    <th scope="col">Prise de service</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($affectations as $affectation): ?>
                    <tr>
                        <td class="mono"><?= e($affectation['numAffect']) ?></td>
                        <td>
                            <div class="person">
                                <span class="person__avatar person__avatar--cool" aria-hidden="true">
                                    <?= e(initials($affectation['prenom'] . ' ' . $affectation['nom'])) ?>
                                </span>
                                <div style="min-width:0">
                                    <div class="person__name"><?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></div>
                                    <div class="person__meta"><?= e($affectation['poste']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="kanban__route">
                                <?= e($affectation['ancien_design']) ?>
                                <?= icon('arrow-right', 14) ?>
                                <strong><?= e($affectation['nouveau_design']) ?></strong>
                            </span>
                        </td>
                        <td><?= e(format_date($affectation['dateAffect'])) ?></td>
                        <td><?= e(format_date($affectation['datePriseService'])) ?></td>
                        <td>
                            <?php $statut = $affectation['statut']; require BASE_PATH . '/views/components/status-badge.php'; ?>
                        </td>
                        <td class="cell-actions">
                            <span class="row-actions">
                                <a class="icon-btn" href="/app/affectations/<?= (int) $affectation['id'] ?>" aria-label="Voir le détail">
                                    <?= icon('eye', 15) ?>
                                </a>

                                <?php if ($affectation['statut'] === 'planifie'): ?>
                                    <form action="/app/affectations/<?= (int) $affectation['id'] ?>/appliquer" method="post" data-ajax>
                                        <?= csrf_field() ?>
                                        <button class="icon-btn icon-btn--accent" type="submit" aria-label="Appliquer l'affectation">
                                            <?= icon('check', 15) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($affectation['statut'] !== 'annule'): ?>
                                    <form action="/app/affectations/<?= (int) $affectation['id'] ?>/annuler" method="post" data-ajax
                                          data-confirm="Annuler l'affectation <?= e($affectation['numAffect']) ?> ?">
                                        <?= csrf_field() ?>
                                        <button class="icon-btn icon-btn--danger" type="submit" aria-label="Annuler l'affectation">
                                            <?= icon('x', 15) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php require BASE_PATH . '/views/components/pagination.php'; ?>
<?php endif; ?>

<!-- Modale de création d'affectation -->
<div class="modal" id="modal-affectation" role="dialog" aria-modal="true" aria-hidden="true"
     aria-labelledby="titre-modal-affectation">
    <div class="modal__panel">
        <header class="modal__head">
            <div>
                <div class="modal__title" id="titre-modal-affectation">Créer une affectation</div>
                <p class="text-muted" style="font-size:.84rem">Prochaine référence : <span class="mono"><?= e($reference) ?></span></p>
            </div>
            <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
        </header>

        <div class="modal__body">
            <form data-ajax action="/app/affectations" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="form-grid-2">
                    <div class="field field--full">
                        <label class="field__label" for="numEmp-select">Agent concerné <span class="req">*</span></label>
                        <select class="select" id="numEmp-select" name="numEmp" required>
                            <option value="">Sélectionner un agent…</option>
                            <?php foreach ($employes as $employe): ?>
                                <option value="<?= e($employe['numEmp']) ?>">
                                    <?= e($employe['numEmp'] . ' — ' . $employe['prenom'] . ' ' . $employe['nom'] . ' (' . ($employe['nom_lieu'] ?? '') . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field__hint">Seuls les agents actifs sont proposés.</span>
                    </div>

                    <div class="field">
                        <label class="field__label" for="ancienLieu">Lieu d'origine <span class="req">*</span></label>
                        <select class="select" id="ancienLieu" name="ancienLieu" required>
                            <?php foreach ($lieux as $lieu): ?>
                                <option value="<?= e($lieu['idlieu']) ?>"><?= e($lieu['design']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label class="field__label" for="nouveauLieu">Lieu d'accueil <span class="req">*</span></label>
                        <select class="select" id="nouveauLieu" name="nouveauLieu" required>
                            <?php foreach ($lieux as $lieu): ?>
                                <option value="<?= e($lieu['idlieu']) ?>"><?= e($lieu['design']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label class="field__label" for="dateAffect">Date d'affectation <span class="req">*</span></label>
                        <input class="input" type="date" id="dateAffect" name="dateAffect" required value="<?= e(date('Y-m-d')) ?>">
                    </div>

                    <div class="field">
                        <label class="field__label" for="datePriseService">Prise de service <span class="req">*</span></label>
                        <input class="input" type="date" id="datePriseService" name="datePriseService" required
                               value="<?= e(date('Y-m-d', strtotime('+30 days'))) ?>">
                        <span class="field__hint">Doit être postérieure ou égale à la date d'affectation.</span>
                    </div>

                    <div class="field">
                        <label class="field__label" for="motif">Motif</label>
                        <select class="select" id="motif" name="motif">
                            <?php foreach ($motifs as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label class="field__label" for="statut-create">Statut initial</label>
                        <select class="select" id="statut-create" name="statut">
                            <option value="planifie">Planifiée (recommandé)</option>
                            <option value="applique">Appliquée immédiatement</option>
                        </select>
                    </div>

                    <div class="field field--full">
                        <label class="field__label" for="observation">Observation</label>
                        <textarea class="textarea" id="observation" name="observation" maxlength="2000"
                                  placeholder="Contexte de la décision, pièces justificatives, accord de la direction…"></textarea>
                    </div>
                </div>

                <div class="form-foot">
                    <button class="btn btn--ghost" type="button" data-modal-close>Annuler</button>
                    <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Enregistrer l'affectation</button>
                </div>
            </form>
        </div>
    </div>
</div>
