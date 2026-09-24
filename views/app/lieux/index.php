<?php

declare(strict_types=1);

/**
 * Référentiel des lieux : couverture par site et gestion CRUD.
 *
 * @var list<array<string, mixed>> $lieux
 * @var list<string> $provinces
 * @var string $term
 * @var string $province
 * @var int $maxEffectif
 */
?>
<div class="page-head__actions">
    <button class="btn btn--primary btn--sm" type="button" data-modal-open="modal-lieu">
        <?= icon('plus', 16) ?>
        Nouveau lieu
    </button>
</div>

<form class="filters" method="get" action="/app/lieux" role="search">
    <div class="field">
        <label class="field__label" for="q">Recherche</label>
        <input class="input" type="search" id="q" name="q" value="<?= e($term) ?>" placeholder="Identifiant, désignation, province…">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="province">Province</label>
        <select class="select" id="province" name="province" data-auto-submit>
            <option value="">Toutes les provinces</option>
            <?php foreach ($provinces as $item): ?>
                <option value="<?= e($item) ?>" <?= $province === $item ? 'selected' : '' ?>><?= e($item) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filters__actions">
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('search', 15) ?> Filtrer</button>
        <a class="btn btn--ghost btn--sm" href="/app/lieux">Réinitialiser</a>
    </div>
