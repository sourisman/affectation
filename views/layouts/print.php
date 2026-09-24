<?php

declare(strict_types=1);

/**
 * Layout d'impression — mise en page document, sans navigation.
 *
 * @var string $content
 * @var array<string, mixed> $meta
 */

$company = company_profile();
$meta = array_merge(['title' => 'Rapport — ' . (string) config('app.name')], $meta ?? []);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($meta['title']) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/core.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/console.css')) ?>">
    <style>
        /* Mise en page spécifique à l'impression papier et PDF. */
        body { background: #fff; color: #0a0c12; }
        .sheet { max-width: 900px; margin: 0 auto; padding: 2.5rem 1.5rem 4rem; }
        .sheet__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem;
            padding-bottom: 1.2rem; border-bottom: 2px solid #0a0c12; }
        .sheet__brand { font-family: var(--font-display); font-size: 1.1rem; letter-spacing: .18em; text-transform: uppercase; }
        .sheet__meta { font-family: var(--font-mono); font-size: .7rem; text-align: right; color: #5b6473; }
        .sheet h1 { font-size: 1.7rem; margin-top: 2rem; }
        .sheet h2 { font-size: 1.05rem; margin-top: 2.2rem; padding-bottom: .4rem; border-bottom: 1px solid #d8dce4; }
        .sheet table { font-size: .8rem; margin-top: .8rem; }
        .sheet th, .sheet td { border-bottom: 1px solid #e4e7ec; padding: .45rem .5rem; text-align: left; }
        .sheet thead th { font-family: var(--font-mono); font-size: .62rem; text-transform: uppercase; letter-spacing: .08em; color: #5b6473; }
        .sheet__foot { margin-top: 3rem; padding-top: 1rem; border-top: 1px solid #d8dce4;
            font-size: .72rem; color: #7b8496; display: flex; justify-content: space-between; }
        .no-print { margin-top: 1.5rem; }
        @media print {
            .no-print { display: none !important; }
            .sheet { padding: 0; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
        .panel, .table-wrap { border-color: #e4e7ec; }
        .kv__key, .mono { color: #5b6473; }
    </style>
</head>
<body>
<div class="sheet">
    <header class="sheet__head">
        <div>
            <div class="sheet__brand"><?= e((string) config('app.name')) ?></div>
            <div style="font-size:.8rem;color:#5b6473;margin-top:.2rem">
                <?= e((string) ($company['legal_name'] ?? '')) ?> · <?= e((string) ($company['city'] ?? '')) ?>
            </div>
        </div>
        <div class="sheet__meta">
            Document généré le <?= e(date('d/m/Y à H:i')) ?><br>
            <?= e((string) ($company['email'] ?? '')) ?> · <?= e((string) ($company['phone'] ?? '')) ?>
        </div>
    </header>

    <?= $__view->yieldContent('content') ?>

    <footer class="sheet__foot">
        <span>Document confidentiel — usage interne</span>
        <span><?= e((string) config('app.name')) ?> © <?= date('Y') ?></span>
    </footer>

    <div class="no-print">
        <button class="btn btn--primary" type="button" data-print>Imprimer / enregistrer en PDF</button>
        <a class="btn btn--outline" href="/app/rapports">Retour aux rapports</a>
    </div>
</div>
</body>
</html>
