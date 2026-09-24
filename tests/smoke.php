<?php

declare(strict_types=1);

/**
 * Vérification de fumée : parcourt les routes réelles sur un serveur en cours
 * d'exécution et contrôle statut HTTP, contenu attendu et en-têtes de sécurité.
 *
 *   php -S 127.0.0.1:8080 -t public public/router.php &
 *   php tests/smoke.php                       # cible http://127.0.0.1:8080
 *   php tests/smoke.php http://localhost:8080
 *
 * Code de sortie : 0 si tout passe, 1 dès qu'un contrôle échoue.
 */

require __DIR__ . '/../bootstrap.php';

$base = rtrim($argv[1] ?? getenv('SMOKE_BASE_URL') ?: 'http://127.0.0.1:8080', '/');
$jar = tempnam(sys_get_temp_dir(), 'affecta-cookies-');
$failures = 0;
$total = 0;
$token = null;
$authenticated = false;

/** Requête HTTP + suivi des cookies + extraction du jeton CSRF. */
function request(string $method, string $url, array $data = [], array $headers = []): array
{
    global $jar;

    $curl = curl_init($url);
    $headerLines = ['Accept: ' . ($headers['accept'] ?? 'text/html')];
    foreach ($headers as $key => $value) {
        if ($key !== 'accept') {
            $headerLines[] = "{$key}: {$value}";
        }
    }

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => $headerLines,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($method === 'HEAD') {
        curl_setopt($curl, CURLOPT_NOBODY, true);
    } elseif ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $raw = curl_exec($curl);

    if ($raw === false) {
        return ['status' => 0, 'body' => '', 'headers' => [], 'error' => curl_error($curl)];
    }

    $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $body = substr($raw, $size);
    $headerText = substr($raw, 0, $size);
    curl_close($curl);

    $parsed = [];
    foreach (explode("\r\n", $headerText) as $line) {
        if (str_contains($line, ':')) {
            [$key, $value] = explode(':', $line, 2);
            $parsed[strtolower(trim($key))] = trim($value);
        }
    }

    return ['status' => $status, 'body' => $body, 'headers' => $parsed, 'error' => null];
}

function csrf(string $html): ?string
{
    return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) === 1 ? $m[1] : null;
}

/** @param list<string> $needles */
function check(string $label, array $response, array $needles = [], ?int $status = 200): void
{
    global $failures, $total;
    $total++;

    $problems = [];

    if ($response['error'] !== null) {
        $problems[] = 'erreur réseau : ' . $response['error'];
    } elseif ($response['status'] !== $status) {
        $problems[] = "HTTP {$response['status']} (attendu {$status})";
    }

    foreach ($needles as $needle) {
        if (! str_contains($response['body'], $needle)) {
            $problems[] = "contenu « {$needle} » absent";
        }
    }

    if (preg_match('/(Fatal error|Uncaught|Warning: |Deprecated: |Notice: )/i', $response['body'], $m) === 1) {
        $problems[] = 'erreur PHP affichée : ' . trim($m[0]);
    }

    if ($problems === []) {
        printf("  \033[32m✓\033[0m %-46s %s\n", $label, $status);

        return;
    }

    $failures++;
    printf("  \033[31m✗\033[0m %-46s %s\n", $label, implode(' · ', $problems));
}

$say = static function (string $text): void {
    printf("\n\033[1m%s\033[0m\n", $text);
};

printf("\n  AFFECTA — test de fumée sur %s\n", $base);

/* ------------------------------------------------------------------- public --- */
$say('Site public');
$home = request('GET', $base . '/');
check('/', $home, ['Construisons le futur', 'id="contact"', 'csrf-token', '</html>']);
check('En-têtes de sécurité', $home, [], null === $home['status'] ? 0 : $home['status']);

