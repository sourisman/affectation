<?php

declare(strict_types=1);

/**
 * Historique chronologique et journal d'audit.
 *
 * @var array<string, list<array<string, mixed>>> $grouped
 * @var list<array<string, mixed>> $log
 * @var list<array<string, mixed>> $lieux
 * @var array<string, string> $statuts
 * @var array<string, mixed> $filters
 * @var array<string, mixed> $pagination
 */

$mois = [
    '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril',
    '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août',
    '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre',
];
?>
<form class="filters" method="get" action="/app/historique" role="search">
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
        <label class="field__label" for="lieu">Lieu</label>
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
        <a class="btn btn--ghost btn--sm" href="/app/historique">Réinitialiser</a>
    </div>
</form>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Chronologie des mouvements</div>
                <div class="text-muted" style="font-size:.8rem">
                    <?= (int) $pagination['total'] ?> affectation(s) correspondant aux critères
                </div>
            </div>
        </header>

        <div class="panel__body">
            <?php if ($grouped === []): ?>
                <div class="empty-state">
                    <span class="empty-state__icon"><?= icon('history', 20) ?></span>
                    <h3>Aucun mouvement sur la période</h3>
                    <p>Modifiez les filtres pour élargir la recherche.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($grouped as $periode => $items): ?>
                <div style="margin-bottom:1.8rem">
                    <div class="mono text-muted" style="font-size:.66rem;margin-bottom:.6rem">
                        <?= e(($mois[substr($periode, 5, 2)] ?? '') . ' ' . substr($periode, 0, 4)) ?>
                        · <?= count($items) ?> mouvement(s)
                    </div>

                    <div class="timeline-list">
                        <?php foreach ($items as $item): ?>
                            <div class="timeline-list__item <?= $item['statut'] === 'planifie' ? 'timeline-list__item--accent' : '' ?>">
                                <span class="timeline-list__icon"><?= icon('route', 15) ?></span>
                                <div>
                                    <div class="timeline-list__text">
                                        <strong><?= e($item['prenom'] . ' ' . $item['nom']) ?></strong>
                                        — <?= e($item['ancien_design']) ?> → <?= e($item['nouveau_design']) ?>
                                    </div>
                                    <div class="timeline-list__meta">
                                        <span class="mono"><?= e($item['numAffect']) ?></span> ·
                                        <?= e(format_date((string) $item['dateAffect'])) ?> ·
                                        <?= e(\App\Models\Affectation::MOTIFS[$item['motif']] ?? 'motif non précisé') ?> ·
                                        <?= e(\App\Models\Affectation::STATUTS[$item['statut']] ?? $item['statut']) ?>
                                        · <a class="text-accent" href="/app/affectations/<?= (int) $item['id'] ?>">détail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php require BASE_PATH . '/views/components/pagination.php'; ?>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div>
                <div class="panel__title">Journal des actions</div>
                <div class="text-muted" style="font-size:.8rem">Traçabilité des opérations sensibles</div>
            </div>
        </header>

        <div class="panel__body">
            <div class="timeline-list">
                <?php foreach ($log as $entry): ?>
                    <div class="timeline-list__item">
                        <span class="timeline-list__icon">
                            <?= icon(match (true) {
                                str_starts_with((string) $entry['action'], 'auth') => 'key',
                                str_starts_with((string) $entry['action'], 'employe') => 'user',
                                str_starts_with((string) $entry['action'], 'lieu') => 'building',
                                str_starts_with((string) $entry['action'], 'user') => 'user-plus',
                                default => 'route',
                            }, 15) ?>
                        </span>
                        <div>
                            <div class="timeline-list__text">
                                <?= e((string) ($entry['description'] ?? $entry['action'])) ?>
                            </div>
                            <div class="timeline-list__meta">
                                <?= e((string) ($entry['user_name'] ?? 'Système')) ?> ·
                                <?= e(format_date((string) $entry['created_at'], 'd/m/Y H:i')) ?> ·
                                <span class="mono"><?= e((string) $entry['action']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
