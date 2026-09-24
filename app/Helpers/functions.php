<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Session;
use App\Core\View;

if (!function_exists('config')) {
    /** Lit une valeur de configuration par notation pointée. */
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    /** Échappement HTML systématique (protection XSS). */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('asset')) {
    /** URL d'un asset public avec cache-busting par empreinte de version. */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $version = (string) config('app.asset_version', '1');

        return '/assets/' . $path . '?v=' . rawurlencode($version);
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    /** Valeur précédemment soumise d'un formulaire. */
    function old(string $key, mixed $default = ''): mixed
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Session::oldInput();
        }

        $value = $cache[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }
}

if (!function_exists('auth_user')) {
    /** @return array<string, mixed>|null */
    function auth_user(): ?array
    {
        $user = Session::get('auth_user');

        return is_array($user) ? $user : null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (auth_user()['role'] ?? '') === 'admin';
    }
}

if (!function_exists('view')) {
    /** @param array<string, mixed> $data */
    function view(string $template, array $data = []): View
    {
        return View::make($template, $data);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd/m/Y'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? '—' : date($format, $timestamp);
    }
}

if (!function_exists('icon')) {
    /** Icône SVG en ligne (aucune requête réseau supplémentaire). */
    /**
 * Valeur de configuration éditable depuis l'administration, avec repli sur `config()`.
 * Tolérant aux pannes : si la table n'existe pas encore, la valeur par défaut est utilisée.
 */
function setting(string $key, mixed $default = null): mixed
{
    static $available = true;

    if (! $available) {
        return $default;
    }

    try {
        $value = (new \App\Models\Setting())->get($key, null);
    } catch (\Throwable) {
        $available = false;

        return $default;
    }

    return ($value === null || $value === '') ? $default : $value;
}

/** Nom public de la marque (surchargeable dans /admin/contenus). */
function site_name(): string
{
    return (string) setting('site_name', config('app.name'));
}

/** Coordonnées publiques : configuration statique enrichie des réglages en base. */
function company_profile(): array
{
    $company = (array) config('app.company', []);

    foreach (['email' => 'site_email', 'phone' => 'site_phone', 'address' => 'site_address'] as $field => $key) {
        $value = setting($key, null);

        if (is_string($value) && $value !== '') {
            $company[$field] = $value;
        }
    }

    return $company;
}

function icon(string $name, int $size = 20, string $class = '', string $label = ''): string
    {
        return \App\Helpers\Icons::render($name, $size, $class, $label);
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : '?';
    }
}

if (!function_exists('active_class')) {
    function active_class(string $path, string $class = 'is-active'): string
    {
        $current = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');

        return str_starts_with($current, rtrim($path, '/')) && $path !== '/' ? $class : '';
    }
}

if (!function_exists('mask_email')) {
    function mask_email(string $email): string
    {
        [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '' || $user === '') {
            return '—';
        }

        $visible = mb_substr($user, 0, 2);

        return $visible . str_repeat('•', max(0, mb_strlen($user) - 2)) . '@' . $domain;
    }
}
