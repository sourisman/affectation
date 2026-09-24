# AFFECTA — Plateforme de pilotage des affectations

> **Construisons le futur du digital.**
> Site vitrine premium + console opérationnelle de gestion des lieux, des agents
> et de leurs affectations. PHP 8.3, MySQL 8 / SQLite, sans dépendance front
> obligatoire, entièrement auto-hébergeable.

[![PHP](https://img.shields.io/badge/PHP-8.3-777bb4?style=flat-square)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.4%20%7C%20MariaDB-4479a1?style=flat-square)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-compose-2496ed?style=flat-square)](https://docs.docker.com/compose/)
[![Licence](https://img.shields.io/badge/licence-propri%C3%A9taire-b6ff2e?style=flat-square)](#licence)

---

## 1. Direction artistique

| Élément | Choix |
| --- | --- |
| Concept | **« Signal nocturne »** — noir profond, graphite, une seule couleur d'accent |
| Ambiance | Minimalisme premium, beaucoup d'air, halos très contrôlés, grain discret |
| Effets | Glassmorphism subtil, gradient mesh, glow, parallaxe légère, reveal au scroll |
| Discipline | Un effet ne s'affiche que s'il sert la compréhension ; jamais tous simultanément |

### Palette

| Rôle | Sombre | Clair |
| --- | --- | --- |
| Fond | `#04050a` | `#f6f7f9` |
| Surface | `rgba(255,255,255,.038)` | `#ffffff` |
| Texte | `#f4f6fa` | `#0a0c12` |
| Accent | `#b6ff2e` (lime électrique) | `#9ae600` |
| Froid (data viz) | `#6c8cff` | `#4f6ae6` |
| Sémantique | succès `#22c55e` · alerte `#f59e0b` · erreur `#ef4444` · info `#38bdf8` |

### Typographie

* **Space Grotesk** — titres et chiffres (personnalité technique, `letter-spacing` négatif)
* **Inter** — texte courant (lisibilité écran, variable)
* **JetBrains Mono** — étiquettes, références, valeurs numériques

Polices **auto-hébergées** (woff2, sous-ensemble latin) : aucune requête vers un CDN tiers,
aucun cookie de traçage, chargement conforme CSP `font-src 'self'`.

### Système d'animation

| Intention | Technique | Outil |
| --- | --- | --- |
| Chargement | Progression 0 → 100 %, voile opaque retiré en fondu | JS natif (`requestAnimationFrame`) |
| Entrée du hero | Stagger, `slide-up`, léger `blur-to-focus` | GSAP timeline |
| Révélation au scroll | Fade + translate + scale, délais en cascade | IntersectionObserver (CSS) |
| Compteurs | Interpolation `easeOutExpo`, espace insécable fine | JS natif |
| Parallaxe | Déplacement vertical lent sur 3 plans | GSAP ScrollTrigger (`scrub`) |
| Timeline | Ligne de progression remplie au défilement | ScrollTrigger |
| Survol | Élévation, glow, bordure lumineuse suivant le curseur | CSS + variable `--px/--py` |
| Curseur | Point instantané + anneau inertiel, change de forme | JS natif (`pointermove`) |
| Magnétisme | Décalage du bouton vers le curseur | GSAP `quickTo` + variables CSS |

`prefers-reduced-motion: reduce` neutralise animations, parallaxes et défilement fluide.

---

## 2. Architecture technique

```text
project/
├── public/                     ← seule racine exposée par le serveur web
│   ├── index.php               front controller unique
│   ├── router.php              routeur du serveur PHP intégré
│   ├── assets/
│   │   ├── css/                core.css · landing.css · console.css
│   │   ├── js/                 app.js (noyau) · landing.js (animations)
│   │   ├── vendor/             gsap.min.js · ScrollTrigger.min.js (auto-hébergés)
│   │   ├── fonts/              woff2 (Space Grotesk, Inter, JetBrains Mono)
│   │   └── images/             logo, favicon, visuels OG et services (SVG)
│   └── uploads/                fichiers téléversés (jamais exécutables)
│
├── app/
│   ├── Core/                   Env · Config · Request · Response · Session
│   │                           Router · View · Database · Model · Schema
│   │                           Migrator · Application
│   ├── Controllers/            Controller · Home · Contact · Auth · Sitemap
│   │   └── Admin/              Dashboard · Employe · Lieu · Affectation
│   │                           Rapport · Historique · User · Message · Setting
│   ├── Models/                 User · Lieu · Employe · Affectation
│   │                           ContactMessage · Setting · ActivityLog
│   ├── Services/               Validator · AffectationService · Mailer
│   │                           UploadService · ActivityAware
│   ├── Middleware/             Auth · Guest · Admin · Csrf · Throttle · SecurityHeaders
│   └── Helpers/                functions.php (helpers globaux) · Icons.php
│
├── config/                     app.php · database.php · mail.php
├── routes/web.php              table de routage complète
├── views/                      layouts · components · sections · pages · app · admin · errors
├── database/
│   ├── migrations/             2 migrations (socle + domaine métier)
│   ├── Seeders/                jeu de démonstration reproductible
│   └── affecta.sql             export MySQL complet (hébergement sans CLI)
├── bin/                        migrate.php · seed.php · dump-sql.php (CLI)
├── docs/                       INSTALLATION · ARCHITECTURE · SECURITE
├── storage/                    logs · cache · sessions · exports
├── tests/                      smoke.php (HTTP) · audit.php (statique)
├── docker/                     nginx/default.conf · php/php.ini
├── Dockerfile · docker-compose.yml · Makefile
├── bootstrap.php               amorçage partagé web + CLI
└── .env.example                configuration d'environnement documentée
```

**Principes**

* Un contrôleur par domaine, une action par cas d'usage ; les vues ne contiennent aucune requête SQL.
* Middlewares chaînés déclarés dans `routes/web.php` (`security`, `auth`, `admin`, `csrf`, `throttle`).
* Modèles en requêtes préparées exclusivement, colonnes écrivables limitées par `$fillable`.
* Migrations portables MySQL **et** SQLite via `App\Core\Schema` (même code, deux drivers).
* Aucune dépendance Composer obligatoire : autoloader PSR-4 de secours inclus. PHPMailer/mPDF sont optionnels.

---

## 3. Démarrage rapide (Docker)

```bash
git clone <dépôt> affecta && cd affecta
cp .env.example .env          # adapter DB_PASSWORD et CONTACT_RECIPIENT

docker compose up -d --build
docker compose exec app php bin/migrate.php
docker compose exec app php bin/seed.php      # données de démonstration

# Site public   → http://localhost:8080
# Console       → http://localhost:8080/login
# Adminer       → http://localhost:8081  (docker compose --profile tools up -d)
```

### Sans Docker (PHP natif, base SQLite)

```bash
cp .env.example .env
# Dans .env : DB_DRIVER=sqlite   (aucun serveur de base requis)

php bin/migrate.php
php bin/seed.php
php -S 0.0.0.0:8080 -t public public/router.php
```

### Sans Docker (MySQL)

```bash
mysql -u root -p < <(echo "CREATE DATABASE affecta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
# .env : DB_DRIVER=mysql, DB_HOST=127.0.0.1, DB_USERNAME, DB_PASSWORD
php bin/migrate.php && php bin/seed.php
```

Commandes utiles :

```bash
php bin/migrate.php --status     # état des migrations
php bin/migrate.php --fresh      # recrée le schéma (développement)
php bin/seed.php --force         # rejoue les données de démonstration
php bin/dump-sql.php             # régénère database/affecta.sql
php tests/smoke.php              # vérification HTTP de toutes les routes
php tests/audit.php              # audit statique des vues (CSS, icônes, variables)
```

---

## 4. Comptes de démonstration

| Rôle | Identifiant | Mot de passe | Accès |
| --- | --- | --- | --- |
| Administrateur | `admin@affecta.dev` | `Admin@2026` | Console `/app` + administration `/admin` |
| Responsable | `manager@affecta.dev` | `Manager@2026` | Console `/app` |

> Ces identifiants sont destinés à la démonstration. `php bin/seed.php` les affiche une seule fois ;
> en production, laissez `SEED_ADMIN_PASSWORD` vide afin qu'un mot de passe aléatoire soit généré.

---

## 5. Fonctionnalités livrées

### Site public
Hero animé · narration problème → solution · 6 fonctionnalités en composition asymétrique ·
démonstration produit à onglets · statistiques à compteurs animés · 5 services avec aperçu
synchronisé au survol · socle technologique · méthode en 6 étapes (ligne de progression) ·
témoignages en carrousel accessible · 3 plans tarifaires avec bascule mensuel/annuel animée
et comparatif détaillé · FAQ en accordéon · contact AJAX · CTA final · pied de page 5 colonnes.

### Console opérationnelle (`/app`)
KPIs et graphiques du tableau de bord · création/application/annulation d'affectations ·
numérotation automatique `AFF-AAAA-NNNN` · contrôle des dates et des lieux ·
suivi kanban du pipeline · gestion des agents et de leurs fiches · référentiel des lieux
avec taux de remplissage · historique chronologique groupé par mois · journal d'audit ·
rapports (couverture, motifs, flux inter-provinces) · rapport annuel imprimable ·
export CSV · détection des agents non affectés.

### Administration (`/admin`)
Vue d'ensemble technique · gestion des comptes et des rôles · boîte de réception des messages
avec suivi de statut · configuration du site (identité, SEO, contact, options).

### Sécurité
PDO + requêtes préparées · jetons CSRF vérifiés par middleware · sessions `HttpOnly`/`SameSite=Lax`
avec régénération périodique · hachage bcrypt/argon2 · limitation de débit (contact, connexion) ·
anti-spam par pot de miel, délai minimal et quota par IP · échappement systématique (`e()`) ·
en-têtes CSP, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS optionnel ·
journalisation des actions sensibles · messages d'erreur neutres en production.

### Référencement et accessibilité
Balises `title`/`description`/canonical · Open Graph et Twitter Card · JSON-LD (Organization,
SoftwareApplication, WebSite, FAQPage) · `sitemap.xml` et `robots.txt` · HTML sémantique ·
navigation clavier complète · `aria-*` sur les composants interactifs · focus visible ·
contrastes AA · lien d'évitement · `prefers-reduced-motion`.

---

## 6. Configuration (`.env`)

| Variable | Rôle | Défaut |
| --- | --- | --- |
| `APP_ENV`, `APP_DEBUG` | Environnement et affichage des erreurs détaillées | `production`, `false` |
| `APP_URL` | URL publique (canonical, sitemap, redirections) | `http://localhost:8080` |
| `DB_DRIVER` | `mysql` (production) ou `sqlite` (démo locale) | `mysql` |
| `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD` | Connexion MySQL | `db`, `affecta` |
| `SESSION_SECURE`, `SESSION_SAMESITE`, `HSTS_ENABLED` | Durcissement des sessions et HTTPS | `false`, `Lax`, `false` |
| `ALLOW_IFRAME_EMBED` | Autorise l'affichage encadré (aperçu local uniquement) | `false` |
| `CONTACT_RECIPIENT`, `CONTACT_RATE_LIMIT`, `CONTACT_MIN_DELAY` | Contact et anti-spam | `contact@affecta.dev`, `5`, `3` |
| `MAIL_DRIVER` | `log` (écrit dans `storage/logs`) ou `smtp` | `log` |
| `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD` | Compte créé par le seeder | `admin@affecta.dev` |

Le fichier `.env` n'est **jamais** committé ; `.env.example` documente chaque clé.

---

## 7. Performance

* CSS critique en deux feuilles, JS en `defer`, aucune bibliothèque non utilisée.
* GSAP et ScrollTrigger auto-hébergés (aucun CDN tiers) et chargés uniquement sur le site public.
* Animations restreintes à `transform` et `opacity` ; `will-change` appliqué avec parcimonie.
* Polices `woff2` préchargées, sous-ensembles latins uniquement (≈ 24 Ko par graisse).
* Images SVG vectorielles (~4 Ko), `loading="lazy"` et `decoding="async"` sur les visuels de section.
* Requêtes SQL indexées et paginées ; graphiques en CSS (aucun canvas superflu).
* Objectif tenu : interactions à 60 FPS sur desktop, rendu immédiat sur mobile 4G.

---

## 8. Tests et qualité

```bash
php bin/seed.php --force            # base de démonstration propre (réarme aussi les quotas)
php tests/smoke.php                 # parcours HTTP complet (public, auth, console, admin) → 49 contrôles
php tests/audit.php                 # audit statique : classes CSS, icônes, variables de vue
php bin/migrate.php --status        # schéma à jour ?
find app views -name '*.php' -exec php -l {} \;   # vérification syntaxique
```

Le script de fumée vérifie : le rendu des pages publiques, l'authentification, la console et
l'administration, les codes 404/419, l'export CSV, la validation du formulaire de contact,
l'anti-spam et les en-têtes de sécurité. L'audit statique garantit qu'aucune vue n'utilise
une classe CSS, une icône ou une variable inexistante.

---

## 9. Documentation

| Document | Contenu |
| --- | --- |
| [`docs/INSTALLATION.md`](docs/INSTALLATION.md) | Déploiement pas à pas : Docker, serveur classique, production |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Noyau, cycle de requête, schéma de données, conventions |
| [`docs/SECURITE.md`](docs/SECURITE.md) | Mesures en place, durcissement, exploitation, sauvegardes |

---

## 10. Licence

Projet propriétaire. Toute reproduction ou diffusion sans autorisation écrite est interdite.
Les données saisies par un client demeurent sa propriété exclusive.
