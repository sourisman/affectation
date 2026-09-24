<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Noyau applicatif : amorçage, filtrage de la requête, rendu des erreurs.
 */
final class Application
{
    private static ?self $instance = null;

    private Router $router;

    private float $startedAt;

    private function __construct(private readonly string $basePath)
    {
        $this->startedAt = microtime(true);
        $this->router = new Router();
    }

    public static function boot(string $basePath): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $basePath = rtrim($basePath, '/');

        // BASE_PATH est consommé par les fichiers de configuration : défini en premier.
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $basePath);
        }

        // Helpers globaux : chargés explicitement pour rester disponibles
        // même lorsque l'autoloader Composer est obsolète ou absent.
        require_once $basePath . '/app/Helpers/functions.php';

        Env::load($basePath . '/.env');
        Config::loadDirectory($basePath . '/config');

        date_default_timezone_set((string) Config::get('app.timezone', 'Indian/Antananarivo'));
        mb_internal_encoding('UTF-8');

        View::configure($basePath . '/views');
        self::registerAutoloader($basePath);

        $app = new self($basePath);
        self::$instance = $app;

        Session::start();

        return $app;
    }

    public static function instance(): self
    {
        return self::$instance ?? throw new \RuntimeException('Application non initialisée.');
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    /** Charge les routes puis exécute la requête courante. */
    public function run(): void
    {
        // Le serveur PHP intégré doit servir les fichiers statiques directement.
        (require $this->basePath('/routes/web.php'))($this->router);

        $request = Request::capture();

        try {
            $response = $this->router->dispatch($request);

            if (!$response instanceof Response) {
                $response = $this->notFound($request);
            }
        } catch (Throwable $e) {
            $response = $this->handleException($e, $request);
        }

        // Les pages 404, 405 et 500 doivent elles aussi porter les en-têtes de sécurité.
        $response = (new \App\Middleware\SecurityHeadersMiddleware())->handle($request, static fn (): Response => $response);

        $response->withHeader('X-Response-Time', round((microtime(true) - $this->startedAt) * 1000, 2) . 'ms');
        $response->send();
    }

    public function terminate(int $status = 0): void
    {
        exit($status);
    }

    private function notFound(Request $request): Response
    {
        if ($request->wantsJson() || str_starts_with($request->path(), '/api/')) {
            return Response::json([
                'success' => false,
                'message' => 'Ressource introuvable.',
            ], 404);
        }

        if (!View::exists('errors.404')) {
            return Response::text('404 — Page introuvable', 404);
        }

        return Response::html(View::make('errors.404')->layout('layouts.public')->render(), 404);
    }

    private function handleException(Throwable $e, Request $request): Response
    {
        $debug = (bool) Config::get('app.debug', false);

        $this->log($e);

        if ($request->wantsJson() || str_starts_with($request->path(), '/api/')) {
            return Response::json([
                'success' => false,
                'message' => $debug ? $e->getMessage() : 'Une erreur interne est survenue. Merci de réessayer.',
            ], 500);
        }

        $message = $debug
            ? $e->getMessage() . ' — ' . $e->getFile() . ':' . $e->getLine()
            : null;

        if (!View::exists('errors.500')) {
            return Response::text('500 — Erreur interne', 500);
        }

        return Response::html(
            View::make('errors.500', ['detail' => $message])->layout('layouts.public')->render(),
            500,
        );
    }

    private function log(Throwable $e): void
    {
        $directory = $this->basePath('/storage/logs');

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
        );

        @file_put_contents($directory . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
        @error_log($e::class . ': ' . $e->getMessage());
    }

    private static function registerAutoloader(string $basePath): void
    {
        // Composer si présent, sinon autoloader PSR-4 minimal (zéro dépendance requise).
        if (is_file($basePath . '/vendor/autoload.php')) {
            require_once $basePath . '/vendor/autoload.php';

            if (class_exists(\Composer\Autoload\ClassLoader::class)) {
                return;
            }
        }

        spl_autoload_register(static function (string $class) use ($basePath): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }

            $relative = str_replace('\\', '/', substr($class, 4));
            $file = $basePath . '/app/' . $relative . '.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
