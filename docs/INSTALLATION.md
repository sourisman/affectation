# Installation et mise en production

Trois scénarios sont couverts : **Docker** (recommandé), **serveur PHP classique**, et
**déploiement mutualisé** sans accès CLI. Chaque étape indique la commande exacte à exécuter.

---

## 1. Prérequis

| Composant | Version minimale | Remarque |
| --- | --- | --- |
| PHP | 8.2 (8.3 recommandé) | extensions `pdo`, `pdo_mysql`, `mbstring`, `json`, `intl` |
| MySQL / MariaDB | MySQL 8.0 / MariaDB 10.6 | `utf8mb4_unicode_ci` |
| SQLite | 3.35+ | uniquement pour la démonstration locale |
| Docker | 24 + Compose v2 | pour la pile complète |
| Composer | 2.x | **optionnel** : l'application embarque un autoloader de secours |

---

## 2. Installation avec Docker (recommandée)

```bash
# 1. Récupérer le projet
git clone <dépôt> affecta && cd affecta

# 2. Préparer l'environnement
cp .env.example .env
#   → modifier au minimum : DB_PASSWORD, DB_ROOT_PASSWORD, CONTACT_RECIPIENT, APP_URL

# 3. Construire et démarrer la pile (nginx + PHP-FPM + MySQL)
docker compose up -d --build

# 4. Créer le schéma et charger les données
docker compose exec app php bin/migrate.php
docker compose exec app php bin/seed.php

# 5. Vérification
curl -I http://localhost:8080/robots.txt     # doit répondre 200
```

Accès : site `http://localhost:8080` · console `http://localhost:8080/login` · Adminer
(profil outils) `docker compose --profile tools up -d` puis `http://localhost:8081`.

### Variables utiles de la pile

| Variable d'environnement hôte | Effet | Défaut |
| --- | --- | --- |
| `HTTP_PORT` | Port HTTP publié par nginx | `8080` |
| `ADMINER_PORT` | Port d'Adminer | `8081` |
| `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Identifiants MySQL | `affecta`, `affecta`, `affecta_secret`, `root_secret` |

### Commandes courantes

```bash
docker compose ps                       # état des services
docker compose logs -f app              # journaux PHP-FPM
docker compose exec app php bin/seed.php --force
docker compose down                     # arrêt (conserve les volumes)
docker compose down -v                  # arrêt + suppression des données
```

---

## 3. Installation sur serveur PHP classique

### 3.1 Base de données

```sql
CREATE DATABASE affecta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'affecta'@'localhost' IDENTIFIED BY 'un-mot-de-passe-solide';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES ON affecta.* TO 'affecta'@'localhost';
FLUSH PRIVILEGES;
```

### 3.2 Application

```bash
cp .env.example .env
# DB_DRIVER=mysql, DB_HOST=127.0.0.1, DB_NAME=affecta,
# DB_USERNAME=affecta, DB_PASSWORD=un-mot-de-passe-solide, APP_URL=https://affecta.exemple.mg

