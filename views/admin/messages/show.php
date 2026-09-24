<?php

declare(strict_types=1);

/**
 * Détail d'un message de contact.
 *
 * @var array<string, mixed> $message
 * @var array<string, string> $statuses
 */
?>
<div class="page-head__actions">
    <a class="btn btn--outline btn--sm" href="/admin/messages"><?= icon('arrow-left', 15) ?> Boîte de réception</a>

    <a class="btn btn--primary btn--sm"
       href="mailto:<?= e((string) $message['email']) ?>?subject=<?= e(rawurlencode('Re: ' . (string) $message['subject'])) ?>">
        <?= icon('mail', 15) ?> Répondre
    </a>
</div>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title"><?= e((string) $message['subject']) ?></div>
                <div class="text-muted" style="font-size:.8rem">
                    Reçu le <?= e(format_date((string) $message['created_at'], 'd/m/Y à H:i')) ?>
                </div>
            </div>
            <span class="badge <?= $message['status'] === 'new' ? 'badge--accent' : '' ?>">
                <?= e($statuses[$message['status']] ?? $message['status']) ?>
            </span>
        </header>

        <div class="panel__body">
            <div class="person" style="gap:.9rem">
                <span class="person__avatar" style="width:46px;height:46px" aria-hidden="true">
                    <?= e(initials((string) $message['name'])) ?>
                </span>
                <div>
                    <div class="person__name"><?= e((string) $message['name']) ?></div>
                    <div class="person__meta">
                        <a href="mailto:<?= e((string) $message['email']) ?>"><?= e((string) $message['email']) ?></a>
                        <?php if (!empty($message['phone'])): ?>
                            · <a href="tel:<?= e((string) $message['phone']) ?>"><?= e((string) $message['phone']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="margin-top:1.6rem;padding:1.2rem;border:1px solid var(--line);border-radius:var(--radius-md);background:var(--bg-subtle)">
                <p style="white-space:pre-wrap;margin:0;color:var(--text-soft);font-size:.94rem"><?= e((string) $message['message']) ?></p>
            </div>
        </div>
    </section>

    <div class="stack">
        <section class="panel">
            <header class="panel__head">
                <div class="panel__title">Traitement</div>
            </header>

            <div class="panel__body">
                <form data-ajax action="/admin/messages/<?= (int) $message['id'] ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="field">
                        <label class="field__label" for="status">Statut du message</label>
                        <select class="select" id="status" name="status" data-auto-submit>
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $message['status'] === $key ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-foot">
                        <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', 15) ?> Mettre à jour</button>
                    </div>
                </form>

                <form action="/admin/messages/<?= (int) $message['id'] ?>" method="post" data-ajax
                      data-confirm="Supprimer définitivement ce message ?" style="margin-top:1rem">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn--danger btn--sm btn--block" type="submit">
                        <?= icon('trash', 15) ?> Supprimer le message
                    </button>
                </form>
            </div>
        </section>

        <section class="panel">
            <header class="panel__head">
                <div class="panel__title">Métadonnées</div>
            </header>

            <div class="panel__body">
                <div class="kv">
                    <div class="kv__row">
                        <span class="kv__key">Identifiant</span>
                        <span class="kv__value mono">#<?= (int) $message['id'] ?></span>
                    </div>
                    <div class="kv__row">
                        <span class="kv__key">Adresse IP</span>
                        <span class="kv__value mono"><?= e((string) ($message['ip'] ?? '—')) ?></span>
                    </div>
                    <div class="kv__row">
                        <span class="kv__key">Agent</span>
                        <span class="kv__value" style="word-break:break-word;font-size:.82rem">
                            <?= e((string) ($message['user_agent'] ?? '—')) ?>
                        </span>
                    </div>
                    <div class="kv__row">
                        <span class="kv__key">Lu le</span>
                        <span class="kv__value"><?= e($message['read_at'] ? format_date((string) $message['read_at'], 'd/m/Y H:i') : 'Non lu') ?></span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
