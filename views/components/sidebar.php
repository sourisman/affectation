<?php

declare(strict_types=1);

/**
 * Barre latérale de la console.
 *
 * @var string|null $scope « admin » pour la section administration
 */

$scope = $scope ?? 'app';
$user = auth_user() ?? ['name' => 'Utilisateur', 'role' => 'guest', 'email' => ''];
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

/** Marque le lien actif en tenant compte des sous-routes. */
$isActive = static function (string $href) use ($path): bool {
    if ($href === '/app' || $href === '/admin') {
        return rtrim($path, '/') === $href;
    }

    return str_starts_with($path, $href);
};

$operatorLinks = [
    ['href' => '/app', 'label' => 'Tableau de bord', 'icon' => 'grid'],
    ['href' => '/app/affectations', 'label' => 'Affectations', 'icon' => 'route'],
    ['href' => '/app/affectations/suivi', 'label' => 'Suivi des mouvements', 'icon' => 'shuffle'],
    ['href' => '/app/employes', 'label' => 'Employés', 'icon' => 'users'],
    ['href' => '/app/lieux', 'label' => 'Lieux', 'icon' => 'building'],
];

$analysisLinks = [
    ['href' => '/app/historique', 'label' => 'Historique', 'icon' => 'history'],
    ['href' => '/app/rapports', 'label' => 'Rapports', 'icon' => 'chart'],
    ['href' => '/app/rapports/non-affectes', 'label' => 'Agents non affectés', 'icon' => 'alert'],
];

$adminLinks = [];

if (is_admin()) {
    $pending = (new \App\Models\ContactMessage())->unreadCount();

    $adminLinks = [
        ['href' => '/admin', 'label' => 'Vue d\'ensemble', 'icon' => 'activity'],
        ['href' => '/admin/utilisateurs', 'label' => 'Utilisateurs', 'icon' => 'user'],
        ['href' => '/admin/messages', 'label' => 'Messages', 'icon' => 'inbox', 'count' => $pending],
        ['href' => '/admin/configuration', 'label' => 'Configuration', 'icon' => 'settings'],
    ];
}
?>
<aside class="sidebar" id="sidebar">
    <a class="sidebar__brand" href="/app">
        <svg class="brand__mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <path d="M20 2 37 11v18L20 38 3 29V11L20 2Z" stroke="currentColor" stroke-width="1.6" opacity=".5"/>
            <path d="M20 11 29 16v9l-9 5-9-5v-9l9-5Z" fill="currentColor"/>
        </svg>
        <div>
            <div class="sidebar__brand-name"><?= e(site_name()) ?></div>
            <div class="sidebar__brand-role"><?= $scope === 'admin' ? 'Administration' : 'Console opérationnelle' ?></div>
        </div>
    </a>

    <nav class="sidebar__group" aria-label="Pilotage">
        <span class="sidebar__label">Pilotage</span>
        <?php foreach ($operatorLinks as $link): ?>
            <a class="sidebar__link <?= $isActive($link['href']) ? 'is-active' : '' ?>" href="<?= e($link['href']) ?>">
                <?= icon($link['icon'], 18) ?>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <nav class="sidebar__group" aria-label="Analyse">
        <span class="sidebar__label">Analyse</span>
        <?php foreach ($analysisLinks as $link): ?>
            <a class="sidebar__link <?= $isActive($link['href']) ? 'is-active' : '' ?>" href="<?= e($link['href']) ?>">
                <?= icon($link['icon'], 18) ?>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($adminLinks !== []): ?>
        <nav class="sidebar__group" aria-label="Administration">
            <span class="sidebar__label">Administration</span>
            <?php foreach ($adminLinks as $link): ?>
                <a class="sidebar__link <?= $isActive($link['href']) ? 'is-active' : '' ?>" href="<?= e($link['href']) ?>">
                    <?= icon($link['icon'], 18) ?>
                    <span><?= e($link['label']) ?></span>
                    <?php if (!empty($link['count'])): ?>
                        <span class="count"><?= (int) $link['count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <div class="sidebar__foot">
        <div class="sidebar__user">
            <span class="sidebar__avatar" aria-hidden="true"><?= e(initials((string) $user['name'])) ?></span>
            <div style="min-width:0">
                <div class="sidebar__user-name"><?= e((string) $user['name']) ?></div>
                <div class="sidebar__user-role"><?= e(mask_email((string) $user['email'])) ?></div>
            </div>
        </div>

        <form action="/logout" method="post" style="margin-top:.6rem">
            <?= csrf_field() ?>
            <button class="btn btn--ghost btn--sm btn--block" type="submit">
                <?= icon('log-out', 16) ?>
                Se déconnecter
            </button>
        </form>
    </div>
</aside>
