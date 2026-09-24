<?php

declare(strict_types=1);

/**
 * Page « À propos ».
 *
 * @var list<array<string, mixed>> $stats
 */
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">À propos</span>
        <h1 style="margin-top:1.1rem;font-size:clamp(2.2rem,5vw,3.6rem)">
            Nous construisons les outils que les équipes RH attendaient.
        </h1>
        <p class="text-muted" style="margin-top:1.4rem;font-size:1.08rem;max-width:64ch">
            AFFECTA est né d'un constat simple : la gestion des affectations reste l'un des
            derniers processus critiques encore piloté sur tableur dans de nombreuses organisations.
            Nous avons décidé d'y remédier avec une exigence de qualité logicielle et d'expérience utilisateur.
        </p>

        <div class="cluster" style="margin-top:2rem">
            <a class="btn btn--primary" href="/#contact">Travailler avec nous <?= icon('arrow-right', 16) ?></a>
            <a class="btn btn--outline" href="/services">Découvrir nos services</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats">
            <?php foreach ($stats as $stat): ?>
                <div class="stat" data-reveal="scale">
                    <div class="stat__value">
                        <span data-counter="<?= e((string) $stat['value']) ?>"
                              data-counter-decimals="<?= (int) $stat['decimals'] ?>">0</span><span class="suffix"><?= e($stat['suffix']) ?></span>
                    </div>
                    <div class="stat__label"><?= e($stat['label']) ?></div>
                    <div class="stat__meta"><?= e($stat['meta']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid grid-3">
        <article class="card card--interactive" data-reveal>
            <?= icon('target', 22, 'text-accent') ?>
            <h3 style="font-size:1.2rem;margin-top:1rem">Notre mission</h3>
            <p class="text-muted" style="margin-top:.6rem;font-size:.94rem">
                Donner aux organisations la capacité de décider vite et juste sur leurs effectifs,
                sans jamais perdre la trace de leurs décisions.
            </p>
        </article>

        <article class="card card--interactive" data-reveal data-reveal-delay="90">
            <?= icon('shield', 22, 'text-accent') ?>
            <h3 style="font-size:1.2rem;margin-top:1rem">Nos principes</h3>
            <p class="text-muted" style="margin-top:.6rem;font-size:.94rem">
                Sécurité par défaut, données propriété du client, performance mesurée, accessibilité
                prise au sérieux et code maintenable sur le long terme.
            </p>
        </article>

        <article class="card card--interactive" data-reveal data-reveal-delay="180">
            <?= icon('users', 22, 'text-accent') ?>
            <h3 style="font-size:1.2rem;margin-top:1rem">Notre équipe</h3>
            <p class="text-muted" style="margin-top:.6rem;font-size:.94rem">
                Une équipe resserrée d'ingénieurs, de designers et de spécialistes des processus RH,
                basée à Antananarivo, disponible sur trois continents.
            </p>
        </article>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-final" data-reveal="scale">
            <span class="eyebrow eyebrow--boxed" style="margin-inline:auto">Prendre contact</span>
            <h2 style="margin-top:1.2rem">Discutons de votre organisation.</h2>
            <p>Un échange de 30 minutes suffit pour qualifier votre besoin et vous proposer une trajectoire réaliste.</p>
            <div class="cluster">
                <a class="btn btn--primary btn--lg" href="/#contact">Demander un échange</a>
                <a class="btn btn--outline btn--lg" href="/realisations">Voir des références</a>
            </div>
        </div>
    </div>
</section>
