<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Gestionnaire de connexion PDO.
 *
 * Driver `mysql`  : production (MySQL 8 / MariaDB 10+) avec requêtes préparées.
 * Driver `sqlite` : exécution locale/démo sans serveur de base de données.
 *
 * Toutes les requêtes de l'application passent par des requêtes préparées :
 * aucune donnée utilisateur n'est interpolée dans le SQL.
 */
final class Database
{
    private static ?PDO $connection = null;

    private static ?string $driver = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) Config::get('database.driver', 'mysql');

        try {
            self::$connection = match ($driver) {
                'sqlite' => self::sqlite(),
                default  => self::mysql(),
            };
        } catch (PDOException $e) {
            // Aucune donnée sensible (DSN, identifiants) n'est exposée à l'utilisateur.
            error_log('[DB] ' . $e->getMessage());

            throw new RuntimeException(
                'Connexion à la base de données impossible. Vérifiez la configuration (.env) puis relancez l\'application.',
                (int) $e->getCode(),
                $e,
            );
        }

        self::$driver = $driver;

        return self::$connection;
    }

    public static function driver(): string
    {
        self::connection();

        return (string) self::$driver;
    }

    public static function isSqlite(): bool
    {
        return self::driver() === 'sqlite';
    }

    /** Exécute une requête préparée et renvoie le statement. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /** @return list<array<string, mixed>> */
    public static function select(string $sql, array $params = []): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = self::run($sql, $params)->fetchAll();

        return $rows;
    }

    /** @return array<string, mixed>|null */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();

        return is_array($row) ? $row : null;
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    public static function statement(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);

        $id = self::connection()->lastInsertId();

        return $id === false || $id === '0' ? 0 : (int) $id;
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    private static function mysql(): PDO
    {
        $host = (string) Config::get('database.host', '127.0.0.1');
        $port = (string) Config::get('database.port', '3306');
        $name = (string) Config::get('database.name', 'affecta');
        $socket = Config::get('database.socket');
        $charset = 'utf8mb4';

        $dsn = $socket
            ? "mysql:unix_socket={$socket};dbname={$name};charset={$charset}"
            : "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        return new PDO(
            $dsn,
            (string) Config::get('database.username', 'root'),
            (string) Config::get('database.password', ''),
            self::options(),
        );
    }

    private static function sqlite(): PDO
    {
        $path = (string) Config::get('database.sqlite_path', dirname(__DIR__, 2) . '/storage/affecta.sqlite');
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path, null, null, self::options());
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');

        return $pdo;
    }

    /** @return array<int, mixed> */
    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::ATTR_PERSISTENT         => false,
        ];
    }
}
