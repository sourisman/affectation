<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** Vérifie le jeton CSRF sur toute requête mutante (POST, PUT, PATCH, DELETE). */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $request->input('_token')
            ?? $request->header('x-csrf-token');

        if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
            if ($request->wantsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Jeton de sécurité invalide ou expiré. Rechargez la page puis réessayez.',
                    'errors'  => ['_token' => 'CSRF invalide'],
                ], 419);
            }

            Session::flash('error', 'Session de formulaire expirée. Merci de réessayer.');
            $referer = $request->header('referer') ?: '/';

            return Response::redirect($referer);
        }

        return $next($request);
    }
}
