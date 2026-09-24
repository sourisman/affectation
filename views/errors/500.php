<?php

declare(strict_types=1);

/**
 * Page 500 — le détail technique n'est affiché qu'en environnement de développement.
 *
 * @var string|null $detail
 */
?>
<section class="section" style="min-height:72vh;display:grid;place-items:center">
    <div class="container" style="text-align:center">
        <div class="error-page__code">500</div>

        <h1 style="font-size:clamp(1.6rem,3.4vw,2.4rem)">Une erreur interne est survenue.</h1>
        <p class="text-muted" style="margin:1.1rem auto 0;max-width:56ch">
            L'incident a été journalisé et notre équipe technique en est informée.
            Merci de réessayer dans quelques instants.
        </p>

        <?php if (!empty($detail)): ?>
            <pre class="error-page__detail"><?= e($detail) ?></pre>
        <?php endif; ?>

        <div class="cluster" style="justify-content:center;margin-top:2rem">
            <a class="btn btn--primary" href="/"><?= icon('refresh', 17) ?> Réessayer</a>
            <a class="btn btn--outline" href="/#contact"><?= icon('mail', 17) ?> Contacter le support</a>
        </div>
    </div>
</section>
