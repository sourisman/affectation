<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class User extends Model
{
    protected string $table = 'users';

    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name', 'email', 'password', 'role', 'job_title', 'avatar_path',
        'is_active', 'last_login_at', 'created_at', 'updated_at',
    ];

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        return Database::selectOne(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => mb_strtolower($email)],
        );
    }

    /** @return list<array<string, mixed>> */
    public function listing(string $search = '', ?string $role = null): array
    {
        $sql = 'SELECT id, name, email, role, job_title, avatar_path, is_active, last_login_at, created_at
                FROM users WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (name LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($role !== null && $role !== '') {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }

        $sql .= ' ORDER BY role, name';

        return Database::select($sql, $params);
    }

    public function emailTaken(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => mb_strtolower($email)];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        return (int) Database::scalar($sql, $params) > 0;
    }

    public function touchLogin(int $id): void
    {
        Database::statement(
            'UPDATE users SET last_login_at = :now, updated_at = :now WHERE id = :id',
            ['now' => date('Y-m-d H:i:s'), 'id' => $id],
        );
    }

    /** @return array<string, int> */
    public function countByRole(): array
    {
        $rows = Database::select('SELECT role, COUNT(*) AS total FROM users GROUP BY role');
        $counts = [];

        foreach ($rows as $row) {
            $counts[(string) $row['role']] = (int) $row['total'];
        }

        return $counts;
    }
}
