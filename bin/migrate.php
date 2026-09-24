<?php

declare(strict_types=1);

/**
 * Migrations de base de données.
 *
 *   php bin/migrate.php             applique les migrations en attente
 *   php bin/migrate.php --fresh     supprime les tables puis rejoue tout
 *   php bin/migrate.php --status    liste l'état des migrations
 */

use App\Core\Database;
use App\Core\Migrator;
use App\Core\Schema;

require dirname(__DIR__) . '/bootstrap.php';

$options = $argv ?? [];
$fresh = in_array('--fresh', $options, true);
$status = in_array('--status', $options, true);

$schema = new Schema(Database::driver());
$migrator = new Migrator(BASE_PATH . '/database/migrations', $schema);

$output = static fn (string $line = ''): int => print($line . PHP_EOL) ?: 0;

$output(sprintf('▶ AFFECTA — migrations (%s)', Database::driver()));

if ($status) {
    $pending = $migrator->pending();
    $output($pending === [] ? '  • Aucune migration en attente.' : '  • En attente : ' . implode(', ', $pending));
    exit(0);
}

try {
    $applied = $migrator->run($fresh);

    if ($applied === []) {
        $output('  • Base déjà à jour.');
    } else {
        foreach ($applied as $migration) {
            $output('  ✓ ' . $migration);
        }
        $output(sprintf('  → %d migration(s) appliquée(s).', count($applied)));
    }

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, '  ✗ ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
