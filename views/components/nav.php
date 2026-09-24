<?php

declare(strict_types=1);

/** Navigation principale du site public. */

$links = [
    ['href' => '/#hero', 'label' => 'Accueil'],
    ['href' => '/#services', 'label' => 'Services'],
    ['href' => '/#fonctionnalites', 'label' => 'Fonctionnalités'],
    ['href' => '/#process', 'label' => 'Méthode'],
    ['href' => '/#tarifs', 'label' => 'Tarifs'],
    ['href' => '/#contact', 'label' => 'Contact'],
];
?>
<nav class="site-nav" aria-label="Navigation principale">
    <div class="container nav__inner">
        <a class="brand" href="/" aria-label="<?= e(site_name()) ?> — accueil">
            <svg class="brand__mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path d="M20 2 37 11v18L20 38 3 29V11L20 2Z" stroke="currentColor" stroke-width="1.6" opacity=".5"/>
                <path d="M20 11 29 16v9l-9 5-9-5v-9l9-5Z" fill="currentColor"/>
            </svg>
            <span><?= e(site_name()) ?></span>
            <span class="brand__tag">2026</span>
        </a>

        <ul class="nav__menu">
            <?php foreach ($links as $link): ?>
                <li>
                    <a class="nav__link" href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="nav__actions">
            <button class="icon-btn" type="button" data-theme-toggle aria-pressed="false" aria-label="Activer le thème clair">
                <?= icon('sun', 16) ?>
            </button>

            <?php if (auth_user() !== null): ?>
                <a class="btn btn--ghost btn--sm" href="/app">Console</a>
            <?php else: ?>
                <a class="btn btn--ghost btn--sm" href="/login">Se connecter</a>
            <?php endif; ?>

            <a class="btn btn--primary btn--sm btn--magnetic" href="#contact">
                Commencer
                <?= icon('arrow-right', 16) ?>
            </a>

            <button class="nav__burger" type="button" aria-expanded="false" aria-controls="menu-mobile" aria-label="Ouvrir le menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<div class="nav__mobile" id="menu-mobile" role="dialog" aria-modal="true" aria-label="Menu">
    <?php foreach ($links as $index => $link): ?>
        <a href="<?= e($link['href']) ?>">
            <span class="idx"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <?= e($link['label']) ?>
        </a>
    <?php endforeach; ?>

    <div class="cluster" style="margin-top:1.6rem">
        <a class="btn btn--primary" href="#contact">Commencer maintenant</a>
        <a class="btn btn--outline" href="<?= auth_user() !== null ? '/app' : '/login' ?>">
            <?= auth_user() !== null ? 'Accéder à la console' : 'Se connecter' ?>
        </a>
    </div>
</div>
