<?php

declare(strict_types=1);

/**
 * Layout public — site vitrine.
 *
 * @var string $content
 * @var array<string, mixed> $meta
 * @var array<int, array{type:string,message:string}> $flashes
 * @var \App\Core\View $__view
 */

$meta = array_merge([
    'title'       => site_name(),
    'description' => (string) config('app.tagline'),
    'canonical'   => '/',
    'type'        => 'website',
    'robots'      => 'index, follow',
], $meta ?? []);

$siteUrl = rtrim((string) config('app.url'), '/');
$canonical = str_starts_with((string) $meta['canonical'], 'http')
    ? (string) $meta['canonical']
    : $siteUrl . $meta['canonical'];

$company = company_profile();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#04050a">
    <meta name="color-scheme" content="dark light">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">

    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="robots" content="<?= e($meta['robots']) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="<?= e($meta['type']) ?>">
    <meta property="og:site_name" content="<?= e(site_name()) ?>">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($siteUrl . '/assets/images/og-cover.svg') ?>">
    <meta property="og:locale" content="fr_FR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($meta['title']) ?>">
    <meta name="twitter:description" content="<?= e($meta['description']) ?>">
    <meta name="twitter:image" content="<?= e($siteUrl . '/assets/images/og-cover.svg') ?>">

    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/images/favicon.svg">

    <!-- Polices auto-hébergées : aucune dépendance à un CDN tiers -->
    <link rel="preload" href="<?= e(asset('fonts/space-grotesk-latin-600-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/inter-latin-400-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/core.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/landing.css')) ?>">

    <?php if (isset($schema)): ?>
        <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
</head>
<body class="site"
      data-flash="<?= e(json_encode($flashes ?? [], JSON_UNESCAPED_UNICODE)) ?>"
      data-theme-url="">

<a class="skip-link" href="#contenu">Aller au contenu principal</a>

<!-- Écran de chargement -->
<div class="loader" id="loader" role="status" aria-live="polite">
    <div class="loader__inner">
        <div class="loader__mark">
            <svg class="loader__glyph" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path d="M20 2 37 11v18L20 38 3 29V11L20 2Z" stroke="currentColor" stroke-width="1.4" opacity=".55"/>
                <path d="M20 11 29 16v9l-9 5-9-5v-9l9-5Z" fill="currentColor" opacity=".9"/>
            </svg>
            <span><?= e(site_name()) ?></span>
        </div>
        <div class="loader__bar"><div class="loader__fill"></div></div>
        <div class="loader__meta">
            <span class="loader__status">Initialisation</span>
            <span class="loader__percent">000%</span>
        </div>
    </div>
</div>

<div class="scroll-progress" aria-hidden="true"></div>
<div class="bg-noise" aria-hidden="true"></div>

<?php require BASE_PATH . '/views/components/nav.php'; ?>

<main id="contenu">
    <?= $__view->yieldContent('content') ?>
</main>

<?php require BASE_PATH . '/views/components/footer.php'; ?>

<button class="to-top" type="button" aria-label="Revenir en haut de la page">
    <?= icon('arrow-up', 18) ?>
</button>

<script src="<?= e(asset('vendor/gsap.min.js')) ?>" defer></script>
<script src="<?= e(asset('vendor/ScrollTrigger.min.js')) ?>" defer></script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<script src="<?= e(asset('js/landing.js')) ?>" defer></script>
</body>
</html>
