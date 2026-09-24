<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;

/**
 * Limitation de débit basée sur l'IP (stockage fichier).
 * Protecteur anti-spam et anti-bruteforce pour les endpoints publics.
 */
final class ThrottleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ?int $maxAttempts = null,
        private readonly ?int $window = null,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $max = $this->maxAttempts ?? (int) Config::get('app.contact.rate_limit', 5);
        $window = $this->window ?? (int) Config::get('app.contact.rate_window', 3600);

        $key = hash('sha256', $request->ip() . '|' . $request->path());
        $store = $this->read($key);

        $now = time();
        $hits = array_values(array_filter($store, static fn (int $ts): bool => $ts > $now - $window));

        if (count($hits) >= $max) {
            $retryAfter = max(1, ($hits[0] + $window) - $now);

            return Response::json([
                'success' => false,
                'message' => 'Trop de tentatives. Merci de patienter quelques minutes avant de réessayer.',
                'retry_after' => $retryAfter,
            ], 429)->withHeader('Retry-After', (string) $retryAfter);
        }

        $hits[] = $now;
        $this->write($key, $hits);

        return $next($request);
    }

    /** @return list<int> */
    private function read(string $key): array
    {
        $file = $this->path($key);

        if (!is_readable($file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? array_map('intval', $data) : [];
    }

    /** @param list<int> $hits */
    private function write(string $key, array $hits): void
    {
        $directory = $this->directory();

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        @file_put_contents($this->path($key), json_encode($hits), LOCK_EX);
    }

    private function directory(): string
    {
        return BASE_PATH . '/storage/cache/throttle';
    }

    private function path(string $key): string
    {
        return $this->directory() . '/' . $key . '.json';
    }
}
