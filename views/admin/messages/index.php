<?php

declare(strict_types=1);

/**
 * Boîte de réception des messages de contact.
 *
 * @var list<array<string, mixed>> $messages
 * @var array<string, string> $statuses
 * @var string $status
 * @var string $term
 * @var array<string, mixed> $pagination
 */

$filters = ['statut' => $status, 'q' => $term];
?>
<form class="filters" method="get" action="/admin/messages" role="search">
    <div class="field">
        <label class="field__label" for="q">Recherche</label>
        <input class="input" type="search" id="q" name="q" value="<?= e($term) ?>"
               placeholder="Nom, email, sujet, contenu…">
    </div>

    <div class="field field--sm">
        <label class="field__label" for="statut">Statut</label>
        <select class="select" id="statut" name="statut" data-auto-submit>
            <option value="">Tous les statuts</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filters__actions">
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('search', 15) ?> Filtrer</button>
        <a class="btn btn--ghost btn--sm" href="/admin/messages">Réinitialiser</a>
    </div>
</form>

<?php if ($messages === []): ?>
    <div class="panel">
        <div class="empty-state">
            <span class="empty-state__icon"><?= icon('inbox', 22) ?></span>
            <h3>Aucun message dans cette vue</h3>
            <p>Les demandes envoyées depuis le site public apparaissent ici automatiquement.</p>
        </div>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Expéditeur</th>
                    <th scope="col">Sujet</th>
                    <th scope="col">Reçu le</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="cell-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td>
                            <div class="person">
                                <span class="person__avatar person__avatar--cool" aria-hidden="true">
                                    <?= e(initials((string) $message['name'])) ?>
                                </span>
                                <div style="min-width:0">
                                    <div class="person__name"><?= e((string) $message['name']) ?></div>
                                    <div class="person__meta"><?= e((string) $message['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a class="cell-strong" href="/admin/messages/<?= (int) $message['id'] ?>">
                                <?= e((string) $message['subject']) ?>
                            </a>
                            <div class="person__meta" style="max-width:46ch">
                                <?= e(mb_strimwidth((string) $message['message'], 0, 90, '…')) ?>
                            </div>
                        </td>
                        <td><?= e(format_date((string) $message['created_at'], 'd/m/Y H:i')) ?></td>
                        <td>
                            <span class="badge <?= match ($message['status']) {
                                'new' => 'badge--accent',
                                'read' => 'badge--info',
                                'answered' => 'badge--ok',
                                default => '',
                            } ?>">
                                <?= e($statuses[$message['status']] ?? $message['status']) ?>
                            </span>
                        </td>
                        <td class="cell-actions">
                            <span class="row-actions">
                                <a class="icon-btn" href="/admin/messages/<?= (int) $message['id'] ?>" aria-label="Ouvrir le message">
                                    <?= icon('eye', 15) ?>
                                </a>
                                <a class="icon-btn" href="mailto:<?= e((string) $message['email']) ?>?subject=<?= e(rawurlencode('Re: ' . (string) $message['subject'])) ?>"
                                   aria-label="Répondre par email">
                                    <?= icon('mail', 15) ?>
                                </a>
                                <form action="/admin/messages/<?= (int) $message['id'] ?>" method="post" data-ajax
                                      data-confirm="Supprimer définitivement ce message ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="icon-btn icon-btn--danger" type="submit" aria-label="Supprimer le message">
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
