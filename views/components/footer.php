<?php

declare(strict_types=1);

/** Pied de page du site public. */

$company = company_profile();
$social = (array) ($company['social'] ?? []);
$year = date('Y');

$columns = [
    'Produit' => [
        ['label' => 'Fonctionnalités', 'href' => '/#fonctionnalites'],
        ['label' => 'Démonstration', 'href' => '/#demonstration'],
        ['label' => 'Tarifs', 'href' => '/#tarifs'],
        ['label' => 'Sécurité', 'href' => '/#technologies'],
        ['label' => 'Console de gestion', 'href' => '/login'],
    ],
    'Services' => [
        ['label' => 'Audit & cadrage', 'href' => '/services'],
        ['label' => 'Intégration', 'href' => '/services'],
        ['label' => 'Développement sur mesure', 'href' => '/services'],
        ['label' => 'Formation', 'href' => '/services'],
        ['label' => 'Maintenance', 'href' => '/services'],
    ],
    'Entreprise' => [
        ['label' => 'À propos', 'href' => '/a-propos'],
        ['label' => 'Références', 'href' => '/realisations'],
        ['label' => 'Méthode', 'href' => '/#process'],
        ['label' => 'Nous contacter', 'href' => '/#contact'],
    ],
    'Ressources' => [
        ['label' => 'Documentation', 'href' => '/#faq'],
        ['label' => 'Questions fréquentes', 'href' => '/#faq'],
        ['label' => 'Statut de la plateforme', 'href' => '/#technologies'],
        ['label' => 'Sitemap', 'href' => '/sitemap.xml'],
    ],
];
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer__top">
            <div class="footer__brand">
                <a class="brand" href="/" aria-label="<?= e(site_name()) ?> — accueil">
                    <svg class="brand__mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                        <path d="M20 2 37 11v18L20 38 3 29V11L20 2Z" stroke="currentColor" stroke-width="1.6" opacity=".5"/>
                        <path d="M20 11 29 16v9l-9 5-9-5v-9l9-5Z" fill="currentColor"/>
                    </svg>
                    <span><?= e(site_name()) ?></span>
                </a>

                <p>
                    Plateforme de pilotage des affectations et de la mobilité interne.
                    Des décisions rapides, des données fiables, des équipes alignées.
                </p>

                <div class="footer__contact">
                    <a href="mailto:<?= e((string) ($company['email'] ?? '')) ?>"><?= e((string) ($company['email'] ?? '')) ?></a>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', (string) ($company['phone'] ?? ''))) ?>"><?= e((string) ($company['phone'] ?? '')) ?></a>
                    <span><?= e((string) ($company['address'] ?? '')) ?></span>
                </div>

                <div class="footer__social">
                    <?php
                    $socialIcons = [
                        'linkedin' => 'LinkedIn',
                        'github'   => 'GitHub',
                        'x'        => 'X (Twitter)',
                        'youtube'  => 'YouTube',
                    ];
                    foreach ($socialIcons as $key => $label):
                        if (empty($social[$key])) {
                            continue;
                        }
                        ?>
                        <a href="<?= e((string) $social[$key]) ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="<?= e($label) ?>">
                            <?= icon($key === 'x' ? 'x-social' : $key, 17) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($columns as $title => $items): ?>
                <div class="footer__col">
                    <h4><?= e($title) ?></h4>
                    <ul>
                        <?php foreach ($items as $item): ?>
                            <li><a href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer__bottom">
            <span>&copy; <?= $year ?> <?= e((string) ($company['legal_name'] ?? 'AFFECTA')) ?>. Tous droits réservés.</span>

            <div class="footer__legal">
                <a href="/legal/confidentialite">Confidentialité</a>
                <a href="/legal/mentions-legales">Mentions légales</a>
                <a href="/sitemap.xml">Plan du site</a>
                <span class="footer__status">
                    <span class="dot dot--pulse" aria-hidden="true"></span>
                    Tous les services opérationnels
                </span>
            </div>
        </div>
    </div>
</footer>
