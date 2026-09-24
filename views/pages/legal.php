<?php

declare(strict_types=1);

/**
 * Pages légales : confidentialité et mentions légales.
 *
 * @var string $pageKey
 * @var string $pageTitle
 */

$company = company_profile();
$updated = '24 septembre 2026';

$contents = [
    'confidentialite' => [
        ['h2' => 'Responsable du traitement', 'body' => 'Le responsable du traitement des données est ' . ($company['legal_name'] ?? 'AFFECTA')
            . ', dont le siège est situé ' . ($company['address'] ?? '') . '. Toute question relative aux données personnelles peut être adressée à '
            . ($company['email'] ?? '') . '.'],
        ['h2' => 'Données collectées', 'body' => 'Deux catégories de données sont traitées :'],
        ['list' => [
            'Données de contact transmises via le formulaire : nom, adresse email, téléphone, sujet et message.',
            'Données de gestion des affectations : identifiant agent, civilité, nom, prénom, email professionnel, téléphone professionnel, poste, lieu de rattachement, dates d\'embauche et d\'affectation, motif et statut.',
        ]],
        ['h2' => 'Finalités et bases légales', 'body' => 'Les données de contact servent exclusivement à répondre à votre demande (base légale : votre consentement). '
            . 'Les données de gestion des affectations sont traitées pour l\'exécution du contrat de travail et l\'intérêt légitime de l\'organisation '
            . '(gestion des ressources humaines, paie, obligations légales).'],
        ['h2' => 'Durées de conservation', 'list' => [
            'Messages de contact : 24 mois à compter du dernier échange.',
            'Historique des affectations : durée légale de conservation des dossiers du personnel.',
            'Journaux techniques et d\'audit : 12 mois.',
        ]],
        ['h2' => 'Vos droits', 'body' => 'Vous disposez d\'un droit d\'accès, de rectification, d\'effacement, de limitation, d\'opposition et de portabilité. '
            . 'Ces droits s\'exercent par email à ' . ($company['email'] ?? '') . '. Une réponse vous est apportée dans un délai maximum d\'un mois.'],
        ['h2' => 'Sécurité', 'body' => 'Les données sont protégées par chiffrement en transit (TLS), cloisonnement des accès par rôle, '
            . 'journalisation des actions sensibles, jetons anti-CSRF et requêtes préparées empêchant toute injection SQL. '
            . 'Les mots de passe sont stockés sous forme de condensats (bcrypt/argon2) et ne sont jamais lisibles.'],
        ['h2' => 'Sous-traitants', 'body' => 'L\'hébergement est assuré par un prestataire situé dans l\'Union européenne ou à Madagascar, selon la formule retenue. '
            . 'Aucune donnée n\'est cédée, louée ou transmise à des fins publicitaires.'],
    ],
    'mentions-legales' => [
        ['h2' => 'Éditeur du site', 'body' => ($company['legal_name'] ?? 'AFFECTA') . ' — ' . ($company['address'] ?? '') . '. '
            . 'Contact : ' . ($company['email'] ?? '') . ' — ' . ($company['phone'] ?? '') . '.'],
        ['h2' => 'Hébergement', 'body' => 'L\'application est hébergée sur une infrastructure conteneurisée (Docker) exploitée en Europe ou à Madagascar '
            . 'selon la formule contractuelle retenue par le client.'],
        ['h2' => 'Propriété intellectuelle', 'body' => 'L\'ensemble des éléments du site (structure, textes, interfaces, code source, marques et logos) '
            . 'est protégé par le droit de la propriété intellectuelle. Toute reproduction, même partielle, est interdite sans autorisation écrite préalable.'],
        ['h2' => 'Données du client', 'body' => 'Les données saisies dans la plateforme demeurent la propriété exclusive du client. '
            . 'Un export complet (CSV, SQL) est fourni sur demande, y compris en fin de relation contractuelle.'],
        ['h2' => 'Disponibilité', 'body' => 'La plateforme est exploitée avec un objectif de disponibilité de 99,9 %. '
            . 'Les opérations de maintenance planifiées sont annoncées au moins 48 heures à l\'avance.'],
        ['h2' => 'Droit applicable', 'body' => 'Les présentes mentions sont soumises au droit malgache. '
            . 'En cas de litige, une solution amiable est recherchée en priorité avant toute action contentieuse.'],
    ],
];

$sections = $contents[$pageKey] ?? [];
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Informations légales</span>
        <h1 style="margin-top:1.1rem;font-size:clamp(2rem,4.4vw,3.1rem)"><?= e($pageTitle) ?></h1>
        <p class="text-muted mono" style="margin-top:1rem;font-size:.74rem">Dernière mise à jour : <?= e($updated) ?></p>
    </div>
</section>

<section class="section section--flush-top">
    <div class="container" style="display:grid;gap:2.5rem">
        <aside class="toc">
            <strong>Sommaire</strong>
            <ol>
                <?php foreach ($sections as $section): ?>
                    <?php if (!isset($section['h2'])) { continue; } ?>
                    <li>
                        <a href="#<?= e(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $section['h2']))) ?>">
                            <?= e($section['h2']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </aside>

        <article class="prose">
            <?php foreach ($sections as $section): ?>
                <?php if (isset($section['h2'])): ?>
                    <h2 id="<?= e(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $section['h2']))) ?>"><?= e($section['h2']) ?></h2>
                <?php endif; ?>

                <?php if (isset($section['body'])): ?>
                    <p><?= e($section['body']) ?></p>
                <?php endif; ?>

                <?php if (isset($section['list'])): ?>
                    <ul>
                        <?php foreach ($section['list'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endforeach; ?>

            <p style="margin-top:2.5rem">
                Une question sur ce document ? Écrivez-nous à
                <a href="mailto:<?= e((string) ($company['email'] ?? '')) ?>"><?= e((string) ($company['email'] ?? '')) ?></a>.
            </p>
        </article>
    </div>
</section>
