<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Sessions sécurisées : cookie HttpOnly/SameSite + régénération périodique de l'ID.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name((string) Config::get('app.session_name', 'affecta_session'));

        [$secure, $sameSite] = self::cookiePolicy();

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => $sameSite,
        ]);
        session_start();

        // Anti fixation de session : nouvel identifiant toutes les 30 minutes.
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - (int) $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /** Message flash consommé au prochain affichage. */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type:string,message:string}> */
    public static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return is_array($flashes) ? $flashes : [];
    }

    /** Mémorise les anciennes valeurs d'un formulaire en erreur. */
    public static function flashInput(array $input): void
    {
        unset($input['password'], $input['password_confirmation'], $input['_token']);
        $_SESSION['_old'] = $input;
    }

    public static function oldInput(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        return is_array($old) ? $old : [];
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        $session = $_SESSION['_csrf'] ?? null;

        return is_string($token) && is_string($session) && hash_equals($session, $token);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name() ?: 'affecta_session', '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
    }

    /**
     * Politique de cookie de session.
     *
     * Par défaut : `Lax` + `Secure` selon la configuration. Lorsqu'un aperçu
     * encadré est autorisé (ALLOW_IFRAME_EMBED) et que la requête arrive en
     * HTTPS, `SameSite=None; Secure` est nécessaire : dans une iframe tierce,
     * un cookie `Lax` ne serait jamais transmis et la session serait perdue.
     *
     * @return array{0:bool,1:string}
     */
    private static function cookiePolicy(): array
    {
        $secure = (bool) Config::get('app.session_secure', false);
        $sameSite = (string) Config::get('app.session_samesite', 'Lax');

        if ((bool) Config::get('app.security.allow_embed', false) && self::isSecureRequest()) {
            return [true, 'None'];
        }

        return [$secure, $sameSite];
    }

    /** Détecte HTTPS, y compris derrière un reverse-proxy (aperçu, load balancer). */
    private static function isSecureRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }
}
