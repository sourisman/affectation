<?php

declare(strict_types=1);

use App\Core\Env;

return [
    /*
     | mysql  : production (MySQL 8 / MariaDB 10+)
     | sqlite : exécution locale sans serveur (démo, tests, CI)
     */
    'driver'   => (string) Env::get('DB_DRIVER', 'mysql'),

    'host'     => (string) Env::get('DB_HOST', '127.0.0.1'),
    'port'     => (int) Env::get('DB_PORT', 3306),
    'name'     => (string) Env::get('DB_NAME', 'affecta'),
    'username' => (string) Env::get('DB_USERNAME', 'affecta'),
    'password' => (string) Env::get('DB_PASSWORD', ''),
    'socket'   => Env::get('DB_SOCKET'),

    'sqlite_path' => BASE_PATH . '/storage/affecta.sqlite',

    'charset'  => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
