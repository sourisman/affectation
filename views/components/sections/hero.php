<?php

declare(strict_types=1);

/** Hero : badge animé, titre premium, CTA, maquette flottante et halos. */

?>
<section class="hero" id="hero" data-hero>
    <div class="hero__decor" aria-hidden="true">
        <span class="glow glow--accent hero__glow-a"></span>
        <span class="glow glow--cool hero__glow-b"></span>
        <span class="bg-grid"></span>
    </div>

    <div class="container hero__inner">
        <div class="hero__copy">
            <div class="hero__badge" data-hero-item>
                <span class="badge">Nouveau</span>
                <span>Console 2026 — pilotage temps réel des effectifs</span>
                <span class="dot dot--pulse" aria-hidden="true"></span>
            </div>

            <h1 class="hero__title" data-hero-item>
                <span class="line"><span>Construisons le</span></span>
                <span class="line"><span>futur du <em>digital</em>.</span></span>
            </h1>

            <p class="hero__lead" data-hero-item>
                AFFECTA centralise les lieux, les agents et leurs affectations dans une plateforme
                unique : décisions documentées, effectifs à jour, historique auditable.
                Déployée en trois semaines, adoptée dès le premier jour.
            </p>

            <div class="hero__actions" data-hero-item>
                <a class="btn btn--primary btn--lg btn--magnetic" href="#contact">
                    Commencer maintenant
                    <?= icon('arrow-right', 18) ?>
                </a>
                <a class="btn btn--outline btn--lg" href="#demonstration">
                    <?= icon('layers', 18) ?>
                    Découvrir la plateforme
                </a>
            </div>

            <div class="hero__meta" data-hero-item>
                <div class="hero__proof">
                    <div class="avatar-stack" aria-hidden="true">
                        <span>NR</span><span>HA</span><span>MR</span><span>DR</span>
                    </div>
                    <span>4,9/5 — 120 projets livrés</span>
                </div>
                <span class="hero__meta-item"><?= icon('shield', 16) ?> Données hébergées et chiffrées</span>
                <span class="hero__meta-item"><?= icon('bolt', 16) ?> Mise en service en 3 semaines</span>
            </div>
        </div>

        <!-- Maquette de la console : élément visuel central -->
        <div class="hero__visual" data-hero-item data-parallax="hero" data-tilt>
            <div class="mock">
                <div class="mock__bar">
                    <div class="mock__dots" aria-hidden="true"><i></i><i></i><i></i></div>
                    <div class="mock__url">affecta.app/app/tableau-de-bord</div>
                </div>

                <div class="mock__body">
                    <div class="mock__kpis">
                        <div class="mock__kpi">
                            <small>Agents actifs</small>
                            <strong>1 248</strong>
                            <em>+4,2 % ce mois</em>
                        </div>
                        <div class="mock__kpi">
                            <small>Lieux</small>
                            <strong>37</strong>
                            <em>6 provinces</em>
                        </div>
                        <div class="mock__kpi">
                            <small>Mouvements</small>
                            <strong>28</strong>
                            <em>11 en attente</em>
                        </div>
                    </div>

                    <div class="mock__chart">
                        <div class="mock__chart-head">
                            <span>Volume mensuel</span>
                            <span>12 mois</span>
                        </div>
                        <div class="mock__bars" aria-hidden="true">
                            <span style="height:38%"></span>
                            <span style="height:56%"></span>
                            <span style="height:44%"></span>
                            <span style="height:72%"></span>
                            <span style="height:61%"></span>
                            <span style="height:84%"></span>
                            <span style="height:52%"></span>
                            <span style="height:96%"></span>
                        </div>
                    </div>

                    <div class="mock__rows" aria-hidden="true">
                        <div class="mock__row">
                            <span>AFF-2026-0042 · Rakoto A.</span>
                            <span>Toamasina → Antananarivo</span>
                            <span class="badge badge--accent">Appliquée</span>
                        </div>
                        <div class="mock__row">
                            <span>AFF-2026-0043 · Rasoa M.</span>
                            <span>Antsirabe → Toliara</span>
                            <span class="badge badge--warn">Planifiée</span>
                        </div>
                        <div class="mock__row">
                            <span>AFF-2026-0044 · Andry T.</span>
                            <span>Mahajanga → Fianarantsoa</span>
                            <span class="badge">En validation</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="float-chip float-chip--tl">
                <?= icon('check-circle', 18) ?>
                <div>
                    <strong>-62 %</strong>
                    <div class="text-muted" style="font-size:.72rem">de délai de traitement</div>
                </div>
            </div>

            <div class="float-chip float-chip--br">
                <?= icon('shield', 18) ?>
                <div>
                    <strong>99,9 %</strong>
                    <div class="text-muted" style="font-size:.72rem">disponibilité mesurée</div>
                </div>
            </div>

            <div class="float-chip float-chip--bl">
                <?= icon('history', 18) ?>
                <div>
                    <strong>Audit</strong>
                    <div class="text-muted" style="font-size:.72rem">chaque action tracée</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="trust" aria-label="Ils nous font confiance">
    <div class="container trust__inner">
        <span class="trust__label">Ils pilotent leurs effectifs avec AFFECTA</span>
        <div class="trust__logos">
            <span>Groupe Horizon</span>
            <span>Transport Océan Indien</span>
            <span>Banque Centrale du Sud</span>
            <span>Agro Sava</span>
            <span>Énergie Ouest</span>
        </div>
    </div>
</section>
