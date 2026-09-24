<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** Empêche un utilisateur déjà connecté de revoir les écrans d'authentification. */
final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (is_array(Session::get('auth_user'))) {
            return Response::redirect('/app');
        }

        return $next($request);
    }
}
