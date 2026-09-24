<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lecteur de fichier .env sans dépendance externe.
 * Les variables déjà présentes dans l'environnement du processus (Docker, CI)
 * ne sont jamais écrasées : le système prime sur le fichier.
 */
final class Env
{
    /** @var array<string, string|null> */
    private static array $values = [];

    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded || !is_readable($path)) {
            self::$loaded = true;

            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = self::normalize(trim($value));

            if (getenv($key) !== false) {
                self::$values[$key] = (string) getenv($key);

                continue;
            }

            self::$values[$key] = $value;
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values[$key] ?? getenv($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        return is_bool($value) ? $value : in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private static function normalize(string $value): string
    {
        if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        if (strlen($value) > 1 && str_starts_with($value, "'") && str_ends_with($value, "'")) {
            return substr($value, 1, -1);
        }

        // Supprime les commentaires en fin de ligne (valeur non quotée).
        if (($pos = strpos($value, ' #')) !== false) {
            $value = substr($value, 0, $pos);
        }

        return trim($value);
    }
}
