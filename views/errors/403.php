<?php

declare(strict_types=1);

/** Page 403 — accès refusé (droits insuffisants). */
?>
<section class="section" style="min-height:72vh;display:grid;place-items:center">
    <div class="container" style="text-align:center">
        <div class="error-page__code">403</div>

        <h1 style="font-size:clamp(1.6rem,3.4vw,2.4rem)">Accès restreint.</h1>
        <p class="text-muted" style="margin:1.1rem auto 0;max-width:54ch">
            Cette section est réservée aux comptes disposant des privilèges d'administration.
            Si vous pensez qu'il s'agit d'une erreur, contactez votre référent AFFECTA.
        </p>

        <div class="cluster" style="justify-content:center;margin-top:2rem">
            <a class="btn btn--primary" href="/app"><?= icon('grid', 17) ?> Revenir à la console</a>
            <a class="btn btn--outline" href="/#contact"><?= icon('mail', 17) ?> Demander un accès</a>
        </div>
    </div>
</section>
