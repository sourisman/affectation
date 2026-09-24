<?php

declare(strict_types=1);

/**
 * AFFECTA — unique point d'entrée public (front controller).
 *
 * Toute la logique applicative vit hors de public/ : seule cette racine est
 * exposée par le serveur web (nginx, Apache, serveur PHP intégré).
 */

// Configuration PHP durcie pour l'exécution web.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('expose_php', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

header_remove('X-Powered-By');

/** @var \App\Core\Application $app */
$app = require dirname(__DIR__) . '/bootstrap.php';

$app->run();
