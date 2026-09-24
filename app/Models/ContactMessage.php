<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class ContactMessage extends Model
{
    protected string $table = 'contact_messages';

    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'status',
        'ip', 'user_agent', 'read_at', 'created_at', 'updated_at',
    ];

    public const STATUSES = [
        'new'      => 'Nouveau',
        'read'     => 'Lu',
        'answered' => 'Traité',
        'archived' => 'Archivé',
    ];

    /** @return list<array<string, mixed>> */
    public function listing(string $status = '', string $term = '', int $limit = 100, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($status, $term);

        return Database::select(
            "SELECT * FROM contact_messages WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params,
        );
    }

    public function countFiltered(string $status = '', string $term = ''): int
    {
        [$where, $params] = $this->buildFilters($status, $term);

        return (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE {$where}", $params);
    }

    /** @return array{0:string,1:array<string,mixed>} */
    private function buildFilters(string $status, string $term): array
    {
        $where = '1=1';
        $params = [];

        if ($status !== '') {
            $where .= ' AND status = :status';
            $params['status'] = $status;
        }

        if ($term !== '') {
            $where .= ' AND (name LIKE :term OR email LIKE :term OR subject LIKE :term OR message LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        return [$where, $params];
    }

    public function markAsRead(int $id): void
    {
        Database::statement(
            "UPDATE contact_messages SET status = 'read', read_at = :now, updated_at = :now
             WHERE id = :id AND status = 'new'",
            ['now' => date('Y-m-d H:i:s'), 'id' => $id],
        );
    }

    /** Nombre de messages reçus depuis une IP et un email donnés (anti-spam). */
    public function recentCountFrom(string $ip, int $windowSeconds): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM contact_messages WHERE ip = :ip AND created_at >= :since',
            ['ip' => $ip, 'since' => date('Y-m-d H:i:s', time() - $windowSeconds)],
        );
    }

    public function unreadCount(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    }
}
