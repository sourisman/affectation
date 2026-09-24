<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Représentation immuable de la requête HTTP entrante.
 */
final class Request
{
    /** @param array<string, mixed> $query @param array<string, mixed> $body @param array<string, mixed> $files @param array<string, string> $headers */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $files = [],
        private readonly array $headers = [],
        private readonly string $ip = '0.0.0.0',
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');

        if ($path === '') {
            $path = '/';
        }

        $body = $_POST;

        // Corps JSON (fetch API) : /api/*
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (str_contains(strtolower((string) $contentType), 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        // Méthode spoofée pour les formulaires HTML : <input name="_method" value="PUT">
        if ($method === 'POST' && isset($body['_method'])) {
            $spoofed = strtoupper((string) $body['_method']);

            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofed;
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with((string) $key, 'HTTP_')) {
                $name = str_replace('_', '-', substr((string) $key, 5));
                $headers[strtolower($name)] = (string) $value;
            }
        }

        return new self(
            $method,
            $path,
            $_GET,
            $body,
            $_FILES,
            $headers,
            self::resolveIp(),
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** Détecte les appels AJAX/Fetch pour répondre en JSON plutôt qu'en redirection. */
    public function wantsJson(): bool
    {
        $accept = strtolower($this->header('accept') ?? '');
        $requestedWith = strtolower($this->header('x-requested-with') ?? '');

        return str_contains($accept, 'application/json')
            || $requestedWith === 'xmlhttprequest'
            || str_starts_with($this->path, '/api/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(string ...$keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }

        return $result;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }

    public function boolean(string $key): bool
    {
        $value = $this->input($key, false);

        return is_bool($value) ? $value : in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK ? $file : null;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function ip(): string
    {
        return $this->ip;
    }

    public function userAgent(): string
    {
        return substr($this->header('user-agent') ?? '', 0, 255);
    }

    private static function resolveIp(): string
    {
        // Derrière un reverse-proxy (nginx, Traefik, Cloudflare).
        $candidates = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($candidates as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', (string) $_SERVER[$key])[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
