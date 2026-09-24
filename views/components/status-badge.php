<?php

declare(strict_types=1);

/**
 * Pastille de statut d'une affectation.
 *
 * @var string $statut
 */

$map = [
    'planifie' => ['class' => 'badge--warn', 'icon' => 'calendar', 'label' => 'Planifiée'],
    'applique' => ['class' => 'badge--ok', 'icon' => 'check-circle', 'label' => 'Appliquée'],
    'annule'   => ['class' => 'badge--danger', 'icon' => 'x-circle', 'label' => 'Annulée'],
];

$meta = $map[$statut] ?? ['class' => '', 'icon' => 'info', 'label' => ucfirst($statut)];
?>
<span class="badge <?= e($meta['class']) ?>">
    <?= icon($meta['icon'], 13) ?>
    <?= e($meta['label']) ?>
</span>
