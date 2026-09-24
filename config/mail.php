<?php

declare(strict_types=1);

use App\Core\Env;

return [
    /*
     | log  : écrit les emails dans storage/logs/mail-*.log (développement, démo)
     | smtp : envoi réel via SMTP lorsque les identifiants sont fournis
     */
    'driver'     => (string) Env::get('MAIL_DRIVER', 'log'),
    'host'       => (string) Env::get('MAIL_HOST', 'smtp.example.com'),
    'port'       => (int) Env::get('MAIL_PORT', 587),
    'username'   => (string) Env::get('MAIL_USERNAME', ''),
    'password'   => (string) Env::get('MAIL_PASSWORD', ''),
    'encryption' => (string) Env::get('MAIL_ENCRYPTION', 'tls'),
    'from'       => [
        'address' => (string) Env::get('MAIL_FROM_ADDRESS', 'no-reply@affecta.dev'),
        'name'    => (string) Env::get('MAIL_FROM_NAME', 'AFFECTA'),
    ],
];