php bin/migrate.php        # crée les 10 tables + index + contraintes
php bin/seed.php           # optionnel : données et comptes de démonstration
```

### 3.3 Serveur web

**Nginx** — exemple minimal (la configuration complète est fournie dans `docker/nginx/default.conf`) :

```nginx
server {
    listen 443 ssl http2;
    server_name affecta.exemple.mg;
    root /var/www/affecta/public;      # impérativement le sous-dossier public/
    index index.php;

    location / { try_files $uri /index.php?$query_string; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ^~ /uploads/ { location ~ \.php$ { deny all; } }
}
```

**Apache** — virtual host avec réécriture :

```apache
<VirtualHost *:443>
    ServerName affecta.exemple.mg
    DocumentRoot /var/www/affecta/public

    <Directory /var/www/affecta/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Autoriser l'accès uniquement au front controller
    <FilesMatch "\.(env|ini|log|sqlite|sql|md)$">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

### 3.4 Permissions

```bash
chown -R www-data:www-data storage public/uploads
chmod -R 775 storage public/uploads
chmod 750 .env
```

---

## 4. Mise en production

1. **Environnement** : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`.
2. **HTTPS** : `SESSION_SECURE=true` et `HSTS_ENABLED=true` **une fois** le certificat en place.
3. **Identifiants** : changer le mot de passe administrateur après la première connexion
   (`/admin/utilisateurs`). Ne jamais conserver les comptes du seeder.
4. **Emails** : `MAIL_DRIVER=smtp` avec `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`.
   Installer PHPMailer (`composer require phpmailer/phpmailer`) si l'envoi SMTP est requis.
5. **Cron de sauvegarde** (exemple quotidien à 2 h) :

```cron
0 2 * * * mysqldump --single-transaction -u affecta -p"$DB_PASS" affecta | gzip > /backup/affecta-$(date +\%F).sql.gz
```

6. **Permissions de fichiers** : `public/uploads` et `storage/` doivent être inscriptibles par PHP ;
   tout le reste peut être en lecture seule (`chmod -R a-w app config routes views`).
7. **Rotation des journaux** : purger `storage/logs/*.log` au-delà de 90 jours.
8. **Mises à jour** : `git pull`, `php bin/migrate.php`, puis rechargement de PHP-FPM
   (`systemctl reload php8.3-fpm` ou `docker compose exec app kill -USR2 1`).

---

## 5. Hébergement mutualisé (sans CLI)

1. Téléverser le projet hors de `public_html`, puis faire pointer le domaine vers `public/`.
   Si l'hébergeur impose `public_html` comme racine, y placer le contenu de `public/`
   et corriger le chemin de `bootstrap.php` (première ligne de `public/index.php`).
2. Créer la base via l'interface (phpMyAdmin) puis importer **`database/affecta.sql`** :
   ce fichier contient le schéma MySQL complet et le jeu de démonstration, régénérable à tout
   moment avec `php bin/dump-sql.php` (ou `php bin/dump-sql.php --schema-only`).
3. Créer manuellement le compte administrateur :

```sql
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
VALUES ('Administrateur', 'admin@exemple.mg',
        '$2y$12$…',            -- hash obtenu via password_hash('mot-de-passe', PASSWORD_DEFAULT)
        'admin', 1, NOW(), NOW());
```

4. Passer `DB_DRIVER=mysql` et renseigner les identifiants fournis par l'hébergeur.

---

## 6. Vérification post-installation

```bash
php tests/smoke.php                       # toutes les routes répondent correctement
curl -sI https://votre-domaine/robots.txt | head -3
curl -s  https://votre-domaine/sitemap.xml | head -5
```

Liste de contrôle :

- [ ] `/` affiche le site public complet (hero, sections, contact).
- [ ] `/login` permet la connexion, `/app` affiche le tableau de bord.
- [ ] `/admin` n'est accessible qu'au rôle `admin` (403 pour un responsable).
- [ ] Le formulaire de contact renvoie une confirmation et un email dans `storage/logs`.
- [ ] `storage/` et `.env` ne sont **pas** accessibles depuis un navigateur.
- [ ] `SESSION_SECURE=true` et HSTS activés derrière HTTPS.

---

## 7. Dépannage

| Symptôme | Cause probable | Correction |
| --- | --- | --- |
| « Connexion à la base de données impossible » | DSN ou identifiants erronés | vérifier `.env`, tester `mysql -u … -p` |
| Page blanche / 500 | erreur PHP masquée | passer `APP_DEBUG=true` temporairement, consulter `storage/logs/app-*.log` |
| 403 sur `/admin` | compte sans rôle administrateur | `UPDATE users SET role='admin' WHERE email='…';` |
| Les styles ne s'appliquent pas | racine du serveur pointant sur la racine du projet | pointer sur `public/` |
| Jeton CSRF invalide en boucle | cookie de session refusé | vérifier `session.cookie_*`, HTTPS et `SESSION_SECURE` |
| Emails absents | driver `log` | lire `storage/logs/mail-*.log` ou configurer SMTP |
