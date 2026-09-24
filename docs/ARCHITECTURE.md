# Architecture technique

## 1. Vue d'ensemble

AFFECTA est une application **MVC sans framework** : le noyau tient en dix classes
lisibles, sans magie ni dépendance obligatoire. Ce choix garantit la maintenabilité
sur dix ans, la portabilité (aucune version majeure à suivre) et la maîtrise complète
du cycle de requête.

```text
Navigateur
   │  HTTPS
   ▼
nginx / Apache ──► public/index.php (front controller unique)
                        │
                        ├─ bootstrap.php  → Env (.env) · Config · View · Session
                        │
                        ├─ routes/web.php → Router + middlewares déclarés
                        │
                        ▼
                   Controller (App\Controllers\…)
                        │        │
                        │        └──► Validator (règles métier + messages)
                        ▼
                   Services (AffectationService, Mailer, UploadService)
                        │
                        ▼
                   Models (PDO, requêtes préparées) ──► MySQL / SQLite
                        │
                        ▼
                   View (layouts + composants) ──► HTML
```

## 2. Cycle de vie d'une requête

1. **`public/index.php`** durcit l'environnement PHP (erreurs masquées, sessions strictes)
   et charge `bootstrap.php`.
2. **`bootstrap.php`** charge Composer s'il existe, enregistre un autoloader PSR-4 de
   secours (`App\` → `app/`, `Database\` → `database/`), puis appelle `Application::boot()`.
3. **`Application::boot()`** définit `BASE_PATH`, charge `.env`, puis tous les fichiers de
   `config/`, configure le fuseau horaire et démarre la session.
4. **`Application::run()`** charge `routes/web.php`, capture la `Request` et confie le
   dispatching au `Router`.
5. **Router** fait correspondre chemin + méthode HTTP, empile les middlewares
   (`security` → `auth` → `admin` → `csrf` → `throttle` selon la route) puis exécute
   l'action du contrôleur.
6. **Contrôleur** valide les entrées (`Validator`), appelle les services et modèles,
   et renvoie une `Response` (HTML, JSON ou fichier).
7. **`Response::send()`** émet le code HTTP, les en-têtes puis le corps. Toute exception
   non rattrapée est journalisée dans `storage/logs/app-AAAA-MM-JJ.log` et rendue en
   page 500 (détail technique uniquement si `APP_DEBUG=true`).

## 3. Noyau (`app/Core`)

| Classe | Responsabilité |
| --- | --- |
| `Env` | Lecture de `.env`, sans écraser les variables du système (Docker/CI) |
| `Config` | Registre clé/valeur avec notation pointée : `config('app.name')` |
| `Request` | Requête immuable : méthode, chemin, entrées, fichiers, en-têtes, IP, détection AJAX/JSON |
| `Response` | Fabriques `html`, `json`, `text`, `download`, `redirect` + en-têtes personnalisés |
| `Session` | Démarrage sécurisé, flashes, données de formulaire, jeton CSRF, destruction |
| `Router` | Routes paramétrées `{id}`, groupes préfixés/middlewares, réponse 405 explicite |
| `View` | Rendu de templates, layouts, sections, piles de scripts, échappement `e()` |
| `Database` | Connexion PDO unique (MySQL/SQLite), helpers `select`, `scalar`, `insert`, `transaction` |
| `Model` | CRUD en requêtes préparées, filtrage par `$fillable`, `exists`, `paginate` côté contrôleur |
| `Schema` | Constructeur de schéma portable (une migration → SQL MySQL **et** SQLite) |
| `Migrator` | Exécution idempotente des migrations + table de suivi + `--fresh` |
| `Application` | Amorçage, chargement des routes, gestion centralisée des erreurs |

## 4. Schéma de données (10 tables)

```
users ──────────┐
  id, name,     │
  email (u),    │ created_by
  password,     ▼
  role,       affectations ──► employes ──► lieux
  is_active     id            numEmp (PK)   idlieu (PK)
                numAffect(u)  civilite      design
password_resets nomEmp        nom, prenom   province
login_attempts  ancienLieu    mail          code_analytique
settings        nouveauLieu   telephone     capacite
contact_messages dateAffect   poste         is_active
activity_logs   datePriseService  lieu (FK)  timestamps
                motif, observation
                statut, created_by
```

**Règles portées par la base**
`affectations` : `CHECK (datePriseService >= dateAffect)`, `CHECK (ancienLieu <> nouveauLieu)`,
clés étrangères `ON DELETE RESTRICT` vers `employes` et `lieux` (intégrité de l'historique),
`created_by` en `ON DELETE SET NULL` (la suppression d'un compte ne détruit pas la traçabilité).

**Index** : `affectations(numAffect)` unique, `(numEmp)`, `(statut)`, `(dateAffect)` ;
`employes(mail)`, `(lieu)`, `(nom, prenom)` ; `lieux(province)`, `(design)` ; `contact_messages(status)`.

**Statuts d'une affectation**

| Statut | Signification | Transition |
| --- | --- | --- |
| `planifie` | Dossier créé, date de prise de service à venir | → `applique` (transfert de l'agent) ou → `annule` |
| `applique` | Décision effective, `employes.lieu` mis à jour | → `annule` (retour au lieu d'origine) |
| `annule` | Dossier clos, historique conservé | terminal (lecture seule) |

## 5. Arborescence applicative

| Dossier | Contenu | Règle |
| --- | --- | --- |
| `app/Controllers` | Une classe par domaine, une méthode par action | aucune requête SQL directe |
| `app/Controllers/Admin` | Console métier (`/app`) et administration (`/admin`) | un contrôleur par entité |
| `app/Models` | Accès aux données et requêtes métier réutilisables | requêtes préparées uniquement |
| `app/Services` | Validation, règles métier, email, upload | sans état, testables isolément |
| `app/Middleware` | Authentification, rôles, CSRF, débit, en-têtes | un middleware = une responsabilité |
| `views/layouts` | `public`, `console`, `admin`, `print` | aucune logique métier |
| `views/components` | Fragments réutilisables (navigation, pied de page, pagination, statuts) | paramètres passés par variables |
| `views/components/sections` | Sections du site public | composables dans `pages/home.php` |
| `public/assets` | CSS, JS, polices, images | servis statiquement, versionnés par `asset()` |

## 6. Conventions de code

* `declare(strict_types=1);` dans tous les fichiers PHP.
* Classes `final` par défaut, dépendances injectées par constructeur, propriétés typées.
* Nommage : `snake_case` pour les colonnes SQL, `camelCase` pour les méthodes,
  `PascalCase` pour les classes ; messages et commentaires en français.
* Toute sortie utilisateur passe par `e()` (échappement `htmlspecialchars`).
* Toute écriture passe par une validation `Validator` ; les erreurs sont renvoyées
  par champ pour un affichage précis dans les formulaires AJAX.
* Les réponses d'action sont uniformes : `{ success, message, errors?, redirect? }`.
* Un fichier CSS par couche (`core`, `landing`, `console`) ; aucun style en ligne
  en dehors de variables de positionnement calculées par JavaScript.

## 7. Front-end

| Couche | Fichier | Rôle |
| --- | --- | --- |
| Jetons et composants | `css/core.css` | thèmes sombre/clair, typographie, boutons, cartes, formulaires, tableaux, toasts |
| Site public | `css/landing.css` | navigation, hero, sections narratives, tarifs, FAQ, contact, pied de page |
| Console | `css/console.css` | coquille, barre latérale, KPIs, graphiques, kanban, modales, impression |
| Noyau JS | `js/app.js` | thème, révélations, compteurs, graphiques, toasts, formulaires AJAX, modales, onglets, carrousel, tarifs |
| Animations | `js/landing.js` | chargeur, curseur, navigation, entrée du hero, parallaxes, timeline, magnétisme |

Le thème est appliqué par `data-theme` sur `<html>` ; la préférence est stockée dans
`localStorage` et, à défaut de choix explicite, suit `prefers-color-scheme`.
Chaque module JavaScript s'active uniquement si les éléments concernés existent :
aucun code inutile n'est exécuté sur une page donnée.

## 8. Extensions prévues

* **Notifications par email** : brancher le driver SMTP de `Mailer` (PHPMailer déjà prévu).
* **Export PDF serveur** : intégrer mPDF (`composer require mpdf/mpdf`) dans `RapportController::print`.
* **API publique** : les routes `/api/*` renvoient déjà du JSON ; ajouter une authentification
  par jeton dans un middleware dédié (`ApiTokenMiddleware`).
* **Journalisation détaillée** : `ActivityLog` accepte un champ `meta` JSON pour stocker
  les valeurs avant/après modification d'une entité.
