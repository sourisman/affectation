<?php

declare(strict_types=1);

/**
 * Routeur pour le serveur PHP intégré (`php -S 0.0.0.0:8080 -t public public/router.php`).
 * Sert directement les fichiers statiques existants et délègue le reste à index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false; // laisse le serveur intégré servir le fichier
}

require __DIR__ . '/index.php';
