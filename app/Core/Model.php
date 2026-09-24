<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Modèle de base : table déclarée par l'enfant, requêtes préparées uniquement.
 *
 * Les colonnes autorisées en écriture sont limitées par $fillable :
 * une clé inattendue dans le payload est silencieusement écartée.
 */
abstract class Model
{
    protected string $table = '';

    protected string $primaryKey = 'id';

    /** @var list<string> */
    protected array $fillable = [];

    /** @param array<string, mixed> $attributes */
    public function __construct(protected array $attributes = [])
    {
    }

    public static function make(): static
    {
        return new static();
    }

    /** @param array<string, mixed> $attributes */
    public static function create(array $attributes): int
    {
        $model = new static();

        return $model->insert($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function insert(array $attributes): int
    {
        $data = $this->filter($attributes);

        if ($data === []) {
            return 0;
        }

        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders),
        );

        return Database::insert($sql, $this->bindings($data));
    }

    /** @param array<string, mixed> $attributes */
    public function update(mixed $id, array $attributes): int
    {
        $data = $this->filter($attributes);
        unset($data[$this->primaryKey]);

        if ($data === []) {
            return 0;
        }

        $assignments = array_map(
            static fn (string $column): string => $column . ' = :' . $column,
            array_keys($data),
        );

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__id',
            $this->table,
            implode(', ', $assignments),
            $this->primaryKey,
        );

        return Database::statement($sql, $this->bindings($data) + ['__id' => $id]);
    }

    public function delete(mixed $id): int
    {
        return Database::statement(
            sprintf('DELETE FROM %s WHERE %s = :__id', $this->table, $this->primaryKey),
            ['__id' => $id],
        );
    }

    /** @return array<string, mixed>|null */
    public function find(mixed $id): ?array
    {
        return Database::selectOne(
            sprintf('SELECT * FROM %s WHERE %s = :__id LIMIT 1', $this->table, $this->primaryKey),
            ['__id' => $id],
        );
    }

    /** @return array<string, mixed>|null */
    public function findBy(string $column, mixed $value): ?array
    {
        $this->assertColumn($column);

        return Database::selectOne(
            sprintf('SELECT * FROM %s WHERE %s = :value LIMIT 1', $this->table, $column),
            ['value' => $value],
        );
    }

    public function exists(string $column, mixed $value, mixed $exceptId = null): bool
    {
        $this->assertColumn($column);

        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s = :value', $this->table, $column);
        $params = ['value' => $value];

        if ($exceptId !== null) {
            $sql .= sprintf(' AND %s <> :except', $this->primaryKey);
            $params['except'] = $exceptId;
        }

        return (int) Database::scalar($sql, $params) > 0;
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        return (int) Database::scalar(sprintf('SELECT COUNT(*) FROM %s WHERE %s', $this->table, $where), $params);
    }

    /** @return list<array<string, mixed>> */
    public function all(string $orderBy = ''): array
    {
        $sql = sprintf('SELECT * FROM %s', $this->table);

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        return Database::select($sql);
    }

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    protected function filter(array $attributes): array
    {
        if ($this->fillable === []) {
            return $attributes;
        }

        return array_intersect_key($attributes, array_flip($this->fillable));
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    protected function bindings(array $data): array
    {
        $bindings = [];

        foreach ($data as $column => $value) {
            $bindings[$column] = is_bool($value) ? (int) $value : $value;
        }

        return $bindings;
    }

    protected function assertColumn(string $column): void
    {
        if ($this->fillable !== [] && !in_array($column, $this->fillable, true) && !str_ends_with($column, '_id')) {
            throw new \InvalidArgumentException("Colonne non interrogeable : $column");
        }
    }
}
