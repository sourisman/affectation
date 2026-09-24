<?php

declare(strict_types=1);

/**
 * Amorçage partagé : utilisé par le front controller (public/index.php)
 * et par les scripts CLI (bin/migrate.php, bin/seed.php).
 *
 * Enregistre un autoloader PSR-4 de secours afin que l'application démarre
 * même avec un dossier vendor/ absent ou obsolète.
 */

use App\Core\Application;

$basePath = __DIR__;

if (is_file($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $class) use ($basePath): void {
    $prefixes = [
        'App\\'      => $basePath . '/app/',
        'Database\\' => $basePath . '/database/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $file = $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

        if (is_file($file)) {
            require_once $file;

            return;
        }
    }
});

return Application::boot($basePath);
