<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;

/**
 * Ajoute les en-têtes de sécurité HTTP à chaque réponse.
 *
 * Le durcissement est piloté par la configuration (.env) : en production,
 * l'application refuse d'être encadrée par un site tiers (anti-clickjacking).
 * En développement, `ALLOW_IFRAME_EMBED` permet l'affichage dans un aperçu
 * intégré sans affaiblir les autres protections.
 */
final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        $embed = (bool) Config::get('app.security.allow_embed', false);
        $csp = (string) Config::get('app.security.csp');

        if ($embed) {
            // Aperçu intégré (développement) : seul l'encadrement est assoupli,
            // le reste de la politique de sécurité reste inchangé.
            $csp = (string) preg_replace('/frame-ancestors[^;]+;/', 'frame-ancestors *;', $csp);
        }

        $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'geolocation=(self), microphone=(), camera=(), payment=()')
            ->withHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->withHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->withHeader('Content-Security-Policy', $csp);

        if (! $embed) {
            // Par défaut : l'application refuse d'être encadrée (anti-clickjacking).
            $response->withHeader('X-Frame-Options', 'DENY');
        }

        if ((bool) Config::get('app.security.hsts', false)) {
            $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Les pages authentifiées ne doivent jamais être mises en cache par un intermédiaire.
        $path = $request->path();
        if ($path === '/admin' || str_starts_with($path, '/admin/') || str_starts_with($path, '/app')) {
            $response->withHeader('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
