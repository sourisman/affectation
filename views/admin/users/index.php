<?php

declare(strict_types=1);

/**
 * Administration des comptes utilisateurs.
 *
 * @var list<array<string, mixed>> $users
 * @var array<string, string> $roles
 * @var string $search
 * @var string $role
 */

$currentUserId = (int) (auth_user()['id'] ?? 0);
?>
<div class="page-head__actions">
    <button class="btn btn--primary btn--sm" type="button" data-modal-open="modal-user">
        <?= icon('user-plus', 15) ?>
        Nouveau compte
    </button>
</div>

<form class="filters" method="get" action="/admin/utilisateurs" role="search">
    <div class="field">
        <label class="field__label" for="q">Recherche</label>
        <input class="input" type="search" id="q" name="q" value="<?= e($search) ?>" placeholder="Nom ou email">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="role">Rôle</label>
        <select class="select" id="role" name="role" data-auto-submit>
            <option value="">Tous les rôles</option>
            <?php foreach ($roles as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $role === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filters__actions">
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('search', 15) ?> Filtrer</button>
        <a class="btn btn--ghost btn--sm" href="/admin/utilisateurs">Réinitialiser</a>
    </div>
</form>

<div class="panel">
    <header class="panel__head">
        <div class="panel__title"><?= count($users) ?> compte(s)</div>
        <span class="badge"><?= icon('lock', 13) ?> Mots de passe hachés (bcrypt/argon2)</span>
    </header>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Créé le</th>
                    <th scope="col">Dernière connexion</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $utilisateur): ?>
                    <?php $isSelf = (int) $utilisateur['id'] === $currentUserId; ?>
                    <tr>
                        <td>
                            <div class="person">
                                <span class="person__avatar" aria-hidden="true"><?= e(initials((string) $utilisateur['name'])) ?></span>
                                <div>
                                    <div class="person__name">
                                        <?= e((string) $utilisateur['name']) ?>
                                        <?php if ($isSelf): ?><span class="badge" style="margin-left:.4rem">vous</span><?php endif; ?>
                                    </div>
                                    <div class="person__meta"><?= e((string) $utilisateur['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge <?= $utilisateur['role'] === 'admin' ? 'badge--accent' : '' ?>"><?= e($roles[$utilisateur['role']] ?? $utilisateur['role']) ?></span></td>
                        <td><?= e(format_date((string) $utilisateur['created_at'])) ?></td>
                        <td><?= e($utilisateur['last_login_at'] ? format_date((string) $utilisateur['last_login_at'], 'd/m/Y H:i') : 'Jamais') ?></td>
                        <td>
                            <?php if ((int) $utilisateur['is_active'] === 1): ?>
                                <span class="badge badge--ok"><?= icon('check', 12) ?> Actif</span>
                            <?php else: ?>
                                <span class="badge badge--danger"><?= icon('x', 12) ?> Désactivé</span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-actions">
                            <span class="row-actions">
                                <button class="icon-btn icon-btn--accent" type="button"
                                        data-modal-open="modal-user-<?= (int) $utilisateur['id'] ?>"
                                        aria-label="Modifier le compte">
                                    <?= icon('edit', 15) ?>
                                </button>

                                <?php if (!$isSelf): ?>
                                    <form action="/admin/utilisateurs/<?= (int) $utilisateur['id'] ?>" method="post" data-ajax
                                          data-confirm="Supprimer le compte de <?= e((string) $utilisateur['name']) ?> ?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="icon-btn icon-btn--danger" type="submit" aria-label="Supprimer le compte">
                                            <?= icon('trash', 15) ?>
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
</div>

<!-- Création -->
<div class="modal" id="modal-user" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="titre-modal-user">
    <div class="modal__panel">
        <header class="modal__head">
            <div>
                <div class="modal__title" id="titre-modal-user">Créer un compte utilisateur</div>
                <p class="text-muted" style="font-size:.84rem">
                    Exigence de mot de passe : 10 caractères minimum, une majuscule, une minuscule et un chiffre.
                </p>
            </div>
            <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
        </header>

        <div class="modal__body">
            <form data-ajax action="/admin/utilisateurs" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="form-grid-2">
                    <div class="field">
                        <label class="field__label" for="name">Nom complet <span class="req">*</span></label>
                        <input class="input" id="name" name="name" required maxlength="120">
                    </div>
                    <div class="field">
                        <label class="field__label" for="email">Email <span class="req">*</span></label>
                        <input class="input" type="email" id="email" name="email" required maxlength="190">
                    </div>
                    <div class="field">
                        <label class="field__label" for="role-create">Rôle <span class="req">*</span></label>
                        <select class="select" id="role-create" name="role" required>
                            <?php foreach ($roles as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label class="field__label" for="job_title">Fonction</label>
                        <input class="input" id="job_title" name="job_title" maxlength="120" placeholder="Responsable RH">
                    </div>
                    <div class="field field--full">
                        <label class="field__label" for="password">Mot de passe temporaire <span class="req">*</span></label>
                        <div class="input-group">
                            <input class="input" type="password" id="password" name="password" required minlength="10">
                            <button class="input-group__toggle" type="button" data-toggle-password="password"
                                    aria-label="Afficher le mot de passe" aria-pressed="false"><?= icon('eye', 16) ?></button>
                        </div>
                        <span class="field__hint">L'utilisateur devra le changer à sa première connexion.</span>
                    </div>
                </div>

                <div class="form-foot">
                    <button class="btn btn--ghost" type="button" data-modal-close>Annuler</button>
                    <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Créer le compte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modification -->
<?php foreach ($users as $utilisateur): ?>
    <?php $isSelf = (int) $utilisateur['id'] === $currentUserId; ?>
    <div class="modal" id="modal-user-<?= (int) $utilisateur['id'] ?>" role="dialog" aria-modal="true" aria-hidden="true"
         aria-labelledby="titre-user-<?= (int) $utilisateur['id'] ?>">
        <div class="modal__panel">
            <header class="modal__head">
                <div>
                    <div class="modal__title" id="titre-user-<?= (int) $utilisateur['id'] ?>">
                        Modifier <?= e((string) $utilisateur['name']) ?>
                    </div>
                    <p class="text-muted" style="font-size:.84rem"><?= e((string) $utilisateur['email']) ?></p>
                </div>
                <button class="modal__close" type="button" data-modal-close aria-label="Fermer"><?= icon('x', 16) ?></button>
            </header>

            <div class="modal__body">
                <form data-ajax action="/admin/utilisateurs/<?= (int) $utilisateur['id'] ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="form-grid-2">
                        <div class="field">
                            <label class="field__label" for="name-<?= (int) $utilisateur['id'] ?>">Nom complet</label>
                            <input class="input" id="name-<?= (int) $utilisateur['id'] ?>" name="name" required
                                   value="<?= e((string) $utilisateur['name']) ?>" maxlength="120">
                        </div>
                        <div class="field">
                            <label class="field__label" for="email-<?= (int) $utilisateur['id'] ?>">Email</label>
                            <input class="input" type="email" id="email-<?= (int) $utilisateur['id'] ?>" name="email" required
                                   value="<?= e((string) $utilisateur['email']) ?>" maxlength="190">
                        </div>
                        <div class="field">
                            <label class="field__label" for="role-<?= (int) $utilisateur['id'] ?>">Rôle</label>
                            <select class="select" id="role-<?= (int) $utilisateur['id'] ?>" name="role"
                                    <?= $isSelf ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $utilisateur['role'] === $key ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isSelf): ?>
                                <input type="hidden" name="role" value="<?= e((string) $utilisateur['role']) ?>">
                                <span class="field__hint">Vous ne pouvez pas modifier votre propre rôle.</span>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="field__label" for="job-<?= (int) $utilisateur['id'] ?>">Fonction</label>
                            <input class="input" id="job-<?= (int) $utilisateur['id'] ?>" name="job_title"
                                   value="<?= e((string) ($utilisateur['job_title'] ?? '')) ?>" maxlength="120">
                        </div>
                        <div class="field field--full">
                            <label class="field__label" for="password-<?= (int) $utilisateur['id'] ?>">Nouveau mot de passe</label>
                            <input class="input" type="password" id="password-<?= (int) $utilisateur['id'] ?>"
                                   name="password" minlength="10" placeholder="Laisser vide pour conserver l'actuel">
                        </div>

                        <div class="field field--full">
                            <label class="checkbox" for="actif-<?= (int) $utilisateur['id'] ?>">
                                <input type="checkbox" id="actif-<?= (int) $utilisateur['id'] ?>" name="is_active" value="1"
                                       <?= (int) $utilisateur['is_active'] === 1 ? 'checked' : '' ?>
                                       <?= $isSelf ? 'disabled' : '' ?>>
                                <span>Compte actif — un compte désactivé ne peut plus se connecter.</span>
                            </label>
                            <?php if ($isSelf): ?>
                                <input type="hidden" name="is_active" value="1">
                            <?php endif; ?>
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
