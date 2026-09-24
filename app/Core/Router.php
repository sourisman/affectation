<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

/**
 * Routeur minimaliste supportant les paramètres dynamiques {id},
 * les groupes de middleware et la génération d'URL nommées.
 */
final class Router
{
    /** @var list<array{method:string,path:string,handler:callable|array,middleware:list<string>,name:?string}> */
    private array $routes = [];

    /** @var list<string> */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    public function get(string $path, callable|array $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $name, $middleware);
    }

    public function post(string $path, callable|array $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $name, $middleware);
    }

    public function put(string $path, callable|array $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $name, $middleware);
    }

    public function delete(string $path, callable|array $handler, ?string $name = null, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $name, $middleware);
    }

    public function add(string $method, string $path, callable|array $handler, ?string $name = null, array $middleware = []): self
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => rtrim($this->groupPrefix . $path, '/') ?: '/',
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
            'name'       => $name,
        ];

        return $this;
    }

    /** @param array{prefix?:string,middleware?:list<string>} $attributes */
    public function group(array $attributes, Closure $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = rtrim($previousPrefix . ($attributes['prefix'] ?? ''), '/');
        $this->groupMiddleware = array_merge($previousMiddleware, $attributes['middleware'] ?? []);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Résout la requête et renvoie la réponse, ou null si aucune route ne correspond.
     */
    public function dispatch(Request $request): ?Response
    {
        // HEAD est servi par la route GET correspondante (les en-têtes seuls sont renvoyés).
        $method = $request->method() === 'HEAD' ? 'GET' : $request->method();
        $path = $request->path();

        // Compatibilité : _method spoofé déjà géré dans Request.
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);

            if ($params === null) {
                continue;
            }

            if ($route['method'] !== $method) {
                $allowedMethods[] = $route['method'];

                continue;
            }

            return $this->run($route, $params, $request);
        }

        if ($allowedMethods !== []) {
            return Response::json([
                'success' => false,
                'message' => 'Méthode non autorisée pour cette ressource.',
            ], 405)->withHeader('Allow', implode(', ', array_unique($allowedMethods)));
        }

        return null;
    }

    /**
     * @param array{method:string,path:string,handler:callable|array,middleware:list<string>,name:?string} $route
     * @param array<string, string> $params
     */
    private function run(array $route, array $params, Request $request): Response
    {
        // Chaîne de middleware : du plus externe au plus interne.
        $pipeline = array_reverse($route['middleware']);

        $core = function (Request $request) use ($route, $params): Response {
            $handler = $route['handler'];

            if (is_array($handler)) {
                [$class, $method] = $handler;
                $controller = new $class();
                $result = $controller->{$method}($request, ...array_values($params));
            } else {
                $result = $handler($request, ...array_values($params));
            }

            return $result instanceof Response ? $result : Response::html((string) $result);
        };

        foreach ($pipeline as $middlewareName) {
            $next = $core;
            $middlewareClass = $this->resolveMiddleware($middlewareName);
            $instance = new $middlewareClass();
            $core = static fn (Request $request): Response => $instance->handle($request, $next);
        }

        return $core($request);
    }

    private function resolveMiddleware(string $name): string
    {
        if (class_exists($name)) {
            return $name;
        }

        $aliases = [
            'auth'       => \App\Middleware\AuthMiddleware::class,
            'guest'      => \App\Middleware\GuestMiddleware::class,
            'admin'      => \App\Middleware\AdminMiddleware::class,
            'csrf'       => \App\Middleware\CsrfMiddleware::class,
            'throttle'   => \App\Middleware\ThrottleMiddleware::class,
            'security'   => \App\Middleware\SecurityHeadersMiddleware::class,
        ];

        return $aliases[$name] ?? throw new \RuntimeException("Middleware inconnu : $name");
    }

    /** @return array<string, string>|null */
    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
