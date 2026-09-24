<?php

declare(strict_types=1);

/**
 * Rapports : couverture territoriale, tendance annuelle, flux et motifs.
 *
 * @var int $year
 * @var list<int> $annees
 * @var list<array<string, mixed>> $couverture
 * @var array<string, int> $mensuel
 * @var list<array<string, mixed>> $flux
 * @var list<array<string, mixed>> $parMotif
 * @var list<array<string, mixed>> $nonAffectes
 * @var array<string, int|float> $synthese
 */

$maxCouverture = max(array_map(static fn (array $row): int => (int) $row['effectif'], $couverture) ?: [1]);
$maxMotif = max(array_map(static fn (array $row): int => (int) $row['total'], $parMotif) ?: [1]);
$moisCourts = ['01' => 'Jan', '02' => 'Fév', '03' => 'Mar', '04' => 'Avr', '05' => 'Mai', '06' => 'Juin',
    '07' => 'Juil', '08' => 'Août', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Déc'];
?>
<div class="page-head__actions">
    <form method="get" action="/app/rapports" style="display:flex;gap:.5rem;align-items:center">
        <select class="select" name="annee" data-auto-submit aria-label="Année du rapport">
            <?php foreach ($annees as $option): ?>
                <option value="<?= (int) $option ?>" <?= $option === $year ? 'selected' : '' ?>><?= (int) $option ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <a class="btn btn--outline btn--sm" href="/app/rapports/impression?annee=<?= (int) $year ?>" target="_blank" rel="noopener">
        <?= icon('printer', 15) ?>
        Rapport imprimable
    </a>

    <a class="btn btn--primary btn--sm" href="/app/affectations/export">
        <?= icon('download', 15) ?>
        Export CSV
    </a>
</div>

<div class="kpis">
    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Agents suivis</span>
            <span class="kpi__icon"><?= icon('users', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= (int) $synthese['employes'] ?>">0</div>
        <div class="kpi__foot"><span>sur <?= (int) $synthese['lieux'] ?> sites</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Ratio de mobilité</span>
            <span class="kpi__icon"><?= icon('shuffle', 16) ?></span>
        </div>
        <div class="kpi__value">
            <span data-counter="<?= (string) $synthese['ratio_mobilite'] ?>" data-counter-decimals="1">0</span> %
        </div>
        <div class="kpi__foot"><span>affectations / agents</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Moyenne par site</span>
            <span class="kpi__icon"><?= icon('building', 16) ?></span>
        </div>
        <div class="kpi__value">
            <span data-counter="<?= (string) $synthese['moyenne_agents'] ?>" data-counter-decimals="1">0</span>
        </div>
        <div class="kpi__foot"><span>agents par lieu</span></div>
    </article>

    <article class="kpi">
        <div class="kpi__head">
            <span class="kpi__label">Agents non affectés</span>
            <span class="kpi__icon"><?= icon('alert', 16) ?></span>
        </div>
        <div class="kpi__value" data-counter="<?= count($nonAffectes) ?>">0</div>
        <div class="kpi__foot">
            <a class="text-accent" href="/app/rapports/non-affectes">Voir la liste <?= icon('arrow-right', 13) ?></a>
        </div>
    </article>
</div>

<section class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title">Mouvements mensuels <?= (int) $year ?></div>
            <div class="text-muted" style="font-size:.8rem">Toutes affectations confondues</div>
        </div>
        <span class="badge"><?= array_sum($mensuel) ?> mouvements</span>
    </header>

    <div class="panel__body">
        <div class="chart" data-chart>
            <?php foreach ($mensuel as $mois => $total): ?>
                <div class="chart__col">
                    <span class="chart__value"><?= (int) $total ?></span>
                    <div class="chart__bar <?= (int) $mois % 2 === 0 ? 'chart__bar--alt' : '' ?>" data-value="<?= (int) $total ?>"></div>
                    <span class="chart__label"><?= e($moisCourts[$mois] ?? $mois) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="split">
    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Couverture territoriale</div>
        </header>

        <div class="panel__body">
            <div class="coverage">
                <?php foreach ($couverture as $row): ?>
                    <div class="coverage__row">
                        <span class="coverage__name">
                            <?= e($row['design']) ?>
                            <span class="text-muted" style="font-size:.78rem">· <?= e($row['province']) ?></span>
                        </span>
                        <span class="coverage__value"><?= (int) $row['effectif'] ?></span>
                        <span class="coverage__bar">
                            <i style="width:<?= round((int) $row['effectif'] / max($maxCouverture, 1) * 100) ?>%"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <div class="panel__title">Motifs de mutation</div>
        </header>

        <div class="panel__body">
            <div class="coverage">
                <?php foreach ($parMotif as $row): ?>
                    <div class="coverage__row">
                        <span class="coverage__name"><?= e($row['motif']) ?></span>
                        <span class="coverage__value"><?= (int) $row['total'] ?></span>
                        <span class="coverage__bar">
                            <i style="width:<?= round((int) $row['total'] / max($maxMotif, 1) * 100) ?>%"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<section class="panel">
    <header class="panel__head">
        <div>
            <div class="panel__title">Flux entre provinces</div>
            <div class="text-muted" style="font-size:.8rem">Origine → destination, affectations actives</div>
        </div>
    </header>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Province d'origine</th>
                    <th scope="col">Province d'accueil</th>
                    <th scope="col">Mouvements</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flux as $flow): ?>
                    <tr>
                        <td class="cell-strong"><?= e((string) $flow['origine']) ?></td>
                        <td><?= e((string) $flow['destination']) ?></td>
                        <td><span class="badge badge--accent"><?= (int) $flow['total'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
