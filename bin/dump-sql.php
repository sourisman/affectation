<?php

declare(strict_types=1);

/**
 * Génère un fichier SQL complet (schéma MySQL + données) à partir des migrations
 * et de la base courante. Utile pour les hébergements sans accès SSH/CLI.
 *
 *   php bin/dump-sql.php                      → database/affecta.sql
 *   php bin/dump-sql.php --out=/tmp/affecta.sql
 *   php bin/dump-sql.php --schema-only        → sans les données
 *   php bin/dump-sql.php --data-only          → sans les tables
 *
 * En mode schéma, aucune connexion base n'est nécessaire : les migrations sont
 * rendues par App\Core\Schema en mode « rendu » avec le driver MySQL.
 */

require __DIR__ . '/../bootstrap.php';

use App\Core\Schema;

$options = getopt('', ['out::', 'schema-only', 'data-only', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, <<<TXT
Génère un export SQL (MySQL) du schéma et/ou des données.

  --out=FICHIER   chemin de sortie (défaut : database/affecta.sql)
  --schema-only   uniquement les CREATE TABLE / CREATE INDEX
  --data-only     uniquement les INSERT
  --help          affiche cette aide

TXT);
    exit(0);
}

$schemaOnly = isset($options['schema-only']);
$dataOnly = isset($options['data-only']);
$out = is_string($options['out'] ?? null) && $options['out'] !== ''
    ? (string) $options['out']
    : BASE_PATH . '/database/affecta.sql';

$lines = [];
$now = date('Y-m-d H:i:s');

$lines[] = '-- ============================================================================';
$lines[] = '--  AFFECTA — schéma MySQL et données';
$lines[] = '--  Généré le ' . $now . ' par bin/dump-sql.php';
$lines[] = '--  Compatible MySQL 8 / MariaDB 10.6+ (utf8mb4_unicode_ci).';
$lines[] = '--';
$lines[] = '--  Comptes de démonstration (à supprimer en production) :';
$lines[] = '--    admin@affecta.dev    / Admin@2026';
$lines[] = '--    manager@affecta.dev  / Manager@2026';
$lines[] = '--';
$lines[] = '--  Import : mysql -u utilisateur -p base < database/affecta.sql';
$lines[] = '-- ============================================================================';
$lines[] = '';
$lines[] = 'SET NAMES utf8mb4;';
$lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$lines[] = '';

/* ------------------------------------------------------------------ schéma ---- */
if (! $dataOnly) {
    $schema = new Schema('mysql', renderOnly: true);

    foreach (glob(BASE_PATH . '/database/migrations/*.php') ?: [] as $file) {
        $migration = require $file;
        $migration($schema);
    }

    $lines[] = '-- ----------------------------------------------------------------------------';
    $lines[] = '--  Structure';
    $lines[] = '-- ----------------------------------------------------------------------------';

    foreach ($schema->rendered() as $statement) {
        $lines[] = $statement;
    }

    $lines[] = '';
}

/* ------------------------------------------------------------------ données --- */
if (! $schemaOnly) {
    $driver = (string) config('database.driver');

    if ($driver !== 'sqlite') {
        fwrite(STDERR, "⚠  L'export de données lit la base SQLite de démonstration.\n"
            . "   Driver actif : {$driver}. Passez DB_DRIVER=sqlite ou utilisez --schema-only.\n");
        exit(1);
    }

    $pdo = new PDO('sqlite:' . (string) config('database.sqlite_path'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $quote = static function (mixed $value): string {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'" . str_replace(['\\', "'", "\r", "\n"], ['\\\\', "\\'", '\r', '\n'], (string) $value) . "'";
    };

    // Ordre respectant les clés étrangères (enfants d'abord à la suppression).
    $tables = [
        'users'            => ['id', 'name', 'email', 'password', 'role', 'job_title', 'avatar_path', 'is_active', 'last_login_at', 'created_at', 'updated_at'],
        'settings'         => ['setting_key', 'setting_value', 'group_name', 'created_at', 'updated_at'],
        'lieux'            => ['idlieu', 'design', 'province', 'code_analytique', 'capacite', 'is_active', 'created_at', 'updated_at'],
        'employes'         => ['numEmp', 'civilite', 'nom', 'prenom', 'mail', 'telephone', 'poste', 'lieu', 'date_embauche', 'is_active', 'created_at', 'updated_at'],
        'affectations'     => ['id', 'numAffect', 'numEmp', 'ancienLieu', 'nouveauLieu', 'dateAffect', 'datePriseService', 'motif', 'observation', 'statut', 'created_by', 'created_at', 'updated_at'],
        'contact_messages' => ['id', 'name', 'email', 'phone', 'subject', 'message', 'status', 'ip', 'user_agent', 'read_at', 'created_at', 'updated_at'],
        'activity_logs'    => ['id', 'user_id', 'action', 'entity', 'entity_id', 'description', 'meta', 'ip', 'created_at'],
    ];

    $lines[] = '-- ----------------------------------------------------------------------------';
    $lines[] = '--  Données';
    $lines[] = '-- ----------------------------------------------------------------------------';

    foreach (array_reverse(array_keys($tables)) as $table) {
        $lines[] = "DELETE FROM `{$table}`;";
    }

    $lines[] = '';

    foreach ($tables as $table => $columns) {
        $rows = $pdo->query('SELECT * FROM ' . $table)->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === []) {
            continue;
        }

        // Seules les colonnes existantes sont exportées (schéma évolutif).
        $available = array_values(array_intersect($columns, array_keys($rows[0])));
        $fieldList = '`' . implode('`, `', $available) . '`';

        foreach (array_chunk($rows, 40) as $chunk) {
            $lines[] = "INSERT INTO `{$table}` ({$fieldList}) VALUES";

            $values = [];
            foreach ($chunk as $row) {
                $values[] = '  (' . implode(', ', array_map($quote, array_map(static fn ($c) => $row[$c] ?? null, $available))) . ')';
            }

            $lines[] = implode(",\n", $values) . ';';
        }

        $lines[] = '';
    }
}

$lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$lines[] = '';

$directory = dirname($out);

if (! is_dir($directory)) {
    mkdir($directory, 0775, true);
}

file_put_contents($out, implode(PHP_EOL, $lines) . PHP_EOL);

$size = (int) filesize($out);

fwrite(STDOUT, sprintf(
    "✓ Export SQL généré : %s (%s Ko, %d ligne(s))%s",
    $out,
    number_format($size / 1024, 1, ',', ' '),
    count($lines),
    PHP_EOL,
));
