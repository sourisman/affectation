<?php

declare(strict_types=1);

/**
 * Liste des employés : recherche, filtres, pagination et modales CRUD.
 *
 * @var list<array<string, mixed>> $employes
 * @var list<array<string, mixed>> $lieux
 * @var list<string> $postes
 * @var array<string, mixed> $filters
 * @var array<string, mixed> $pagination
 */

$civilites = ['M.', 'Mme', 'Mlle'];
?>
<div class="page-head__actions">
    <button class="btn btn--primary btn--sm" type="button" data-modal-open="modal-employe" id="ouvrir-nouvel-agent">
        <?= icon('user-plus', 16) ?>
        Nouvel agent
    </button>
</div>

<form class="filters" method="get" action="/app/employes" role="search">
    <div class="field">
        <label class="field__label" for="q">Recherche</label>
        <input class="input" type="search" id="q" name="q" value="<?= e((string) $filters['term']) ?>"
               placeholder="Nom, prénom, matricule, email…">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="lieu">Lieu</label>
        <select class="select" id="lieu" name="lieu" data-auto-submit>
            <option value="">Tous les lieux</option>
            <?php foreach ($lieux as $lieu): ?>
                <option value="<?= e($lieu['idlieu']) ?>" <?= $filters['lieu'] === $lieu['idlieu'] ? 'selected' : '' ?>>
                    <?= e($lieu['design']) ?> (<?= (int) $lieu['effectif'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field field--sm">
        <label class="field__label" for="poste">Poste</label>
        <select class="select" id="poste" name="poste" data-auto-submit>
            <option value="">Tous les postes</option>
            <?php foreach ($postes as $poste): ?>
                <option value="<?= e($poste) ?>" <?= $filters['poste'] === $poste ? 'selected' : '' ?>><?= e($poste) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field field--sm">
        <label class="field__label" for="statut">Statut</label>
        <select class="select" id="statut" name="statut" data-auto-submit>
            <option value="actifs" <?= $filters['actif'] ? 'selected' : '' ?>>Actifs</option>
            <option value="inactifs" <?= !$filters['actif'] ? 'selected' : '' ?>>Inactifs</option>
        </select>
    </div>

    <div class="filters__actions">
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('search', 15) ?> Filtrer</button>
        <a class="btn btn--ghost btn--sm" href="/app/employes">Réinitialiser</a>
    </div>
</form>

<?php if ($employes === []): ?>
    <div class="panel">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('users', 22) ?></span>
            <h3>Aucun agent ne correspond à ces critères</h3>
            <p>Modifiez la recherche ou créez une nouvelle fiche agent.</p>
            <button class="btn btn--primary btn--sm" type="button" data-modal-open="modal-employe">
                <?= icon('user-plus', 15) ?> Nouvel agent
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Agent</th>
                    <th scope="col">Matricule</th>
                    <th scope="col">Poste</th>
                    <th scope="col">Lieu</th>
                    <th scope="col">Ancienneté</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employes as $employe): ?>
                    <tr>
                        <td>
                            <div class="person">
                                <span class="person__avatar" aria-hidden="true"><?= e(initials($employe['prenom'] . ' ' . $employe['nom'])) ?></span>
                                <div style="min-width:0">
                                    <div class="person__name"><?= e($employe['civilite'] . ' ' . $employe['prenom'] . ' ' . $employe['nom']) ?></div>
                                    <div class="person__meta"><?= e($employe['mail']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="mono"><?= e($employe['numEmp']) ?></td>
                        <td><?= e($employe['poste']) ?></td>
                        <td><?= e($employe['nom_lieu'] ?? '—') ?></td>
                        <td><?= e(format_date($employe['date_embauche'], 'M Y')) ?></td>
                        <td>
                            <?php if ((int) $employe['is_active'] === 1): ?>
                                <span class="badge badge--ok"><?= icon('check', 12) ?> Actif</span>
                            <?php else: ?>
                                <span class="badge"><?= icon('x', 12) ?> Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-actions">
                            <span class="row-actions">
                                <a class="icon-btn" href="/app/employes/<?= e($employe['numEmp']) ?>" aria-label="Consulter la fiche">
                                    <?= icon('eye', 15) ?>
                                </a>
                                <button class="icon-btn icon-btn--accent" type="button"
                                        data-modal-open="modal-employe-<?= e($employe['numEmp']) ?>"
                                        aria-label="Modifier la fiche">
                                    <?= icon('edit', 15) ?>
                                </button>
                                <form action="/app/employes/<?= e($employe['numEmp']) ?>" method="post" data-ajax
                                      data-confirm="Supprimer définitivement l'agent <?= e($employe['prenom'] . ' ' . $employe['nom']) ?> ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="icon-btn icon-btn--danger" type="submit" aria-label="Supprimer l'agent">
                                        <?= icon('trash', 15) ?>
                                    </button>
                                </form>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php require BASE_PATH . '/views/components/pagination.php'; ?>
<?php endif; ?>

<!-- Modale de création -->
<div class="modal" id="modal-employe" role="dialog" aria-modal="true" aria-labelledby="titre-modal-employe" aria-hidden="true">
    <div class="modal__panel">
        <header class="modal__head">
            <div>
                <div class="modal__title" id="titre-modal-employe">Créer une fiche agent</div>
                <p class="text-muted" style="font-size:.84rem">Le matricule sert d\'identifiant unique dans toute la plateforme.</p>
            </div>
            <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
        </header>

        <div class="modal__body">
            <form data-ajax action="/app/employes" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="form-grid-2">
                    <div class="field">
                        <label class="field__label" for="numEmp">Matricule <span class="req">*</span></label>
                        <input class="input" id="numEmp" name="numEmp" required maxlength="10" placeholder="EMP-0043">
                    </div>

                    <div class="field">
                        <label class="field__label" for="civilite">Civilité <span class="req">*</span></label>
                        <select class="select" id="civilite" name="civilite" required>
                            <?php foreach ($civilites as $civilite): ?>
                                <option value="<?= e($civilite) ?>"><?= e($civilite) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label class="field__label" for="nom">Nom <span class="req">*</span></label>
                        <input class="input" id="nom" name="nom" required maxlength="80">
                    </div>

                    <div class="field">
                        <label class="field__label" for="prenom">Prénom <span class="req">*</span></label>
                        <input class="input" id="prenom" name="prenom" required maxlength="80">
                    </div>

                    <div class="field">
                        <label class="field__label" for="mail">Email professionnel <span class="req">*</span></label>
                        <input class="input" type="email" id="mail" name="mail" required maxlength="150">
                    </div>

                    <div class="field">
                        <label class="field__label" for="telephone">Téléphone</label>
                        <input class="input" type="tel" id="telephone" name="telephone" maxlength="30" placeholder="+261 34 00 000 00">
                    </div>

                    <div class="field">
                        <label class="field__label" for="poste-create">Poste <span class="req">*</span></label>
                        <input class="input" id="poste-create" name="poste" required maxlength="100" list="liste-postes">
                        <datalist id="liste-postes">
                            <?php foreach ($postes as $poste): ?>
                                <option value="<?= e($poste) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="field">
                        <label class="field__label" for="lieu-create">Lieu d'affectation <span class="req">*</span></label>
                        <select class="select" id="lieu-create" name="lieu" required>
                            <?php foreach ($lieux as $lieu): ?>
                                <option value="<?= e($lieu['idlieu']) ?>"><?= e($lieu['design']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field field--full">
                        <label class="field__label" for="date_embauche">Date d'embauche</label>
                        <input class="input" type="date" id="date_embauche" name="date_embauche">
                    </div>
                </div>

                <div class="form-foot">
                    <button class="btn btn--ghost" type="button" data-modal-close>Annuler</button>
                    <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Enregistrer l'agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de modification -->
<?php foreach ($employes as $employe): ?>
    <div class="modal" id="modal-employe-<?= e($employe['numEmp']) ?>" role="dialog" aria-modal="true"
         aria-labelledby="titre-modal-<?= e($employe['numEmp']) ?>" aria-hidden="true">
        <div class="modal__panel">
            <header class="modal__head">
                <div>
                    <div class="modal__title" id="titre-modal-<?= e($employe['numEmp']) ?>">
                        Modifier <?= e($employe['prenom'] . ' ' . $employe['nom']) ?>
                    </div>
                    <p class="text-muted" style="font-size:.84rem">Matricule <?= e($employe['numEmp']) ?></p>
                </div>
                <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
            </header>

            <div class="modal__body">
                <form data-ajax action="/app/employes/<?= e($employe['numEmp']) ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="form-grid-2">
                        <div class="field">
                            <label class="field__label" for="civilite-<?= e($employe['numEmp']) ?>">Civilité</label>
                            <select class="select" id="civilite-<?= e($employe['numEmp']) ?>" name="civilite" required>
                                <?php foreach ($civilites as $civilite): ?>
                                    <option value="<?= e($civilite) ?>" <?= $employe['civilite'] === $civilite ? 'selected' : '' ?>>
                                        <?= e($civilite) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label class="field__label" for="nom-<?= e($employe['numEmp']) ?>">Nom</label>
                            <input class="input" id="nom-<?= e($employe['numEmp']) ?>" name="nom" required
                                   value="<?= e($employe['nom']) ?>" maxlength="80">
                        </div>

                        <div class="field">
                            <label class="field__label" for="prenom-<?= e($employe['numEmp']) ?>">Prénom</label>
                            <input class="input" id="prenom-<?= e($employe['numEmp']) ?>" name="prenom" required
                                   value="<?= e($employe['prenom']) ?>" maxlength="80">
                        </div>

                        <div class="field">
                            <label class="field__label" for="mail-<?= e($employe['numEmp']) ?>">Email</label>
                            <input class="input" type="email" id="mail-<?= e($employe['numEmp']) ?>" name="mail" required
                                   value="<?= e($employe['mail']) ?>" maxlength="150">
                        </div>

                        <div class="field">
                            <label class="field__label" for="telephone-<?= e($employe['numEmp']) ?>">Téléphone</label>
                            <input class="input" type="tel" id="telephone-<?= e($employe['numEmp']) ?>" name="telephone"
                                   value="<?= e((string) $employe['telephone']) ?>" maxlength="30">
                        </div>

                        <div class="field">
                            <label class="field__label" for="poste-<?= e($employe['numEmp']) ?>">Poste</label>
                            <input class="input" id="poste-<?= e($employe['numEmp']) ?>" name="poste" required
                                   value="<?= e($employe['poste']) ?>" maxlength="100">
                        </div>

                        <div class="field">
                            <label class="field__label" for="lieu-<?= e($employe['numEmp']) ?>">Lieu d'affectation</label>
                            <select class="select" id="lieu-<?= e($employe['numEmp']) ?>" name="lieu" required>
                                <?php foreach ($lieux as $lieu): ?>
                                    <option value="<?= e($lieu['idlieu']) ?>" <?= $employe['lieu'] === $lieu['idlieu'] ? 'selected' : '' ?>>
                                        <?= e($lieu['design']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label class="field__label" for="date-<?= e($employe['numEmp']) ?>">Date d'embauche</label>
                            <input class="input" type="date" id="date-<?= e($employe['numEmp']) ?>" name="date_embauche"
                                   value="<?= e((string) $employe['date_embauche']) ?>">
                        </div>

                        <div class="field field--full">
                            <label class="checkbox" for="actif-<?= e($employe['numEmp']) ?>">
                                <input type="checkbox" id="actif-<?= e($employe['numEmp']) ?>" name="is_active" value="1"
                                       <?= (int) $employe['is_active'] === 1 ? 'checked' : '' ?>>
                                <span>Agent actif — décochez pour archiver sans supprimer l'historique.</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-foot">
                        <button class="btn btn--ghost" type="button" data-modal-close>Annuler</button>
                        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
