# Sécurité

Ce document décrit les protections **réellement implémentées** dans le code, leur
emplacement, et les opérations d'exploitation attendues.

---

## 1. Injection SQL

* Accès données exclusivement via **PDO** avec `PDO::ATTR_EMULATE_PREPARES => false`
  (`app/Core/Database.php`) : les requêtes sont réellement préparées côté serveur.
* Aucune concaténation de valeur dans une requête : les valeurs passent par des
  paramètres nommés (`:id`, `:statut`).
* Les fragments dynamiques (colonne de tri, `IN (...)`) sont construits à partir de
  **listes blanches** internes, jamais depuis `$_GET`/`$_POST`.
* `App\Core\Model` limite les colonnes écrivables à `$fillable` : un champ non déclaré
  est ignoré, même s'il est présent dans la requête.

```php
// app/Models/Affectation.php
$stmt = $this->db->select(
    'SELECT a.* FROM affectations a WHERE a.statut = :statut ORDER BY a.dateAffect DESC',
    ['statut' => $statut]
);
```

**Vérification manuelle** : `sqlmap -u "https://…/app/affectations?statut=planifie" --batch`
ne doit produire aucune injection (les filtres sont typés et validés en amont).

---

## 2. XSS (cross-site scripting)

* Toute donnée affichée dans une vue passe par `e()` :
  `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
* Les messages de formulaire utilisent `textContent` côté JavaScript : aucune
  interpolation HTML dans les toasts ou les listes AJAX.
* Les contenus de configuration (accueil, SEO) sont échappés à l'affichage ;
  ils ne sont jamais interprétés comme du HTML brut.
* **Content Security Policy** (`config/app.php` → `app.security.csp`, émis par
  `SecurityHeadersMiddleware`) :

```
default-src 'self';
script-src 'self' 'unsafe-inline';      ← à resserrer par nonce en production (voir §9)
style-src  'self' 'unsafe-inline';
img-src    'self' data:;
font-src   'self';
connect-src 'self';
frame-ancestors 'none';
base-uri 'self';
form-action 'self';
object-src 'none'
```

---

## 3. CSRF

* Jeton aléatoire de 32 octets par session (`bin2hex(random_bytes(32))`), rotaté à la
  connexion et après 30 minutes d'ancienneté.
* Chaque formulaire d'écriture inclut `csrf_field()`. Les formulaires AJAX transmettent
  également l'en-tête `X-CSRF-Token`.
* Le middleware `Csrf` couvre automatiquement `POST`, `PUT`, `PATCH`, `DELETE` ;
  une requête invalide reçoit **419** (`{"success":false,…}` en JSON, message en HTML).
* Un jeton absent, altéré ou expiré est refusé : aucun repli sur un mode permissif.

---

## 4. Authentification et sessions

| Mesure | Détail |
| --- | --- |
| Hachage | `password_hash(..., PASSWORD_DEFAULT)` (bcrypt/argon2), `password_needs_rehash()` à la connexion |
| Comparaison | `password_verify()`, échec volontairement identique pour un email inexistant (anti-énumération) |
| Cannibalisation de session | `session_regenerate_id(true)` à la connexion |
| Cookies | `HttpOnly`, `SameSite=Lax`, `Secure` si `SESSION_SECURE=true`, `Path=/` |
| Verrouillage | `login_attempts` : 5 tentatives échouées → blocage temporisé par IP + email |
| Déconnexion | destruction de session + invalidation du cookie, jeton CSRF exigé |
| Rôles | `Auth` (authentifié), `Admin` (rôle `admin`) — vérifiés côté serveur, jamais côté client |
| Comptes | `is_active = 0` : connexion refusée, sans révéler la raison exacte |

---

## 5. Validation et téléversements

* `App\Services\Validator` applique des règles déclaratives et renvoie des messages
  en français, par champ : `required`, `email`, `min`, `max`, `int`, `date`, `date_after`,
  `numeric`, `in`, `unique`, `regex`, `phone`, `same`.
* Les données entrantes sont normalisées avant stockage (`trim`, `mb_strtolower` sur les
  emails, suppression des caractères de contrôle).
* `UploadService` :
  * vérifie le **type MIME réel** via `finfo` (jamais l'extension déclarée) ;
  * impose une liste blanche (`image/jpeg`, `image/png`, `image/webp`, `application/pdf`) ;
  * limite la taille (`app.upload.max_size`, 5 Mo par défaut) ;
  * réécrit le nom du fichier (`uniqid` + extension sûre) — aucun nom fourni par le client ;
  * neutralise les traversées de chemin (`../`, null bytes, chemins absolus) ;
  * interdit l'exécution dans `public/uploads` (règle nginx/Apache fournie).

---

## 6. Anti-spam du formulaire de contact

Trois barrières indépendantes, sans CAPTCHA (aucune donnée envoyée à un tiers) :

1. **Pot de miel** : champ `website` invisible ; s'il est rempli, la requête est
   silencieusement acceptée puis ignorée (le robot ne reçoit aucun signal d'échec).
2. **Délai minimal** : `form_started_at` horodate l'ouverture du formulaire ; une
   soumission en moins de `CONTACT_MIN_DELAY` secondes (3 par défaut) est rejetée.
3. **Quota par IP** : `ContactMessage::recentCountFrom($ip, 3600)` comparé à
   `CONTACT_RATE_LIMIT` (5/heure) → réponse **429**.

Le middleware `Throttle` protège en complément les points sensibles (contact, connexion).

---

## 7. En-têtes HTTP et hygiène

Émis par `SecurityHeadersMiddleware` et nginx :

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY                 (SAMEORIGIN uniquement si ALLOW_IFRAME_EMBED=true, aperçu local)
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(self), microphone=(), camera=(), payment=()
Cross-Origin-Opener-Policy: same-origin
Cache-Control: no-store, private      (espace /app et /admin)
Strict-Transport-Security: max-age=31536000; includeSubDomains   (si HSTS_ENABLED=true)
```

