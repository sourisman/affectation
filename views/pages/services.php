<?php

declare(strict_types=1);

/**
 * Page « Services » — réutilise la section interactive et la méthode.
 *
 * @var list<array<string, mixed>> $services
 * @var list<array<string,string>> $process
 */
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Services</span>
        <h1 style="margin-top:1.1rem;font-size:clamp(2.2rem,5vw,3.6rem)">
            Nous ne livrons pas seulement un logiciel.
        </h1>
        <p class="text-muted" style="margin-top:1.4rem;font-size:1.08rem;max-width:64ch">
            Nous prenons en charge l'intégralité du parcours : comprendre vos contraintes,
            reprendre vos données, former vos équipes et garantir la disponibilité du service.
        </p>
    </div>
</section>

<?php require BASE_PATH . '/views/components/sections/services.php'; ?>
<?php require BASE_PATH . '/views/components/sections/process.php'; ?>

<section class="section">
    <div class="container">
        <div class="cta-final" data-reveal="scale">
            <h2>Un besoin précis ? Décrivez-le nous.</h2>
            <p>Nous vous répondons avec une estimation de charge, un calendrier réaliste et les prérequis techniques.</p>
            <div class="cluster">
                <a class="btn btn--primary btn--lg" href="/#contact">Demander une estimation</a>
                <a class="btn btn--outline btn--lg" href="/#tarifs">Consulter les tarifs</a>
            </div>
        </div>
    </div>
</section>
