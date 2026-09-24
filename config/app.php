<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name'        => (string) Env::get('APP_NAME', 'AFFECTA'),
    'tagline'     => (string) Env::get('APP_TAGLINE', 'Construisons le futur du digital.'),
    'env'         => (string) Env::get('APP_ENV', 'production'),
    'debug'       => Env::bool('APP_DEBUG', false),
    'url'         => rtrim((string) Env::get('APP_URL', 'http://localhost:8080'), '/'),
    'timezone'    => (string) Env::get('APP_TIMEZONE', 'Indian/Antananarivo'),
    'locale'      => (string) Env::get('APP_LOCALE', 'fr'),
    'asset_version' => (string) Env::get('ASSET_VERSION', '1.0.0'),

    // Sécurité
    'session_name'    => (string) Env::get('SESSION_NAME', 'affecta_session'),
    'session_secure'   => Env::bool('SESSION_SECURE', false),
    'session_samesite' => (string) Env::get('SESSION_SAMESITE', 'Lax'),
    'trusted_proxies' => array_filter(explode(',', (string) Env::get('TRUSTED_PROXIES', ''))),

    'security' => [
        'csp' => (string) Env::get(
            'CSP_POLICY',
            "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' data: blob:; font-src 'self' data:; "
            . "connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'",
        ),
        'hsts' => Env::bool('HSTS_ENABLED', false),
        // Autorise l'affichage en iframe (aperçu local uniquement).
        'allow_embed' => Env::bool('ALLOW_IFRAME_EMBED', false),
    ],

    // Contact & anti-spam
    'contact' => [
        'recipient'     => (string) Env::get('CONTACT_RECIPIENT', 'contact@affecta.dev'),
        'rate_limit'    => (int) Env::get('CONTACT_RATE_LIMIT', 5),
        'rate_window'   => (int) Env::get('CONTACT_RATE_WINDOW', 3600),
        'min_delay'     => (int) Env::get('CONTACT_MIN_DELAY', 3),
    ],

    // Société (affiché dans le footer / données structurées)
    'company' => [
        'legal_name' => (string) Env::get('COMPANY_NAME', 'AFFECTA SAS'),
        'email'      => (string) Env::get('COMPANY_EMAIL', 'contact@affecta.dev'),
        'phone'      => (string) Env::get('COMPANY_PHONE', '+261 34 00 000 00'),
        'address'    => (string) Env::get('COMPANY_ADDRESS', 'Immeuble Skyline, Antananarivo 101, Madagascar'),
        'city'       => (string) Env::get('COMPANY_CITY', 'Antananarivo'),
        'country'    => (string) Env::get('COMPANY_COUNTRY', 'Madagascar'),
        'social'     => [
            'linkedin'  => (string) Env::get('SOCIAL_LINKEDIN', 'https://www.linkedin.com/company/affecta'),
            'github'    => (string) Env::get('SOCIAL_GITHUB', 'https://github.com/affecta'),
            'x'         => (string) Env::get('SOCIAL_X', 'https://x.com/affecta'),
            'youtube'   => (string) Env::get('SOCIAL_YOUTUBE', 'https://www.youtube.com/@affecta'),
        ],
    ],

    'upload' => [
        'max_size'  => (int) Env::get('UPLOAD_MAX_SIZE', 5_242_880),
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'application/pdf'],
        'path'      => BASE_PATH . '/public/uploads',
    ],
];
