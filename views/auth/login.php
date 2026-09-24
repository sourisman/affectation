<?php

declare(strict_types=1);

/**
 * Écran de connexion — layout neutre (pas de navbar/footer du site vitrine).
 *
 * @var array<int, array{type:string,message:string}> $flashes
 */

$company = company_profile();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#04050a">
    <title>Connexion — Espace <?= e(site_name()) ?></title>
    <meta name="description" content="Accédez à votre espace de pilotage des affectations.">
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="<?= e(asset('fonts/space-grotesk-latin-600-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/core.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/console.css')) ?>">
</head>
<body class="console" data-flash="<?= e(json_encode($flashes ?? [], JSON_UNESCAPED_UNICODE)) ?>">
<div class="auth-shell">
    <aside class="auth-aside">
        <a class="brand" href="/" aria-label="Retour au site">
            <svg class="brand__mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path d="M20 2 37 11v18L20 38 3 29V11L20 2Z" stroke="currentColor" stroke-width="1.6" opacity=".5"/>
                <path d="M20 11 29 16v9l-9 5-9-5v-9l9-5Z" fill="currentColor"/>
            </svg>
            <span><?= e(site_name()) ?></span>
        </a>

        <div>
            <p class="auth-aside__quote">
                « Les décisions d'affectation se prennent enfin sur des données à jour,
                avec une trace complète de chaque arbitrage. »
            </p>
            <p class="text-muted" style="margin-top:1.2rem;font-size:.88rem">
                Nadia Rakoto — Directrice des opérations, Groupe Horizon
            </p>
        </div>

        <div class="mono text-muted" style="font-size:.64rem;letter-spacing:.14em">
            <?= e((string) ($company['legal_name'] ?? '')) ?> · Antananarivo
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <span class="eyebrow">Espace sécurisé</span>
            <h1 style="margin-top:1rem">Connexion à la console</h1>
            <p>Utilisez les identifiants transmis par votre administrateur.</p>

            <form class="auth-form" data-ajax action="/login" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="field">
                    <label class="field__label" for="email">Adresse email</label>
                    <input class="input" type="email" id="email" name="email" required autocomplete="username"
                           autofocus placeholder="prenom.nom@affecta.dev" value="<?= e((string) old('email')) ?>">
                </div>

                <div class="field">
                    <label class="field__label" for="password">Mot de passe</label>
                    <div class="input-group">
                        <input class="input" type="password" id="password" name="password" required
                               autocomplete="current-password" minlength="6" placeholder="••••••••••">
                        <button class="input-group__toggle" type="button" data-toggle-password="password"
                                aria-pressed="false" aria-label="Afficher le mot de passe">
                            <?= icon('eye', 16) ?>
                        </button>
                    </div>
                </div>

                <button class="btn btn--primary btn--block btn--lg" type="submit">
                    <?= icon('log-out', 18) ?>
                    Se connecter
                </button>
            </form>

            <?php if ((bool) config('app.debug')): ?>
                <div class="auth-hint">
                    <strong style="display:block;margin-bottom:.4rem;color:var(--text-soft)">Comptes de démonstration</strong>
                    Administrateur : <code>admin@affecta.dev</code> / <code>Admin@2026</code><br>
                    Responsable : <code>manager@affecta.dev</code> / <code>Manager@2026</code>
                </div>
            <?php endif; ?>

            <p class="text-muted" style="margin-top:1.6rem;font-size:.82rem">
                <?= icon('lock', 14) ?>
                Session chiffrée, connexion limitée à 5 tentatives par quart d'heure.
            </p>

            <p style="margin-top:1.4rem;font-size:.86rem">
                <a class="text-accent" href="/">← Retour au site public</a>
            </p>
        </div>
    </main>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
