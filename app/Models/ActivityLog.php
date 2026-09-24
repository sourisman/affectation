<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    protected string $primaryKey = 'id';

    protected array $fillable = ['user_id', 'action', 'entity', 'entity_id', 'description', 'meta', 'ip', 'created_at'];

    public function record(
        string $action,
        ?string $entity = null,
        ?string $entityId = null,
        ?string $description = null,
        array $meta = [],
    ): void {
        $user = auth_user();

        Database::statement(
            'INSERT INTO activity_logs (user_id, action, entity, entity_id, description, meta, ip, created_at)
             VALUES (:user_id, :action, :entity, :entity_id, :description, :meta, :ip, :created_at)',
            [
                'user_id'     => $user['id'] ?? null,
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'description' => $description,
                'meta'        => $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
                'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at'  => date('Y-m-d H:i:s'),
            ],
        );
    }

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 10): array
    {
        return Database::select(
            "SELECT al.*, u.name AS user_name, u.role
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.id DESC
             LIMIT {$limit}",
        );
    }

    /** Nombre d'actions par jour sur les 14 derniers jours. */
    public function dailyActivity(int $days = 14): array
    {
        $rows = Database::select(
            'SELECT created_at FROM activity_logs WHERE created_at >= :since',
            ['since' => date('Y-m-d H:i:s', strtotime("-{$days} days"))],
        );

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $series[date('Y-m-d', strtotime("-{$i} days"))] = 0;
        }

        foreach ($rows as $row) {
            $key = substr((string) $row['created_at'], 0, 10);

            if (isset($series[$key])) {
                $series[$key]++;
            }
        }

        return $series;
    }
}
