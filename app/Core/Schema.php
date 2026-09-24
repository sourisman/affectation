<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Constructeur de schéma portable MySQL / SQLite.
 *
 * Les migrations décrivent les tables avec cette API : le SQL généré diffère
 * selon le driver actif, ce qui permet d'exécuter la même base de code en
 * production (MySQL) et en local (SQLite, tests, CI).
 */
final class Schema
{
    /** @var list<string> Requêtes capturées en mode rendu (génération de dump SQL). */
    private array $rendered = [];

    /**
     * @param bool $renderOnly En mode rendu, aucun accès base : les requêtes sont
     *                         collectées via `rendered()` (export SQL, documentation).
     */
    public function __construct(
        private readonly string $driver,
        private readonly bool $renderOnly = false,
    ) {
    }

    /** Requêtes produites en mode rendu, dans l'ordre de déclaration. */
    public function rendered(): array
    {
        return $this->rendered;
    }

    public function driver(): string
    {
        return $this->driver;
    }

    public function isSqlite(): bool
    {
        return $this->driver === 'sqlite';
    }

    /** Table de référence : entier auto-incrémenté en clé primaire. */
    public function id(string $column = 'id'): string
    {
        return $this->isSqlite()
            ? "{$column} INTEGER PRIMARY KEY AUTOINCREMENT"
            : "{$column} BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
    }

    /** Clé étrangère entière (version simplifiée : BIGINT UNSIGNED / INTEGER). */
    public function foreignId(string $column, bool $nullable = false): string
    {
        $type = $this->isSqlite() ? 'INTEGER' : 'BIGINT UNSIGNED';

        return "{$column} {$type} " . ($nullable ? 'NULL' : 'NOT NULL');
    }

    public function string(string $column, int $length = 255, bool $nullable = false, ?string $default = null): string
    {
        $sql = "{$column} VARCHAR({$length}) " . ($nullable ? 'NULL' : 'NOT NULL');

        if ($default !== null) {
            $sql .= " DEFAULT '" . str_replace("'", "''", $default) . "'";
        }

        return $sql;
    }

    public function text(string $column, bool $nullable = false): string
    {
        return "{$column} TEXT " . ($nullable ? 'NULL' : 'NOT NULL');
    }

    public function integer(string $column, bool $nullable = false, ?int $default = null): string
    {
        $sql = "{$column} INTEGER " . ($nullable ? 'NULL' : 'NOT NULL');

        if ($default !== null) {
            $sql .= " DEFAULT {$default}";
        }

        return $sql;
    }

    public function boolean(string $column, bool $default = true): string
    {
        return $this->isSqlite()
            ? "{$column} INTEGER NOT NULL DEFAULT " . ($default ? '1' : '0')
            : "{$column} TINYINT(1) NOT NULL DEFAULT " . ($default ? '1' : '0');
    }

    public function decimal(string $column, int $precision = 10, int $scale = 2, bool $nullable = false, ?string $default = null): string
    {
        $sql = "{$column} DECIMAL({$precision},{$scale}) " . ($nullable ? 'NULL' : 'NOT NULL');

        if ($default !== null) {
            $sql .= " DEFAULT {$default}";
        }

        return $sql;
    }

    public function date(string $column, bool $nullable = false): string
    {
        return "{$column} DATE " . ($nullable ? 'NULL' : 'NOT NULL');
    }

    public function dateTime(string $column, bool $nullable = false): string
    {
        $type = $this->isSqlite() ? 'DATETIME' : 'DATETIME';

        return "{$column} {$type} " . ($nullable ? 'NULL' : 'NOT NULL');
    }

    public function timestamps(): string
    {
        // `created_at` reste figé : seul `updated_at` se met à jour automatiquement.
        return $this->timestamp('created_at', false, false) . ', ' . $this->timestamp('updated_at');
    }

    public function timestamp(string $column, bool $nullable = false, bool $autoFill = true): string
    {
        if ($this->isSqlite()) {
            return "{$column} DATETIME " . ($nullable ? 'NULL' : 'NOT NULL');
        }

        return $nullable
            ? "{$column} TIMESTAMP NULL DEFAULT NULL"
            : "{$column} TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" . ($autoFill ? ' ON UPDATE CURRENT_TIMESTAMP' : '');
    }

    public function json(string $column, bool $nullable = true): string
    {
        return $this->isSqlite()
            ? "{$column} TEXT " . ($nullable ? 'NULL' : 'NOT NULL')
            : "{$column} JSON " . ($nullable ? 'NULL' : 'NOT NULL');
    }

    public function options(): string
    {
        return $this->isSqlite()
            ? ''
            : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    }

    public function primaryKey(string ...$columns): string
    {
        return 'PRIMARY KEY (' . implode(', ', $columns) . ')';
    }

    public function foreignKey(string $column, string $table, string $reference = 'id', string $onDelete = 'RESTRICT'): string
    {
        return sprintf(
            'FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s' . ($this->isSqlite() ? '' : ' ON UPDATE CASCADE'),
            $column,
            $table,
            $reference,
            $onDelete,
        );
    }

    /** Contrainte CHECK, supportée par MySQL 8+ et SQLite. */
    public function check(string $name, string $expression): string
    {
        if ($this->isSqlite()) {
            return "CHECK ({$expression})";
        }

        return "CONSTRAINT {$name} CHECK ({$expression})";
    }

    /** @param list<string> $definitions */
    public function create(string $table, array $definitions): void
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s (%s)%s',
            $table,
            implode(', ', array_filter($definitions)),
            $this->options(),
        );

        if ($this->renderOnly) {
            $this->rendered[] = $sql . ';';

            return;
        }

        Database::connection()->exec($sql);
    }

    /** Index simple, créé séparément pour rester portable. */
    public function index(string $table, string $name, array $columns, bool $unique = false): void
    {
        $kind = $unique ? 'UNIQUE INDEX' : 'INDEX';
        $sql = sprintf(
            'CREATE %s IF NOT EXISTS %s ON %s (%s)',
            $kind,
            $name,
            $table,
            implode(', ', $columns),
        );

        if ($this->renderOnly) {
            // Un dump doit rester rejouable sur une base vierge : pas de « IF NOT EXISTS ».
            $this->rendered[] = ($this->isSqlite() ? $sql : str_replace(' IF NOT EXISTS', '', $sql)) . ';';

            return;
        }

        try {
            Database::connection()->exec($sql);
        } catch (\PDOException) {
            // MySQL ne supporte pas IF NOT EXISTS sur les index : erreur ignorée si déjà créé.
        }
    }

    public function dropIfExists(string $table): void
    {
        $keyword = $this->isSqlite() ? 'DROP TABLE IF EXISTS' : 'DROP TABLE IF EXISTS';

        Database::connection()->exec("{$keyword} {$table}");
    }

    public function tableExists(string $table): bool
    {
        try {
            if ($this->isSqlite()) {
                return (bool) Database::scalar(
                    "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :name",
                    ['name' => $table],
                );
            }

            return (bool) Database::scalar(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :name',
                ['name' => $table],
            );
        } catch (\Throwable) {
            return false;
        }
    }
}
