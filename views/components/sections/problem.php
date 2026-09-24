<?php

declare(strict_types=1);

/** Narration : le problème posé, puis la réponse apportée par la plateforme. */

?>
<section class="section" id="probleme">
    <div class="container problem">
        <div data-reveal="left">
            <span class="eyebrow">Le constat</span>
            <h2 style="margin-top:1rem">Gérer les affectations sur tableur coûte cher.</h2>
            <p class="text-muted" style="margin-top:1.2rem;font-size:1.05rem">
                Les équipes RH passent plus de temps à vérifier des données qu'à accompagner les personnes.
                Le coût n'est pas visible immédiatement — il apparaît dans les délais, les erreurs et la perte de confiance.
            </p>

            <div class="cluster" style="margin-top:2rem">
                <a class="btn btn--outline" href="#demonstration">
                    Voir la solution
                    <?= icon('arrow-right', 16) ?>
                </a>
            </div>
        </div>

        <div class="problem__list" data-reveal="right">
            <article class="problem__item">
                <span class="idx">01</span>
                <div>
                    <h4>Données dispersées</h4>
                    <p>Effectifs sur tableur, décisions par courriel, historiques incomplets : personne ne dispose de la même version de la réalité.</p>
                </div>
            </article>

            <article class="problem__item">
                <span class="idx">02</span>
                <div>
                    <h4>Délais qui s'accumulent</h4>
                    <p>Une mutation validée met des semaines à se refléter dans la paie, avec des allers-retours de vérification.</p>
                </div>
            </article>

            <article class="problem__item">
                <span class="idx">03</span>
                <div>
                    <h4>Aucune traçabilité</h4>
                    <p>Impossible de reconstituer le parcours d'un agent ni d'identifier qui a validé quelle décision, et quand.</p>
                </div>
            </article>

            <article class="problem__item problem__item--ok">
                <span class="idx">→</span>
                <div>
                    <h4>Avec AFFECTA</h4>
                    <p>Une source unique, des règles appliquées automatiquement, un historique complet et des indicateurs fiables par site, par poste et par période.</p>
                </div>
            </article>
        </div>
    </div>
</section>