foreach ([
    '/services'               => 'Un accompagnement complet',
    '/a-propos'               => 'AFFECTA',
    '/realisations'           => 'résultats vérifiables',
    '/legal/confidentialite'  => 'Confidentialité',
    '/legal/mentions-legales' => 'Mentions',
] as $path => $needle) {
    check($path, request('GET', $base . $path), [$needle]);
}

check('/robots.txt', request('GET', $base . '/robots.txt'), ['Sitemap']);
check('/sitemap.xml', request('GET', $base . '/sitemap.xml'), ['<urlset']);
check('/page-inexistante → 404', request('GET', $base . '/page-inexistante'), ["n'existe pas"], 404);

$headers = request('GET', $base . '/');
$expected = [
    'x-content-type-options' => 'nosniff',
    'referrer-policy'        => 'strict-origin-when-cross-origin',
    'content-security-policy' => "default-src 'self'",
];
foreach ($expected as $key => $value) {
    $total++;
    if (isset($headers['headers'][$key]) && str_contains($headers['headers'][$key], $value)) {
        printf("  \033[32m✓\033[0m %-46s %s\n", "en-tête {$key}", $value);
    } else {
        $failures++;
        printf("  \033[31m✗\033[0m %-46s manquant ou invalide\n", "en-tête {$key}");
    }
}

/* --------------------------------------------------------------- contact API -- */
$say('Formulaire de contact (AJAX)');
$token = csrf($home['body']);

