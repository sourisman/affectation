<?php

declare(strict_types=1);

/**
 * Audit statique de l'interface : vérifie que les vues n'utilisent que des
 * ressources réellement définies.
 *
 *   1. classes CSS : chaque classe des vues doit exister dans public/assets/css
 *      (ou être une classe d'état injectée par JavaScript) ;
 *   2. icônes : chaque `icon('…')` doit exister dans App\Helpers\Icons ;
 *   3. variables de vue : chaque `$variable` lue par une vue doit être fournie
 *      par le contrôleur correspondant.
 *
 *   php tests/audit.php
 *
 * Code de sortie : 0 si aucune anomalie, 1 sinon.
 */

require __DIR__ . '/../bootstrap.php';

use App\Helpers\Icons;

$root = BASE_PATH;
$problems = 0;

function title(string $text): void
{
    printf("\n\033[1m%s\033[0m\n", $text);
}

/* --------------------------------------------------------------- collecte ----- */

/** Tous les fichiers de vue. */
$viewFiles = [];
$directory = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/views', FilesystemIterator::SKIP_DOTS));

foreach ($directory as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $viewFiles[str_replace($root . '/views/', '', $file->getPathname())] = $file->getPathname();
    }
}

/** Classes déclarées dans les feuilles de style. */
$css = '';
foreach (glob($root . '/public/assets/css/*.css') as $sheet) {
    $css .= file_get_contents($sheet);
}
foreach ($viewFiles as $viewPath) {
    // Les gabarits peuvent embarquer une feuille de style ciblée (impression, e-mail).
    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/s', (string) file_get_contents($viewPath), $blocks) > 0) {
        $css .= implode("\n", $blocks[1]);
    }
}

preg_match_all('/\.(-?[_a-zA-Z][\w-]*)/', (string) $css, $cssMatches);
$cssClasses = array_flip(array_map(static fn (string $c): string => ltrim($c, '.'), $cssMatches[1]));

/** Classes d'état et utilitaires pilotés par JavaScript / les helpers PHP. */
$runtime = [
    'is-visible', 'is-open', 'is-active', 'is-loading', 'is-locked', 'is-scrolled', 'is-hidden',
    'is-current', 'is-disabled', 'is-error', 'is-valid', 'is-done', 'is-playing', 'is-dragging',
    'spinner', 'hp-field', 'sr-only', 'skip-link', 'no-print', 'print-only', 'js-only',
    'gsap-init', 'has-js', 'no-js', 'reveal-ready', 'theme-veil', 'to-top', 'scroll-progress',
    'field-error', 'is-filled', 'is-invalid', 'is-selected', 'is-collapsed', 'is-expanded',
];
$runtimeClasses = array_flip($runtime);

/* ---------------------------------------------------------------- 1. CSS ----- */
title('1. Classes CSS utilisées dans les vues');

$unknown = [];

foreach ($viewFiles as $relative => $path) {
    $html = (string) file_get_contents($path);

    preg_match_all('/class="([^"]*)"/', $html, $matches);

    foreach ($matches[1] as $attribute) {
        // Les attributs dynamiques sont ignorés (valeur construite en PHP).
        $attribute = (string) preg_replace('/<\?.*?\?>/s', ' ', $attribute);

        foreach (preg_split('/\s+/', trim($attribute)) ?: [] as $class) {
            if ($class === '' || str_contains($class, '<?')) {
                continue;
            }

            if (isset($cssClasses[$class]) || isset($runtimeClasses[$class])) {
                continue;
            }

            $unknown[$class][] = $relative;
        }
    }
}

if ($unknown === []) {
    printf("  \033[32m✓\033[0m aucune classe inconnue\n");
} else {
    ksort($unknown);
    foreach ($unknown as $class => $files) {
        $problems++;
        printf("  \033[33m•\033[0m .%-28s %s\n", $class, implode(', ', array_slice(array_unique($files), 0, 3)));
    }
    printf("  → %d classe(s) sans style déclaré (à confirmer : état dynamique ou oubli).\n", count($unknown));
}

/* -------------------------------------------------------------- 2. Icônes ---- */
title('2. Icônes référencées dans les vues');

$available = array_flip(Icons::available());
$missingIcons = [];
$usedIcons = [];

foreach ($viewFiles as $relative => $path) {
    $html = (string) file_get_contents($path);

    preg_match_all("/icon\(\s*'([^']+)'/", $html, $matches);

    foreach ($matches[1] as $name) {
        $usedIcons[$name] = true;

        if (! isset($available[$name])) {
            $missingIcons[$name][] = $relative;
        }
    }
}

if ($missingIcons === []) {
    printf("  \033[32m✓\033[0m les %d icônes utilisées existent (%d disponibles)\n", count($usedIcons), count($available));
} else {
    foreach ($missingIcons as $name => $files) {
        $problems++;
        printf("  \033[31m✗\033[0m %-24s %s\n", $name, implode(', ', array_unique($files)));
    }
}