</form>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div class="panel__title"><?= count($lieux) ?> lieu(x) référencé(s)</div>
        </header>

        <?php if ($lieux === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon"><?= icon('building', 22) ?></span>
                <h3>Aucun lieu trouvé</h3>
                <p>Créez un premier site pour commencer à rattacher des agents.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Identifiant</th>
                            <th scope="col">Désignation</th>
                            <th scope="col">Province</th>
                            <th scope="col">Effectif</th>
                            <th scope="col">Capacité</th>
                            <th scope="col" class="cell-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lieux as $lieu): ?>
                            <?php
                            $taux = $lieu['capacite'] !== null && (int) $lieu['capacite'] > 0
                                ? round((int) $lieu['effectif'] / (int) $lieu['capacite'] * 100)
                                : null;
                            ?>
                            <tr>
                                <td class="mono"><?= e($lieu['idlieu']) ?></td>
                                <td class="cell-strong"><?= e($lieu['design']) ?></td>
                                <td><?= e($lieu['province']) ?></td>
                                <td><?= (int) $lieu['effectif'] ?> agents</td>
                                <td>
                                    <?php if ($taux === null): ?>
                                        <span class="text-muted">Non définie</span>
                                    <?php else: ?>
                                        <span class="badge <?= $taux > 100 ? 'badge--danger' : ($taux > 85 ? 'badge--warn' : 'badge--ok') ?>">
                                            <?= $taux ?> % de remplissage
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <span class="row-actions">
                                        <a class="icon-btn" href="/app/lieux/<?= e($lieu['idlieu']) ?>" aria-label="Consulter la fiche du lieu">
                                            <?= icon('eye', 15) ?>
                                        </a>
                                        <button class="icon-btn icon-btn--accent" type="button"
                                                data-modal-open="modal-lieu-<?= e($lieu['idlieu']) ?>" aria-label="Modifier le lieu">
                                            <?= icon('edit', 15) ?>
                                        </button>
                                        <form action="/app/lieux/<?= e($lieu['idlieu']) ?>" method="post" data-ajax
                                              data-confirm="Supprimer le lieu « <?= e($lieu['design']) ?> » ?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="icon-btn icon-btn--danger" type="submit" aria-label="Supprimer le lieu">
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
        <?php endif; ?>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Couverture des effectifs</div>
                <div class="text-muted" style="font-size:.8rem">Agents rattachés par site</div>
            </div>
        </header>

        <div class="panel__body">
            <div class="coverage">
                <?php foreach ($lieux as $lieu): ?>
                    <div class="coverage__row">
                        <span class="coverage__name"><?= e($lieu['design']) ?></span>
                        <span class="coverage__value"><?= (int) $lieu['effectif'] ?></span>
                        <span class="coverage__bar">
                            <i style="width:<?= round((int) $lieu['effectif'] / max($maxEffectif, 1) * 100) ?>%"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modale de création -->
<div class="modal" id="modal-lieu" role="dialog" aria-modal="true" aria-labelledby="titre-modal-lieu" aria-hidden="true">
    <div class="modal__panel">
        <header class="modal__head">
            <div class="modal__title" id="titre-modal-lieu">Créer un lieu d'affectation</div>
            <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
        </header>

        <div class="modal__body">
            <form data-ajax action="/app/lieux" method="post" novalidate>
                <?= csrf_field() ?>
                <div class="form-grid-2">
                    <div class="field">
                        <label class="field__label" for="idlieu">Identifiant <span class="req">*</span></label>
                        <input class="input" id="idlieu" name="idlieu" required maxlength="10" placeholder="LIEU-008">
                    </div>
                    <div class="field">
                        <label class="field__label" for="province-create">Province <span class="req">*</span></label>
                        <input class="input" id="province-create" name="province" required maxlength="100" list="liste-provinces">
                        <datalist id="liste-provinces">
                            <?php foreach ($provinces as $item): ?>
                                <option value="<?= e($item) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="field field--full">
                        <label class="field__label" for="design">Désignation <span class="req">*</span></label>
                        <input class="input" id="design" name="design" required maxlength="120" placeholder="Antenne Morondava">
                    </div>
                    <div class="field">
                        <label class="field__label" for="code_analytique">Code analytique</label>
                        <input class="input" id="code_analytique" name="code_analytique" maxlength="20" placeholder="MOR-01">
                    </div>
                    <div class="field">
                        <label class="field__label" for="capacite">Capacité d'accueil</label>
                        <input class="input" type="number" id="capacite" name="capacite" min="0" placeholder="40">
                    </div>
                </div>

                <div class="form-foot">
                    <button class="btn btn--ghost" type="button" data-modal-close>Annuler</button>
                    <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Enregistrer le lieu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de modification -->
<?php foreach ($lieux as $lieu): ?>
    <div class="modal" id="modal-lieu-<?= e($lieu['idlieu']) ?>" role="dialog" aria-modal="true" aria-hidden="true"
         aria-labelledby="titre-<?= e($lieu['idlieu']) ?>">
        <div class="modal__panel">
            <header class="modal__head">
                <div>
                    <div class="modal__title" id="titre-<?= e($lieu['idlieu']) ?>">Modifier <?= e($lieu['design']) ?></div>
                    <p class="text-muted" style="font-size:.84rem">Identifiant <?= e($lieu['idlieu']) ?></p>
                </div>
                <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
            </header>

            <div class="modal__body">
                <form data-ajax action="/app/lieux/<?= e($lieu['idlieu']) ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="form-grid-2">
                        <div class="field field--full">
                            <label class="field__label" for="design-<?= e($lieu['idlieu']) ?>">Désignation</label>
                            <input class="input" id="design-<?= e($lieu['idlieu']) ?>" name="design" required
                                   value="<?= e($lieu['design']) ?>" maxlength="120">
                        </div>
                        <div class="field">
                            <label class="field__label" for="province-<?= e($lieu['idlieu']) ?>">Province</label>
                            <input class="input" id="province-<?= e($lieu['idlieu']) ?>" name="province" required
                                   value="<?= e($lieu['province']) ?>" maxlength="100">
                        </div>
                        <div class="field">
                            <label class="field__label" for="code-<?= e($lieu['idlieu']) ?>">Code analytique</label>
                            <input class="input" id="code-<?= e($lieu['idlieu']) ?>" name="code_analytique"
                                   value="<?= e((string) $lieu['code_analytique']) ?>" maxlength="20">
                        </div>
                        <div class="field">
                            <label class="field__label" for="capacite-<?= e($lieu['idlieu']) ?>">Capacité</label>
                            <input class="input" type="number" min="0" id="capacite-<?= e($lieu['idlieu']) ?>" name="capacite"
                                   value="<?= e((string) $lieu['capacite']) ?>">
                        </div>
                        <div class="field">
                            <label class="checkbox" for="actif-<?= e($lieu['idlieu']) ?>">
                                <input type="checkbox" id="actif-<?= e($lieu['idlieu']) ?>" name="is_active" value="1"
                                       <?= (int) $lieu['is_active'] === 1 ? 'checked' : '' ?>>
                                <span>Lieu actif</span>
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
