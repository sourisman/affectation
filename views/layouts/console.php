<?php

declare(strict_types=1);

/**
 * Layout de la console métier (espace connecté).
 *
 * @var string $content
 * @var array<string, mixed> $meta
 * @var array<int, array{type:string,message:string}> $flashes
 * @var string|null $pageTitle
 * @var string|null $pageLead
 */

$meta = array_merge([
    'title'  => 'Console — ' . site_name(),
    'robots' => 'noindex, nofollow',
], $meta ?? []);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#04050a">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="<?= e($meta['robots']) ?>">
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="Espace de pilotage des affectations et des effectifs.">

    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="<?= e(asset('fonts/space-grotesk-latin-600-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/core.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/console.css')) ?>">
</head>
<body class="console" data-flash="<?= e(json_encode($flashes ?? [], JSON_UNESCAPED_UNICODE)) ?>">

<a class="skip-link" href="#console-contenu">Aller au contenu</a>

<div class="console-shell">
    <?php require BASE_PATH . '/views/components/sidebar.php'; ?>
    <div class="sidebar-backdrop" aria-hidden="true"></div>

    <div class="console-main">
        <header class="console-topbar">
            <button class="console-burger" type="button" aria-label="Ouvrir la navigation">
                <?= icon('menu', 19) ?>
            </button>

            <div>
                <div class="console-topbar__title"><?= e((string) ($pageTitle ?? 'Tableau de bord')) ?></div>
                <nav class="console-topbar__crumbs" aria-label="Fil d'Ariane">
                    <a href="/app">Console</a>
                    <span aria-hidden="true">/</span>
                    <span><?= e((string) ($pageTitle ?? 'Tableau de bord')) ?></span>
                </nav>
            </div>

            <div class="console-topbar__actions">
                <a class="btn btn--ghost btn--sm" href="/" target="_blank" rel="noopener">
                    <?= icon('external', 15) ?>
                    Voir le site
                </a>

                <button class="icon-btn" type="button" data-theme-toggle aria-pressed="false" aria-label="Changer de thème">
                    <?= icon('sun', 16) ?>
                </button>

                <form action="/logout" method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <button class="icon-btn icon-btn--danger" type="submit" aria-label="Se déconnecter">
                        <?= icon('log-out', 16) ?>
                    </button>
                </form>
            </div>
        </header>

        <div class="console-body" id="console-contenu">
            <?php if (!empty($pageLead)): ?>
                <div class="page-head">
                    <div>
                        <h1><?= e((string) $pageTitle) ?></h1>
                        <p><?= e((string) $pageLead) ?></p>
                    </div>
                    <div class="page-head__actions"><?= $__view->yieldContent('page-actions') ?></div>
                </div>
            <?php endif; ?>

            <?= $__view->yieldContent('content') ?>
        </div>
    </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<?= $__view->stack('scripts') ?>
</body>
</html>
