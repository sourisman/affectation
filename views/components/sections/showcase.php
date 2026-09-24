<?php

declare(strict_types=1);

/** Démonstration produit : onglets, panneaux flottants et parallaxe au survol. */

$panels = [
    [
        'id' => 'pilotage', 'label' => 'Pilotage',
        'title' => 'Toute la situation, sur un seul écran',
        'text' => 'Effectifs par site, mouvements en cours, taux de couverture et alertes : les indicateurs se recalculent à chaque saisie.',
        'points' => [
            'Indicateurs consolidés multi-sites et multi-provinces',
            'Tendance sur 12 mois et comparaison période à période',
            'Alertes automatiques sur les capacités dépassées',
        ],
    ],
    [
        'id' => 'affectations', 'label' => 'Affectations',
        'title' => 'Un acte, une référence, un historique',
        'text' => 'Chaque mouvement reçoit une référence unique (AFF-2026-0042), des dates contrôlées et un statut clair : planifié, appliqué ou annulé.',
        'points' => [
            'Numérotation automatique et contrôle des dates',
            'Application en un clic, transfert de l\'agent immédiat',
            'Annulation possible sans perdre la trace de la décision',
        ],
    ],
    [
        'id' => 'rapports', 'label' => 'Rapports',
        'title' => 'Des exports prêts pour le comité de direction',
        'text' => 'Couverture territoriale, flux entre provinces, motifs de mutation et synthèse imprimable : vos analyses se génèrent en quelques secondes.',
        'points' => [
            'Export CSV compatible tableur, sans mise en forme manuelle',
            'Rapport annuel imprimable au format document',
            'Détection immédiate des agents non affectés',
        ],
    ],
];
?>
<section class="section showcase" id="demonstration">
    <div class="container">
        <div class="section-head section-head--center">
            <span class="eyebrow eyebrow--boxed">Démonstration</span>
            <h2>L'interface pensées pour les opérations, pas pour les démos.</h2>
            <p>Trois écrans couvrent 90 % du quotidien d'une direction des ressources humaines.</p>
        </div>

        <div class="showcase__stage" data-tabs>
            <div class="showcase__tabs" role="tablist" aria-label="Modules de la plateforme">
                <?php foreach ($panels as $index => $panel): ?>
                    <button class="showcase__tab"
                            type="button"
                            role="tab"
                            id="tab-<?= e($panel['id']) ?>"
                            aria-controls="panel-<?= e($panel['id']) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                        <?= e($panel['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($panels as $index => $panel): ?>
                <div class="showcase__panel"
                     id="panel-<?= e($panel['id']) ?>"
                     role="tabpanel"
                     aria-labelledby="tab-<?= e($panel['id']) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <div class="showcase__grid">
                        <div class="showcase__copy">
                            <h3><?= e($panel['title']) ?></h3>
                            <p><?= e($panel['text']) ?></p>

                            <ul class="showcase__list">
                                <?php foreach ($panel['points'] as $point): ?>
                                    <li><?= icon('check', 17) ?> <?= e($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="stage" data-parallax="soft">
                            <div class="stage__card stage__card--main">
                                <div class="mock__chart-head">
                                    <span><?= e($panel['label']) ?></span>
                                    <span><?= e($panel['id']) === 'rapports' ? 'Exercice en cours' : 'Temps réel' ?></span>
                                </div>

                                <?php if ($panel['id'] === 'affectations'): ?>
                                    <div class="mock__rows" style="margin-top:.8rem">
                                        <div class="mock__row">
                                            <span>AFF-2026-0042</span><span>Toamasina → Antananarivo</span>
                                            <span class="badge badge--accent">Appliquée</span>
                                        </div>
                                        <div class="mock__row">
                                            <span>AFF-2026-0043</span><span>Antsirabe → Toliara</span>
                                            <span class="badge badge--warn">Planifiée</span>
                                        </div>
                                        <div class="mock__row">
                                            <span>AFF-2026-0044</span><span>Mahajanga → Antsirabe</span>
                                            <span class="badge badge--danger">Annulée</span>
                                        </div>
                                    </div>
                                <?php elseif ($panel['id'] === 'rapports'): ?>
                                    <div style="margin-top:.8rem">
                                        <div class="kpi-line"><span>Ratio de mobilité</span><b>67 %</b></div>
                                        <div class="kpi-line"><span>Agents par site</span><b>33,7</b></div>
                                        <div class="kpi-line"><span>Sites en tension</span><b>2</b></div>
                                        <div style="margin-top:1rem">
                                            <div class="meter"><i style="width:67%"></i></div>
                                        </div>
                                        <div class="cluster" style="margin-top:1.1rem">
                                            <span class="badge badge--accent"><?= icon('download', 13) ?> Export CSV</span>
                                            <span class="badge"><?= icon('printer', 13) ?> Rapport annuel</span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mock__bars" style="height:130px;margin-top:.8rem" aria-hidden="true">
                                        <span style="height:44%"></span>
                                        <span style="height:68%"></span>
                                        <span style="height:52%"></span>
                                        <span style="height:80%"></span>
                                        <span style="height:64%"></span>
                                        <span style="height:92%"></span>
                                        <span style="height:58%"></span>
                                        <span style="height:74%"></span>
                                        <span style="height:88%"></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="stage__card stage__card--float stage__card--a">
                                <div class="mono text-muted" style="font-size:.6rem">Couverture</div>
                                <strong style="font-family:var(--font-display);font-size:1.4rem">91 %</strong>
                                <div class="meter" style="margin-top:.6rem"><i style="width:91%"></i></div>
                            </div>

                            <div class="stage__card stage__card--float stage__card--b">
                                <div class="mono text-muted" style="font-size:.6rem">Dernière action</div>
                                <div style="font-size:.84rem;margin-top:.35rem">Affectation appliquée</div>
                                <div class="text-muted" style="font-size:.74rem">il y a 4 minutes · N. Rakoto</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