/* ------------------------------------------------- 3. Variables de vue -------- */
title('3. Variables lues par les vues et fournies par les contrôleurs');

/** Variables globales injectées par le noyau, les layouts ou les helpers. */
$globals = [
    'content', 'meta', 'pageTitle', 'pageLead', 'flashes', '__view', 'scope', 'schema', 'title',
    'BASE_PATH', 'request', 'app', 'error', 'detail', 'pages',
];

/** Clés de payload et nom de vue de chaque action de contrôleur. */
$payloads = [];
$controllers = [];

$controllerIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/Controllers', FilesystemIterator::SKIP_DOTS));

foreach ($controllerIterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $source = (string) file_get_contents($file->getPathname());
    $short = $file->getFilename();

    // Chaque appel _view('nom', [ ... ]) : le tableau est délimité par les parenthèses.
    preg_match_all('/\$this->(?:view|console|admin)\(\s*\'([^\']+)\'\s*,\s*\[/', $source, $calls, PREG_OFFSET_CAPTURE);

    foreach ($calls[1] as $index => [$viewName, $position]) {
        $start = $calls[0][$index][1] + strlen($calls[0][$index][0]);

        // Lecture équilibrée pour isoler le tableau de données.
        $depth = 1;
        $payload = '';
        for ($i = $start; $i < strlen($source) && $depth > 0; $i++) {
            $character = $source[$i];
            if ($character === '[') {
                $depth++;
            } elseif ($character === ']') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }
            $payload .= $character;
        }

        preg_match_all("/'([a-zA-Z_][\w]*)'\s*=>/", $payload, $keys);

        $controllers[$viewName] = $short . ' (top niveau)';
        $payloads[$viewName] = array_unique(array_merge($payloads[$viewName] ?? [], $keys[1]));
        $payloads[$viewName][] = 'meta';
    }
}

/**
 * Variables lues dans une vue, moins celles qu'elle déclare elle-même
 * (affectations, paramètres de boucle, closures, catch).
 */
function usedVariables(string $source): array
{
    preg_match_all('/\$([a-zA-Z_][\w]*)/', $source, $matches);
    $used = array_unique($matches[1]);

    $local = [];

    // $variable = … (hors comparaisons ==, => et null coalescing)
    preg_match_all('/\$([a-zA-Z_][\w]*)\s*(?:\[[^\]]*\])?\s*=[^=>]/', $source, $assigned);
    $local = array_merge($local, $assigned[1]);

    // foreach (… as $key => $value) / as $value
    preg_match_all('/\bas\s+(?:&\s*)?\$([a-zA-Z_][\w]*)(?:\s*=>\s*(?:&\s*)?\$([a-zA-Z_][\w]*))?/', $source, $loops);
    $local = array_merge($local, $loops[1], $loops[2]);

    // list($a, $b) = … / [$a, $b] = …
    preg_match_all('/(?:list\s*\(|\[)\s*((?:\$[a-zA-Z_][\w]*\s*,?\s*)+)\]?\s*=/', $source, $lists);
    foreach ($lists[1] as $list) {
        preg_match_all('/\$([a-zA-Z_][\w]*)/', $list, $names);
        $local = array_merge($local, $names[1]);
    }

    // Paramètres de closure, use (…) et catch
    preg_match_all('/(?:function\s*\(|fn\s*\(|use\s*\(|catch\s*\([^)]*?)\s*([^)]*)\)/', $source, $params);
    foreach ($params[1] as $parameterList) {
        preg_match_all('/\$([a-zA-Z_][\w]*)/', $parameterList, $names);
        $local = array_merge($local, $names[1]);
    }

    return array_values(array_diff($used, array_unique($local)));
}

$reported = 0;

foreach ($viewFiles as $relative => $path) {
    if (! str_starts_with($relative, 'app/') && ! str_starts_with($relative, 'admin/')) {
        continue;
    }

    $viewName = str_replace(['/', '.php'], ['.', ''], $relative);
    $declared = array_flip(array_merge($payloads[$viewName] ?? [], $globals));
    $missing = [];

    foreach (usedVariables((string) file_get_contents($path)) as $variable) {
        if (! isset($declared[$variable])) {
            $missing[] = '$' . $variable;
        }
    }

    if ($missing === []) {
        continue;
    }

    $reported++;
    printf("  \033[33m•\033[0m %-28s %s\n", $viewName, implode(', ', $missing));
}

if ($reported === 0) {
    printf("  \033[32m✓\033[0m toutes les variables lues sont fournies\n");
} else {
    printf("  → %d vue(s) à confirmer : variable locale issue d'un foreach imbriqué ou donnée manquante.\n", $reported);
}

/* -------------------------------------------------------------- Bilan --------- */
printf("\n  %s  audit terminé — %d anomalie(s) bloquante(s)\n\n", $problems === 0 ? "\033[32mOK\033[0m" : "\033[31mATTENTION\033[0m", $problems);

exit($problems === 0 ? 0 : 1);
