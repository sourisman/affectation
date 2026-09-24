<?php

declare(strict_types=1);

/** Page 404 — rendue avec le layout public. */
?>
<section class="section" style="min-height:72vh;display:grid;place-items:center">
    <div class="container" style="text-align:center">
        <div class="error-page__code">404</div>

        <h1 style="font-size:clamp(1.6rem,3.4vw,2.4rem)">Cette page n'existe pas (ou plus).</h1>
        <p class="text-muted" style="margin:1.1rem auto 0;max-width:52ch">
            Le lien est peut-être obsolète, ou l'adresse comporte une erreur de frappe.
            Vous pouvez revenir à l'accueil ou explorer les fonctionnalités de la plateforme.
        </p>

        <div class="cluster" style="justify-content:center;margin-top:2rem">
            <a class="btn btn--primary btn--magnetic" href="/">
                <?= icon('home', 17) ?>
                Retour à l'accueil
            </a>
            <a class="btn btn--outline" href="/#contact">
                <?= icon('mail', 17) ?>
                Nous signaler un problème
            </a>
        </div>
    </div>
</section>
