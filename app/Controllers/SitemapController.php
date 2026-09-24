<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/** Référencement : sitemap XML et directives robots.txt. */
final class SitemapController extends Controller
{
    public function index(Request $request): Response
    {
        $base = rtrim((string) config('app.url', ''), '/');
        $today = date('Y-m-d');

        $pages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/services', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => '/realisations', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/a-propos', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/legal/confidentialite', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/legal/mentions-legales', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $urls = '';
        foreach ($pages as $page) {
            $urls .= sprintf(
                "  <url>\n    <loc>%s%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
                $base,
                $page['loc'],
                $today,
                $page['changefreq'],
                $page['priority'],
            );
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . $urls
            . '</urlset>' . "\n";

        return Response::html($xml)->withHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots(Request $request): Response
    {
        $base = rtrim((string) config('app.url', ''), '/');

        $content = "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /app\n"
            . "Disallow: /admin\n"
            . "Disallow: /login\n"
            . "Disallow: /api/\n"
            . "Disallow: /uploads/\n"
            . "\n"
            . "Sitemap: {$base}/sitemap.xml\n";

        return Response::text($content);
    }
}
