<?php

declare(strict_types=1);

/**
 * Données de démonstration.
 *
 *   php bin/seed.php          insère les données si la base est vide
 *   php bin/seed.php --force  rejoue le seeder (purge les tables métier)
 *
 * Les comptes de démonstration sont affichés une seule fois à l'exécution.
 * Aucun mot de passe n'est stocké en clair : tout passe par password_hash().
 */

use App\Core\Database;
use Database\Seeders\DatabaseSeeder;

require dirname(__DIR__) . '/bootstrap.php';

$force = in_array('--force', $argv ?? [], true);

try {
    $result = (new DatabaseSeeder(Database::connection()))->run($force);
} catch (Throwable $e) {
    fwrite(STDERR, '  ✗ ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

print('▶ AFFECTA — données de démonstration' . PHP_EOL);

if ($result['skipped'] === true) {
    print('  • Base déjà peuplée : rien à faire (utilisez --force pour rejouer).' . PHP_EOL);
    exit(0);
}

foreach ($result['counts'] as $table => $count) {
    printf('  ✓ %-18s %d ligne(s)%s', $table, $count, PHP_EOL);
}

print(PHP_EOL . '  Comptes de démonstration :' . PHP_EOL);
foreach ($result['accounts'] as $account) {
    printf('    • %-22s %-22s %s%s', $account['role'], $account['email'], $account['password'], PHP_EOL);
}
print('  ⚠ Ces identifiants sont destinés à la démonstration : changez-les avant mise en production.' . PHP_EOL);

exit(0);
