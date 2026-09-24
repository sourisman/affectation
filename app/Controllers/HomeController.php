<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * Site vitrine : hero, narration produit, démonstration, tarifs, contact.
 * Le contenu éditorial est centralisé ici pour rester facile à maintenir
 * (et pourra être piloté depuis l'administration par la suite).
 */
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('pages.home', [
            'meta' => [
                'title'       => 'AFFECTA — Construisons le futur du digital',
                'description' => 'AFFECTA est la plateforme qui pilote les affectations, la mobilité interne et les effectifs en temps réel : décisions rapides, données fiables, zéro friction.',
                'canonical'   => '/',
                'type'        => 'website',
            ],
            'features'     => $this->features(),
            'stats'        => $this->stats(),
            'services'     => $this->serviceItems(),
            'technologies' => $this->technologies(),
            'process'      => $this->process(),
            'testimonials' => $this->testimonials(),
            'plans'        => $this->plans(),
            'faq'          => $this->faq(),
            'schema'       => $this->schema(),
        ]);
    }

    public function about(Request $request): Response
    {
        return $this->view('pages.about', [
            'meta' => [
                'title'       => 'À propos — AFFECTA',
                'description' => 'Équipe, mission et méthode : AFFECTA conçoit des outils de pilotage des ressources humaines pour les organisations exigeantes.',
                'canonical'   => '/a-propos',
            ],
            'stats' => $this->stats(),
        ]);
    }

    public function services(Request $request): Response
    {
        return $this->view('pages.services', [
            'meta' => [
                'title'       => 'Services — AFFECTA',
                'description' => 'Audit, déploiement, intégration, formation et maintien en condition opérationnelle de votre plateforme de gestion des affectations.',
                'canonical'   => '/services',
            ],
            'services' => $this->serviceItems(),
            'process'  => $this->process(),
        ]);
    }

    public function portfolio(Request $request): Response
    {
        return $this->view('pages.portfolio', [
            'meta' => [
                'title'       => 'Références — AFFECTA',
                'description' => 'Études de cas et résultats mesurés : comment nos clients ont réduit leurs délais de traitement et fiabilisé leurs effectifs.',
                'canonical'   => '/realisations',
            ],
            'testimonials' => $this->testimonials(),
        ]);
    }

    public function legal(Request $request, string $page): Response
    {
        $pages = [
            'confidentialite' => [
                'title'       => 'Politique de confidentialité',
                'description' => 'Traitement des données personnelles, durées de conservation, droits des personnes et contact du délégué à la protection des données.',
            ],
            'mentions-legales' => [
                'title'       => 'Mentions légales',
                'description' => 'Éditeur, hébergement, propriété intellectuelle et conditions d\'utilisation de la plateforme AFFECTA.',
            ],
        ];

        if (!isset($pages[$page])) {
            return $this->notFoundView();
        }

        return $this->view('pages.legal', [
            'meta' => [
                'title'       => $pages[$page]['title'] . ' — AFFECTA',
                'description' => $pages[$page]['description'],
                'canonical'   => '/legal/' . $page,
            ],
            'pageKey'  => $page,
            'pageTitle' => $pages[$page]['title'],
        ]);
    }

    private function notFoundView(): Response
    {
        return Response::html(\App\Core\View::make('errors.404')->layout('layouts.public')->render(), 404);
    }

    /** @return list<array<string, mixed>> */
    private function features(): array
    {
        return [
            [
                'num' => '01', 'size' => 'hero', 'icon' => 'gauge',
                'title' => 'Performance temps réel',
                'text'  => 'Chaque affectation se répercute instantanément sur les effectifs, les capacités et les indicateurs. Plus de tableaux parallèles, plus d\'écarts entre le terrain et la direction.',
                'rows'  => [
                    ['Studio Antananarivo', '42 agents', '98 %'],
                    ['Antenne Toamasina', '18 agents', '91 %'],
                    ['Hub Antsiranana', '11 agents', '87 %'],
                ],
            ],
            [
                'num' => '02', 'size' => 'wide', 'icon' => 'shield',
                'title' => 'Sécurité by design',
                'text'  => 'PDO et requêtes préparées, jetons CSRF, sessions durcies, en-têtes de sécurité, journalisation des actions sensibles.',
            ],
            [
                'num' => '03', 'size' => 'wide', 'icon' => 'bolt',
                'title' => 'Automatisation',
                'text'  => 'Numérotation automatique des actes, contrôle des dates, alertes sur les dossiers en attente et transferts en un clic.',
            ],
            [
                'num' => '04', 'size' => 'third', 'icon' => 'spark',
                'title' => 'Intelligence',
                'text'  => 'Détection des sur-effectifs, des postes vacants et des affectations incohérentes avant validation.',
            ],
            [
                'num' => '05', 'size' => 'third', 'icon' => 'layers',
                'title' => 'Scalabilité',
                'text'  => 'De 50 à 50 000 agents : pagination, index, requêtes optimisées et architecture modulaire.',
            ],
            [
                'num' => '06', 'size' => 'third', 'icon' => 'cursor',
                'title' => 'Expérience utilisateur',
                'text'  => 'Interface pensée pour les opérations : chaque action utile est accessible en moins de trois clics.',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function stats(): array
    {
        return [
            ['value' => 99.9, 'decimals' => 1, 'suffix' => '%', 'label' => 'Disponibilité de la plateforme', 'meta' => 'SLA 12 mois'],
            ['value' => 50, 'decimals' => 0, 'suffix' => 'K+', 'label' => 'Utilisateurs accompagnés', 'meta' => '6 pays'],
            ['value' => 120, 'decimals' => 0, 'suffix' => '+', 'label' => 'Projets livrés', 'meta' => 'depuis 2018'],
            ['value' => 15, 'decimals' => 0, 'suffix' => '', 'label' => 'Pays couverts', 'meta' => '3 continents'],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function serviceItems(): array
    {
        return [
            [
                'num' => '01', 'key' => 'audit', 'icon' => 'search',
                'title' => 'Audit & cadrage',
                'subtitle' => 'Cartographie des effectifs, des flux et des irritants',
                'text'  => 'Nous partons de vos données réelles : organigramme, lieux, contraintes réglementaires. Vous recevez une feuille de route priorisée et chiffrée.',
                'tags'  => ['Diagnostic', 'Feuille de route', 'Cadrage'],
                'image' => 'service-audit.svg',
            ],
            [
                'num' => '02', 'key' => 'integration', 'icon' => 'plug',
                'title' => 'Intégration & migration',
                'subtitle' => 'Reprise de vos historiques sans rupture de service',
                'text'  => 'Import contrôlé des employés, lieux et affectations, réconciliation des écarts, double vérification avant bascule.',
                'tags'  => ['ETL', 'Reprise de données', 'Recette'],
                'image' => 'service-integration.svg',
            ],
            [
                'num' => '03', 'key' => 'sur_mesure', 'icon' => 'code',
                'title' => 'Développement sur mesure',
                'subtitle' => 'Modules spécifiques et interfaçage',
                'text'  => 'API REST sécurisée, connecteurs paie et SIRH, règles de gestion propres à votre organisation.',
                'tags'  => ['API', 'Connecteurs', 'Sécurité'],
                'image' => 'service-dev.svg',
            ],
            [
                'num' => '04', 'key' => 'formation', 'icon' => 'users',
                'title' => 'Formation & adoption',
                'subtitle' => 'Des équipes autonomes dès la première semaine',
                'text'  => 'Ateliers par rôle (RH, direction, encadrement), guides courts, support réactif et mesure de l\'adoption.',
                'tags'  => ['Ateliers', 'Guides', 'Support'],
                'image' => 'service-formation.svg',
            ],
            [
                'num' => '05', 'key' => 'mco', 'icon' => 'activity',
                'title' => 'Maintien en condition opérationnelle',
                'subtitle' => 'Supervision, sauvegardes, évolutions',
                'text'  => 'Monitoring, sauvegardes testées, mises à jour de sécurité et amélioration continue pilotée par les indicateurs.',
                'tags'  => ['Supervision', 'SLA 8h', 'Évolutions'],
                'image' => 'service-mco.svg',
            ],
        ];
    }

    /** @return list<array<string,string>> */
    private function technologies(): array
    {
        return [
            ['name' => 'PHP 8.3', 'glyph' => 'PHP', 'note' => 'Backend principal'],
            ['name' => 'Laravel', 'glyph' => 'LAR', 'note' => 'Écosystème & patterns'],
            ['name' => 'JavaScript ES6+', 'glyph' => 'JS', 'note' => 'Interactions'],
            ['name' => 'React', 'glyph' => 'RCT', 'note' => 'Interfaces riches'],
            ['name' => 'Vue', 'glyph' => 'VUE', 'note' => 'Composants légers'],
            ['name' => 'Node.js', 'glyph' => 'NODE', 'note' => 'Services temps réel'],
            ['name' => 'MySQL / MariaDB', 'glyph' => 'SQL', 'note' => 'Données relationnelles'],
            ['name' => 'Docker', 'glyph' => 'DKR', 'note' => 'Reproductibilité'],
            ['name' => 'Kubernetes', 'glyph' => 'K8S', 'note' => 'Orchestration'],
            ['name' => 'GitLab CI', 'glyph' => 'CI', 'note' => 'Intégration continue'],
            ['name' => 'Linux', 'glyph' => 'LNX', 'note' => 'Environnement'],
            ['name' => 'AWS', 'glyph' => 'AWS', 'note' => 'Hébergement'],
        ];
    }

    /** @return list<array<string,string>> */
    private function process(): array
    {
        return [
            ['num' => '01', 'title' => 'Analyse', 'text' => 'Immersion sur site, entretiens avec les équipes, cartographie des flux réels et des irritants.', 'duration' => '1 à 2 semaines'],
            ['num' => '02', 'title' => 'Conception', 'text' => 'Modèle de données, règles de gestion, maquettes validées et scénarios de recette.', 'duration' => '2 à 3 semaines'],
            ['num' => '03', 'title' => 'Développement', 'text' => 'Sprints courts, démonstrations régulières, code revu et testé à chaque livraison.', 'duration' => '4 à 10 semaines'],
            ['num' => '04', 'title' => 'Tests', 'text' => 'Recette fonctionnelle, tests de charge, contrôle de sécurité et validation métier.', 'duration' => '1 à 2 semaines'],
            ['num' => '05', 'title' => 'Déploiement', 'text' => 'Bascule progressive, reprise des données, accompagnement des équipes sur le terrain.', 'duration' => '1 semaine'],
            ['num' => '06', 'title' => 'Maintenance', 'text' => 'Supervision, corrections, évolutions et comité de suivi trimestriel.', 'duration' => 'continu'],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function testimonials(): array
    {
        return [
            [
                'quote' => 'Nous traitions les affectations sur tableur, avec des doublons et des retards de paie. Aujourd\'hui tout est tracé et les décisions se prennent sur des données à jour.',
                'name'  => 'Nadia Rakoto', 'role' => 'Directrice des opérations', 'company' => 'Groupe Horizon', 'rating' => 5,
            ],
            [
                'quote' => 'Le délai de traitement d\'une mutation est passé de trois semaines à quatre jours. Les équipes RH ont récupéré un temps considérable.',
                'name'  => 'Hery Andrianina', 'role' => 'Responsable mobilité interne', 'company' => 'Transport Océan Indien', 'rating' => 5,
            ],
            [
                'quote' => 'La reprise de dix ans d\'historique s\'est faite sans perte. La qualité de l\'accompagnement a fait la différence auprès des équipes.',
                'name'  => 'Miora Ratsimba', 'role' => 'DRH', 'company' => 'Banque Centrale du Sud', 'rating' => 5,
            ],
            [
                'quote' => 'Interface claire, rapide, et surtout des indicateurs fiables par site. Notre comité de direction pilote enfin sur des chiffres partagés.',
                'name'  => 'David Rakotondrabe', 'role' => 'Directeur général', 'company' => 'Agro Sava', 'rating' => 4,
            ],
            [
                'quote' => 'Le support répond en moins d\'une heure et maîtrise le métier. C\'est rare et ça change tout au quotidien.',
                'name'  => 'Fanja Rasoanaivo', 'role' => 'Chef de projet SIRH', 'company' => 'Énergie Ouest', 'rating' => 5,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function plans(): array
    {
        return [
            [
                'name' => 'Starter', 'monthly' => 240000, 'yearly' => 199000, 'featured' => false,
                'desc' => 'Pour une structure qui démarre et veut sortir des tableurs.',
                'cta'  => 'Commencer maintenant',
                'features' => [
                    ['label' => 'Jusqu\'à 100 agents', 'included' => true],
                    ['label' => 'Lieux illimités', 'included' => true],
                    ['label' => 'Affectations et historique', 'included' => true],
                    ['label' => 'Comptes utilisateurs : 3', 'included' => true],
                    ['label' => 'Export CSV', 'included' => true],
                    ['label' => 'Authentification renforcée', 'included' => false],
                    ['label' => 'Accompagnement dédié', 'included' => false],
                ],
            ],
            [
                'name' => 'Pro', 'monthly' => 590000, 'yearly' => 479000, 'featured' => true,
                'desc' => 'Le standard pour les DRH multi-sites qui pilotent en continu.',
                'cta'  => 'Découvrir la plateforme',
                'features' => [
                    ['label' => 'Jusqu\'à 2 000 agents', 'included' => true],
                    ['label' => 'Tableaux de bord avancés', 'included' => true],
                    ['label' => 'Workflow de validation', 'included' => true],
                    ['label' => 'Comptes utilisateurs : 25', 'included' => true],
                    ['label' => 'Exports CSV et PDF', 'included' => true],
                    ['label' => 'Authentification renforcée', 'included' => true],
                    ['label' => 'Accompagnement dédié', 'included' => false],
                ],
            ],
            [
                'name' => 'Enterprise', 'monthly' => 1290000, 'yearly' => 990000, 'featured' => false,
                'desc' => 'Grandes organisations, exigences réglementaires et SLA contractuels.',
                'cta'  => 'Parler à un expert',
                'features' => [
                    ['label' => 'Agents illimités', 'included' => true],
                    ['label' => 'Modules sur mesure et API', 'included' => true],
                    ['label' => 'Comptes utilisateurs illimités', 'included' => true],
                    ['label' => 'Hébergement dédié ou on-premise', 'included' => true],
                    ['label' => 'SLA 8h et astreinte', 'included' => true],
                    ['label' => 'Authentification renforcée', 'included' => true],
                    ['label' => 'Accompagnement dédié', 'included' => true],
                ],
            ],
        ];
    }

    /** @return list<array{question:string,answer:string}> */
    private function faq(): array
    {
        return [
            [
                'question' => 'Combien de temps prend un déploiement complet ?',
                'answer'   => 'Entre trois et huit semaines selon la volumétrie et l\'état de vos données. Les structures jusqu\'à 300 agents démarrent généralement en moins d\'un mois, reprise d\'historique incluse.',
            ],
            [
                'question' => 'Pouvons-nous conserver notre organigramme et nos règles internes ?',
                'answer'   => 'Oui. Les lieux, postes, motifs d\'affectation et circuits de validation sont paramétrables. Aucune règle métier n\'est imposée : nous modélisons la vôtre.',
            ],
            [
                'question' => 'Où sont hébergées les données ?',
                'answer'   => 'Au choix : cloud européen, datacenter local ou hébergement sur votre propre infrastructure (on-premise). Les données peuvent être chiffrées au repos et les sauvegardes sont testées mensuellement.',
            ],
            [
                'question' => 'Comment gérez-vous les données personnelles ?',
                'answer'   => 'Minimisation des données collectées, journal d\'audit des accès, durée de conservation configurable et suppression sur demande. Un registre des traitements est fourni à la mise en service.',
            ],
            [
                'question' => 'Existe-t-il une reprise de l\'historique existant ?',
                'answer'   => 'Oui, c\'est une étape intégrée au projet : extraction, nettoyage, rapprochement des doublons et double vérification avant bascule. Un rapport d\'écarts vous est remis.',
            ],
            [
                'question' => 'Que se passe-t-il si nous arrêtons la collaboration ?',
                'answer'   => 'Vos données restent les vôtres : export complet au format ouvert (CSV, SQL) et documentation de reprise. Aucune licence n\'est verrouillante au-delà de la période en cours.',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $company = (array) config('app.company', []);
        $url = rtrim((string) config('app.url', ''), '/');

        return [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'Organization',
                    'name'        => $company['legal_name'] ?? 'AFFECTA',
                    'url'         => $url,
                    'logo'        => $url . '/assets/images/logo.svg',
                    'email'       => $company['email'] ?? null,
                    'telephone'   => $company['phone'] ?? null,
                    'address'     => [
                        '@type'           => 'PostalAddress',
                        'streetAddress'   => $company['address'] ?? '',
                        'addressLocality' => $company['city'] ?? '',
                        'addressCountry'  => $company['country'] ?? '',
                    ],
                    'sameAs' => array_values(array_filter((array) ($company['social'] ?? []))),
                ],
                [
                    '@type'       => 'SoftwareApplication',
                    'name'        => 'AFFECTA',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem'     => 'Web',
                    'description' => 'Plateforme de pilotage des affectations, de la mobilité interne et des effectifs.',
                    'offers'      => array_map(static fn (array $plan): array => [
                        '@type'         => 'Offer',
                        'name'          => $plan['name'],
                        'price'         => (string) $plan['monthly'],
                        'priceCurrency' => 'MGA',
                    ], $this->plans()),
                ],
                [
                    '@type'           => 'WebSite',
                    'url'             => $url,
                    'name'            => 'AFFECTA',
                    'inLanguage'      => 'fr',
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => $url . '/recherche?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(static fn (array $item): array => [
                        '@type' => 'Question',
                        'name'  => $item['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
                    ], $this->faq()),
                ],
            ],
        ];
    }
}
