<?php

declare(strict_types=1);

/**
 * Tarifs : trois plans, bascule mensuel/annuel animée et comparatif détaillé.
 *
 * @var list<array<string, mixed>> $plans
 */

$rows = [
    ['Agents inclus', '100', '2 000', 'Illimités'],
    ['Lieux illimités', true, true, true],
    ['Historique des affectations', true, true, true],
    ['Tableau de bord consolidé', false, true, true],
    ['Workflow de validation', false, true, true],
    ['Export CSV', true, true, true],
    ['Export PDF / impression', false, true, true],
    ['Authentification renforcée', false, true, true],
    ['API et modules sur mesure', false, false, true],
    ['SLA contractuel', false, false, true],
    ['Accompagnement dédié', false, false, true],
];
?>
<section class="section" id="tarifs">
    <div class="container">
        <div class="section-head section-head--center">
            <span class="eyebrow eyebrow--boxed">Tarifs</span>
            <h2>Un prix clair, sans coût caché.</h2>
            <p>Hébergement, mises à jour de sécurité et support inclus dans tous les plans.</p>
        </div>

        <div class="billing-toggle">
            <div class="switch" data-billing-toggle role="group" aria-label="Périodicité de facturation">
                <span class="switch__thumb" aria-hidden="true"></span>
                <button class="switch__option" type="button" data-billing="monthly" aria-pressed="true">Mensuel</button>
                <button class="switch__option" type="button" data-billing="yearly" aria-pressed="false">Annuel</button>
            </div>
            <span class="billing-toggle__save"><?= icon('sparkles', 14) ?> Jusqu'à 25 % d'économie</span>
        </div>

        <div class="plans">
            <?php foreach ($plans as $plan): ?>
                <article class="plan <?= $plan['featured'] ? 'plan--featured' : '' ?>" data-reveal="scale">
                    <?php if ($plan['featured']): ?>
                        <span class="plan__ribbon">Le plus choisi</span>
                    <?php endif; ?>

                    <span class="plan__name"><?= e($plan['name']) ?></span>

                    <div class="plan__price">
                        <span class="amount" data-price-monthly="<?= (int) $plan['monthly'] ?>"
                              data-price-yearly="<?= (int) $plan['yearly'] ?>"><?= number_format((int) $plan['monthly'], 0, ',', ' ') ?></span>
                        <span class="currency">Ar</span>
                        <span class="period" data-billing-period>/mois</span>
                    </div>

                    <p class="plan__desc"><?= e($plan['desc']) ?></p>

                    <ul class="plan__features">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li class="<?= $feature['included'] ? '' : 'is-off' ?>">
                                <?= icon($feature['included'] ? 'check' : 'x', 17) ?>
                                <span><?= e($feature['label']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="plan__cta">
                        <a class="btn <?= $plan['featured'] ? 'btn--primary' : 'btn--outline' ?> btn--block btn--magnetic"
                           href="#contact">
                            <?= e($plan['cta']) ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="text-muted" style="text-align:center;margin-top:1.4rem;font-size:.86rem"
           data-billing-note
           data-billing-note-yearly="Facturation annuelle — 2 mois offerts par rapport au mensuel"
           data-billing-note="">Tarifs hors taxes, par établissement. Devis personnalisé au-delà de 5 000 agents.</p>

        <details class="compare">
            <summary>
                Comparer les fonctionnalités en détail
                <?= icon('plus', 18) ?>
            </summary>

            <div class="compare__body">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Fonctionnalité</th>
                            <th scope="col">Starter</th>
                            <th scope="col">Pro</th>
                            <th scope="col">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= e($row[0]) ?></td>
                                <?php for ($column = 1; $column <= 3; $column++): ?>
                                    <td>
                                        <?php if ($row[$column] === true): ?>
                                            <span class="yes"><?= icon('check', 16) ?><span class="sr-only">Inclus</span></span>
                                        <?php elseif ($row[$column] === false): ?>
                                            <span class="no"><?= icon('minus', 16) ?><span class="sr-only">Non inclus</span></span>
                                        <?php else: ?>
                                            <?= e((string) $row[$column]) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</section>
