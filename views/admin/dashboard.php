<?php

declare(strict_types=1);

/**
 * Tableau de bord d'administration.
 *
 * @var array<string, int> $totaux
 * @var array<string, int> $activite
 * @var array<string, mixed> $dashboard
 * @var list<array<string, mixed>> $journal
 * @var list<array<string, mixed>> $derniers
 * @var list<array<string, mixed>> $messages
 * @var array<string, string> $environnement
 */
?>
<div class="kpis">
    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Comptes utilisateurs</span>
            <span class="kpi__icon"><?= icon('user', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['users'] ?>">0</div>
        <div class="kpi__foot"><span><?= (int) $totaux['actifs'] ?> actif(s) · <?= (int) $totaux['admins'] ?> administrateur(s)</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Données métier</span>
            <span class="kpi__icon"><?= icon('database', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['employes'] ?>">0</div>
        <div class="kpi__foot">
            <span><?= (int) $totaux['lieux'] ?> lieux · <?= (int) $totaux['affectations'] ?> affectations</span>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Messages reçus</span>
            <span class="kpi__icon"><?= icon('inbox', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $totaux['messages'] ?>">0</div>
        <div class="kpi__foot">
            <?php if ((int) $totaux['non_lus'] > 0): ?>
                <a class="text-accent" href="/admin/messages?statut=new">
                    <?= (int) $totaux['non_lus'] ?> non lu(s) <?= icon('arrow-right', 13) ?>
                </a>
            <?php else: ?>
                <span>Boîte à jour</span>
            <?php endif; ?>
        </div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Santé de la plateforme</span>
            <span class="kpi__icon"><?= icon('activity', 16) ?></span>
        </div>
        <div class="kpi__value"><?= e($environnement['php']) ?></div>
        <div class="kpi__foot">
            <span>Base <?= e($environnement['driver']) ?> · debug <?= e($environnement['debug']) ?> · pic <?= e($environnement['memoire']) ?></span>
        </div>
    </article>
</div>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Activité de la plateforme</div>
                <div class="text-muted" style="font-size:.8rem">Actions journalisées sur 14 jours</div>
            </div>
            <span class="badge"><?= array_sum($activite) ?> actions</span>
        </header>

        <div class="panel__body">
            <div class="chart" data-chart>
                <?php foreach ($activite as $jour => $total): ?>
                    <div class="chart__col">
                        <span class="chart__value"><?= (int) $total ?></span>
                        <div class="chart__bar" data-value="<?= (int) $total ?>"></div>
                        <span class="chart__label"><?= e(date('d/m', strtotime($jour))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Vue métier</div>
            <a class="btn btn--ghost btn--sm" href="/app">Console</a>
        </header>

        <div class="panel__body">
            <div class="kpi-line"><span>Affectations appliquées</span><b><?= (int) $dashboard['appliquees'] ?></b></div>
            <div class="kpi-line"><span>Affectations planifiées</span><b><?= (int) $dashboard['planifiees'] ?></b></div>
            <div class="kpi-line"><span>Affectations annulées</span><b><?= (int) $dashboard['annulees'] ?></b></div>
            <div class="kpi-line"><span>Mouvements ce mois</span><b><?= (int) $dashboard['mois_courant'] ?></b></div>

            <div style="margin-top:1.2rem">
                <div class="mono text-muted" style="font-size:.62rem;margin-bottom:.4rem">Taux d'application</div>
                <?php
                $totalAffectations = max((int) $dashboard['total'], 1);
                $tauxApplication = round((int) $dashboard['appliquees'] / $totalAffectations * 100);
                ?>
                <div class="meter"><i style="width:<?= $tauxApplication ?>%"></i></div>
                <div class="text-muted" style="font-size:.78rem;margin-top:.4rem"><?= $tauxApplication ?> % des dossiers aboutis</div>
            </div>
        </div>
    </section>
</div>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Derniers messages de contact</div>
            <a class="btn btn--ghost btn--sm" href="/admin/messages">Boîte de réception</a>
        </header>

        <?php if ($messages === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon"><?= icon('inbox', 20) ?></span>
                <h3>Aucun message</h3>
                <p>Les demandes envoyées via le formulaire public apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Expéditeur</th>
                            <th scope="col">Sujet</th>
                            <th scope="col">Reçu le</th>
                            <th scope="col">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td class="cell-strong"><?= e($message['name']) ?></td>
                                <td>
                                    <a href="/admin/messages/<?= (int) $message['id'] ?>"><?= e($message['subject']) ?></a>
                                </td>
                                <td><?= e(format_date((string) $message['created_at'], 'd/m/Y H:i')) ?></td>
                                <td>
                                    <span class="badge <?= $message['status'] === 'new' ? 'badge--accent' : '' ?>">
                                        <?= e(\App\Models\ContactMessage::STATUSES[$message['status']] ?? $message['status']) ?>
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
            <div class="panel__title">Journal d'audit</div>
        </header>

        <div class="panel__body">
            <div class="timeline-list">
                <?php foreach ($journal as $entry): ?>
                    <div class="timeline-list__item">
                        <span class="timeline-list__icon"><?= icon('activity', 15) ?></span>
                        <div>
                            <div class="timeline-list__text"><?= e((string) ($entry['description'] ?? $entry['action'])) ?></div>
                            <div class="timeline-list__meta">
                                <?= e((string) ($entry['user_name'] ?? 'Système')) ?> ·
                                <?= e(format_date((string) $entry['created_at'], 'd/m/Y H:i')) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<section class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title">Comptes autorisés</div>
            <div class="text-muted" style="font-size:.8rem">Derniers accès connus</div>
        </div>
        <a class="btn btn--outline btn--sm" href="/admin/utilisateurs">Gérer les utilisateurs</a>
    </header>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Fonction</th>
                    <th scope="col">Dernière connexion</th>
                    <th scope="col">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($derniers as $utilisateur): ?>
                    <tr>
                        <td>
                            <div class="person">
                                <span class="person__avatar" aria-hidden="true"><?= e(initials((string) $utilisateur['name'])) ?></span>
                                <div>
                                    <div class="person__name"><?= e((string) $utilisateur['name']) ?></div>
                                    <div class="person__meta"><?= e(mask_email((string) $utilisateur['email'])) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge <?= $utilisateur['role'] === 'admin' ? 'badge--accent' : '' ?>"><?= e((string) $utilisateur['role']) ?></span></td>
                        <td><?= e((string) ($utilisateur['job_title'] ?? '—')) ?></td>
                        <td><?= e($utilisateur['last_login_at'] ? format_date((string) $utilisateur['last_login_at'], 'd/m/Y H:i') : 'Jamais') ?></td>
                        <td>
                            <?php if ((int) $utilisateur['is_active'] === 1): ?>
                                <span class="badge badge--ok"><?= icon('check', 12) ?> Actif</span>
                            <?php else: ?>
                                <span class="badge"><?= icon('x', 12) ?> Désactivé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
