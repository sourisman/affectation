<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** Réserve l'accès aux utilisateurs authentifiés. */
final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (is_array(Session::get('auth_user'))) {
            return $next($request);
        }

        if ($request->wantsJson()) {
            return Response::json([
                'success' => false,
                'message' => 'Session expirée. Merci de vous reconnecter.',
                'redirect' => '/login',
            ], 401);
        }

        Session::put('intended_url', $request->path());
        Session::flash('warning', 'Veuillez vous connecter pour continuer.');

        return Response::redirect(rtrim((string) Config::get('app.url', ''), '/') . '/login');
    }
}
