<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/** Réserve l'accès aux administrateurs (rôle + statut actif). */
final class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $user = auth_user();

        if ($user === null) {
            return (new AuthMiddleware())->handle($request, $next);
        }

        if (($user['role'] ?? '') !== 'admin' || (int) ($user['is_active'] ?? 0) !== 1) {
            if ($request->wantsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Accès refusé : privilèges administrateur requis.',
                ], 403);
            }

            return Response::html(
                \App\Core\View::make('errors.403')->layout('layouts.public')->render(),
                403,
            );
        }

        return $next($request);
    }
}
