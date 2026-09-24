<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Exécuteur de migrations : chaque fichier database/migrations/*.php
 * retourne une closure (Schema $schema): void appliquée une seule fois.
 */
final class Migrator
{
    public function __construct(
        private readonly string $directory,
        private readonly Schema $schema,
    ) {
    }

    /** @return list<string> */
    public function pending(): array
    {
        $this->ensureRepository();

        $executed = $this->executed();
        $pending = [];

        foreach ($this->files() as $file) {
            $name = basename($file, '.php');

            if (!in_array($name, $executed, true)) {
                $pending[] = $name;
            }
        }

        return $pending;
    }

    /** @return list<string> */
    public function run(bool $fresh = false): array
    {
        if ($fresh) {
            $this->reset();
        }

        $this->ensureRepository();

        $applied = [];

        foreach ($this->pending() as $name) {
            $file = $this->directory . '/' . $name . '.php';
            $migration = require $file;

            if (!is_callable($migration)) {
                throw new \RuntimeException("Migration invalide : {$name} (une closure est attendue).");
            }

            $start = microtime(true);

            try {
                $migration($this->schema);
            } catch (Throwable $e) {
                throw new \RuntimeException("Échec de la migration {$name} : {$e->getMessage()}", 0, $e);
            }

            Database::statement(
                'INSERT INTO migrations (migration, batch, ran_at) VALUES (:migration, :batch, :ran_at)',
                [
                    'migration' => $name,
                    'batch'     => 1,
                    'ran_at'    => date('Y-m-d H:i:s'),
                ],
            );

            $applied[] = sprintf('%s (%.0f ms)', $name, (microtime(true) - $start) * 1000);
        }

        return $applied;
    }

    /** Supprime toutes les tables applicatives (mode --fresh). */
    private function reset(): void
    {
        $tables = [
            'activity_logs', 'login_attempts', 'password_resets', 'contact_messages',
            'settings', 'affectations', 'employes', 'lieux', 'users', 'migrations',
        ];

        if ($this->schema->isSqlite()) {
            Database::connection()->exec('PRAGMA foreign_keys = OFF');
        } else {
            Database::connection()->exec('SET FOREIGN_KEY_CHECKS = 0');
        }

        foreach ($tables as $table) {
            $this->schema->dropIfExists($table);
        }

        if ($this->schema->isSqlite()) {
            Database::connection()->exec('PRAGMA foreign_keys = ON');
        } else {
            Database::connection()->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function ensureRepository(): void
    {
        $this->schema->create('migrations', [
            $this->schema->id(),
            $this->schema->string('migration', 191),
            $this->schema->integer('batch', false, 1),
            $this->schema->timestamp('ran_at', false, false),
        ]);
        $this->schema->index('migrations', 'migrations_migration_unique', ['migration'], true);
    }

    /** @return list<string> */
    private function executed(): array
    {
        try {
            $rows = Database::select('SELECT migration FROM migrations');
        } catch (Throwable) {
            return [];
        }

        return array_map(static fn (array $row): string => (string) $row['migration'], $rows);
    }

    /** @return list<string> */
    private function files(): array
    {
        $files = glob($this->directory . '/*.php') ?: [];
        sort($files);

        return $files;
    }
}