Hygiène complémentaire : `expose_php=Off`, `display_errors=Off`, `allow_url_fopen=Off`,
`error_log` dans `storage/logs`, `.env` et `storage/` hors racine web, exécution PHP
interdite dans `public/uploads`, fichiers `.sqlite`/`.log`/`.sql` jamais servis.

---

## 8. Journalisation et traçabilité

* `ActivityLog::record()` trace les actions sensibles : connexion/déconnexion, création,
  application, annulation d'affectation, modification d'un agent ou d'un lieu,
  changement de rôle, mise à jour de la configuration, envoi de message.
* Chaque entrée conserve : utilisateur, action, entité, identifiant, adresse IP, agent
  utilisateur, horodatage. Consultable dans `/app/historique` et `/admin`.
* Les erreurs applicatives sont écrites dans `storage/logs/app-AAAA-MM-JJ.log`
  (jamais affichées à l'utilisateur si `APP_DEBUG=false`).

---

## 9. Durcissement en production — liste de contrôle

- [ ] `APP_ENV=production` et `APP_DEBUG=false`.
- [ ] `.env` avec `chmod 600`, hors dépôt Git (vérifié : `.gitignore`).
- [ ] HTTPS activé, puis `SESSION_SECURE=true` **et** `HSTS_ENABLED=true`.
- [ ] `ALLOW_IFRAME_EMBED=false` (valeur par défaut).
- [ ] Mots de passe des comptes du seeder changés, comptes de démonstration supprimés.
- [ ] Composer installé avec `--no-dev` ; `vendor/` hors racine web.
- [ ] Remplacer `'unsafe-inline'` par un **nonce CSP** (`script-src 'self' 'nonce-…'`)
      dès que la génération de balises script est centralisée dans le layout.
- [ ] Sauvegardes quotidiennes chiffrées et **restauration testée** au moins une fois.
- [ ] Journalisation centralisée (fail2ban sur les 401/429, alerte sur 500).
- [ ] Mises à jour de PHP/MySQL appliquées sous 30 jours après publication.

---

## 10. Réponse à incident

1. **Isoler** : bloquer l'IP hostile au niveau nginx, suspendre le compte compromis
   (`UPDATE users SET is_active = 0`).
2. **Évaluer** : lire `activity_logs` et `storage/logs/app-*.log` sur la fenêtre concernée.
3. **Révoquer** : régénérer les secrets (`SESSION_*`, identifiants SMTP, mot de passe base),
   forcer la déconnexion globale (purge de `storage/sessions`).
4. **Corriger** : appliquer le correctif, redéployer, revérifier `tests/smoke.php`.
5. **Documenter** : consigner chronologie, cause racine et actions correctives.
6. **Notifier** : informer les personnes concernées si des données personnelles sont touchées,
   conformément aux obligations applicables.
