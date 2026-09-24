<?php

declare(strict_types=1);

/**
 * Rapport annuel imprimable.
 *
 * @var int $year
 * @var list<array<string, mixed>> $couverture
 * @var array<string, int> $mensuel
 * @var array<string, int|float> $synthese
 * @var list<array<string, mixed>> $affectations
 */

$moisCourts = ['01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril', '05' => 'Mai', '06' => 'Juin',
    '07' => 'Juillet', '08' => 'Août', '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'];
?>
<h1>Rapport d'affectation — exercice <?= (int) $year ?></h1>
<p style="font-size:.86rem;color:#5b6473;margin-top:.4rem">
    Synthèse des mouvements de personnel et de la couverture des sites.
</p>

<h2>1. Synthèse générale</h2>
<table>
    <tbody>
        <tr><th scope="row">Agents suivis</th><td><?= (int) $synthese['employes'] ?></td></tr>
        <tr><th scope="row">Sites d'affectation</th><td><?= (int) $synthese['lieux'] ?></td></tr>
        <tr><th scope="row">Affectations enregistrées</th><td><?= (int) $synthese['affectations'] ?></td></tr>
        <tr><th scope="row">Affectations appliquées</th><td><?= (int) $synthese['appliquees'] ?></td></tr>
        <tr><th scope="row">Ratio de mobilité</th><td><?= (string) $synthese['ratio_mobilite'] ?> %</td></tr>
        <tr><th scope="row">Moyenne d'agents par site</th><td><?= (string) $synthese['moyenne_agents'] ?></td></tr>
    </tbody>
</table>

<h2>2. Mouvements mensuels <?= (int) $year ?></h2>
<table>
    <thead>
        <tr><th scope="col">Mois</th><th scope="col">Mouvements</th></tr>
    </thead>
    <tbody>
        <?php foreach ($mensuel as $mois => $total): ?>
            <tr>
                <td><?= e($moisCourts[$mois] ?? $mois) ?></td>
                <td><?= (int) $total ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th scope="row">Total</th>
            <td><strong><?= array_sum($mensuel) ?></strong></td>
        </tr>
    </tbody>
</table>

<h2>3. Couverture des sites</h2>
<table>
    <thead>
        <tr><th scope="col">Site</th><th scope="col">Province</th><th scope="col">Effectif</th></tr>
    </thead>
    <tbody>
        <?php foreach ($couverture as $row): ?>
            <tr>
                <td><?= e($row['design']) ?></td>
                <td><?= e($row['province']) ?></td>
                <td><?= (int) $row['effectif'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>4. Détail des affectations <?= (int) $year ?></h2>
<table>
    <thead>
        <tr>
            <th scope="col">Référence</th>
            <th scope="col">Agent</th>
            <th scope="col">Origine</th>
            <th scope="col">Accueil</th>
            <th scope="col">Date</th>
            <th scope="col">Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($affectations as $affectation): ?>
            <tr>
                <td class="mono"><?= e($affectation['numAffect']) ?></td>
                <td><?= e($affectation['prenom'] . ' ' . $affectation['nom']) ?></td>
                <td><?= e((string) $affectation['ancien_design']) ?></td>
                <td><?= e((string) $affectation['nouveau_design']) ?></td>
                <td><?= e(format_date((string) $affectation['dateAffect'])) ?></td>
                <td><?= e(\App\Models\Affectation::STATUTS[$affectation['statut']] ?? $affectation['statut']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p style="margin-top:2rem;font-size:.82rem;color:#5b6473">
    Visa de la direction des ressources humaines : ____________________________
</p>