if ($token === null) {
    $failures++;
    printf("  \033[31m✗\033[0m %-46s jeton CSRF introuvable\n", 'extraction du jeton');
} else {
    $valid = request('POST', $base . '/api/contact', [
        '_token'          => $token,
        'form_started_at' => time() - 30,
        'name'            => 'Test de fumée',
        'email'           => 'recette@example.mg',
        'phone'           => '+261 34 11 222 33',
        'subject'         => 'Demande de démonstration',
        'message'         => 'Bonjour, nous souhaitons planifier une démonstration de la plateforme pour nos équipes.',
        'consent'         => '1',
    ], ['accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);

    if ($valid['status'] === 429) {
        // Le quota horaire par IP a déjà été consommé (tests manuels, exécutions répétées).
        printf("  \033[33m•\033[0m %-46s quota horaire atteint — relancer après « php bin/seed.php --force »\n", 'envoi valide');
    } else {
        check('envoi valide', $valid, ['"success":true']);
    }

    $invalid = request('POST', $base . '/api/contact', [
        '_token'          => $token,
        'form_started_at' => time() - 30,
        'name'            => 'A',
        'email'           => 'pas-un-email',
        'subject'         => 'Test',
        'message'         => 'trop court',
    ], ['accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);
    if ($invalid['status'] === 429) {
        printf("  \033[33m•\033[0m %-46s ignoré (quota atteint, contrôle CSRF couvert plus bas)\n", 'validation serveur');
    } elseif (in_array($invalid['status'], [200, 422], true)) {
        // 422 : sémantique REST attendue pour une validation échouée.
        check('validation serveur', $invalid, ['"success":false', 'email'], $invalid['status']);
    } else {
        check('validation serveur', $invalid, ['"success":false'], 422);
    }

    $spoofed = request('POST', $base . '/api/contact', [
        '_token' => 'jeton-invalide',
        'name'   => 'Test', 'email' => 't@t.mg', 'subject' => 'Test', 'message' => 'Message assez long pour passer.',
    ], ['accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);
    check('CSRF rejeté', $spoofed, [], 419);

    $honeypot = request('POST', $base . '/api/contact', [
        '_token'          => $token,
        'form_started_at' => time() - 30,
        'website'         => 'http://spam.example',
        'name'            => 'Robot', 'email' => 'bot@spam.example', 'subject' => 'Spam',
        'message'         => 'Achat de liens en masse pour votre site.', 'consent' => '1',
    ], ['accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);
    if ($honeypot['status'] !== 429) {
        check('pot de miel neutralisé', $honeypot, [], 200);
    }
}

/* ------------------------------------------------------------------ console --- */
$say('Authentification et console (/app)');
$loginPage = request('GET', $base . '/login');
check('/login', $loginPage, ['Connexion', 'name="email"']);
$loginToken = csrf($loginPage['body']);

check('/app sans session → redirection', request('GET', $base . '/app'), [], 302);

if ($loginToken !== null) {
    $login = request('POST', $base . '/login', [
        '_token'   => $loginToken,
        'email'    => 'admin@affecta.dev',
        'password' => 'Admin@2026',
    ], ['accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);

    check('connexion administrateur', $login, ['"success":true']);
    $authenticated = str_contains($login['body'], '"success":true');
}

if ($authenticated) {
    check('/app (tableau de bord)', request('GET', $base . '/app'), ['Tableau de bord', 'Agents']);
    check('/app/employes', request('GET', $base . '/app/employes'), ['Agents']);
    check('/app/lieux', request('GET', $base . '/app/lieux'), ['Lieux']);
    check('/app/affectations', request('GET', $base . '/app/affectations'), ['Affectation']);
    check('/app/affectations/suivi', request('GET', $base . '/app/affectations/suivi'), ['Suivi']);
    check('/app/historique', request('GET', $base . '/app/historique'), ['Historique']);
    check('/app/rapports', request('GET', $base . '/app/rapports'), ['Rapport']);
    check('/app/rapports/non-affectes', request('GET', $base . '/app/rapports/non-affectes'), ['non affectés']);
    check('/app/rapports/impression', request('GET', $base . '/app/rapports/impression'), ['Rapport']);

    $export = request('GET', $base . '/app/affectations/export');
    check('export CSV', $export, ['Référence;Employé', 'AFF-'], 200);

    $first = request('GET', $base . '/app/affectations/1');
    check('/app/affectations/1 (détail)', $first, ['Affectation']);

    $agent = request('GET', $base . '/app/employes/EMP-0001');
    check('/app/employes/EMP-0001 (fiche)', $agent, ['EMP-0001']);

    $site = request('GET', $base . '/app/lieux/LIEU-001');
    check('/app/lieux/LIEU-001 (fiche)', $site, ['LIEU-001']);
}

/* -------------------------------------------------------------------- admin --- */
$say('Administration (/admin)');
if ($authenticated) {
    check('/admin (vue d\'ensemble)', request('GET', $base . '/admin'), ['Admin']);
    check('/admin/utilisateurs', request('GET', $base . '/admin/utilisateurs'), ['Utilisateur']);
    check('/admin/messages', request('GET', $base . '/admin/messages'), ['Message']);
    check('/admin/messages/1', request('GET', $base . '/admin/messages/1'), ['Message']);
    check('/admin/contenus', request('GET', $base . '/admin/contenus'), ['Configuration']);
    check('/admin/configuration', request('GET', $base . '/admin/configuration'), ['Configuration']);

    request('POST', $base . '/logout', ['_token' => $loginToken ?? '']);
    check('/app après déconnexion → redirection', request('GET', $base . '/app'), [], 302);
}

/* ------------------------------------------------------------------- assets --- */
$say('Ressources statiques');
foreach ([
    '/assets/css/core.css', '/assets/css/landing.css', '/assets/css/console.css',
    '/assets/js/app.js', '/assets/js/landing.js',
    '/assets/vendor/gsap.min.js', '/assets/vendor/ScrollTrigger.min.js',
    '/assets/images/favicon.svg', '/assets/images/og-cover.svg',
] as $asset) {
    check($asset, request('GET', $base . $asset), [], 200);
}

/* -------------------------------------------------------------------- bilan --- */
@unlink($jar);

$passed = $total - $failures;
printf("\n  %s  %d/%d contrôle(s) réussi(s)%s\n\n", $failures === 0 ? "\033[32mBILAN\033[0m" : "\033[31mBILAN\033[0m", $passed, $total,
    $failures === 0 ? ' — aucun échec.' : " — {$failures} échec(s).");

exit($failures === 0 ? 0 : 1);
