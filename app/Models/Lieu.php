<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Lieu extends Model
{
    protected string $table = 'lieux';

    protected string $primaryKey = 'idlieu';

    protected array $fillable = [
        'idlieu', 'design', 'province', 'code_analytique', 'capacite', 'is_active',
        'created_at', 'updated_at',
    ];

    /** @return list<array<string, mixed>> */
    public function search(string $term = '', string $province = ''): array
    {
        $sql = 'SELECT l.*,
                       (SELECT COUNT(*) FROM employes e WHERE e.lieu = l.idlieu AND e.is_active = 1) AS effectif
                FROM lieux l
                WHERE 1=1';
        $params = [];

        if ($term !== '') {
            $sql .= ' AND (l.idlieu LIKE :term OR l.design LIKE :term OR l.province LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        if ($province !== '') {
            $sql .= ' AND l.province = :province';
            $params['province'] = $province;
        }

        $sql .= ' ORDER BY l.design';

        return Database::select($sql, $params);
    }

    /** @return list<string> */
    public function provinces(): array
    {
        $rows = Database::select('SELECT DISTINCT province FROM lieux ORDER BY province');

        return array_map(static fn (array $row): string => (string) $row['province'], $rows);
    }

    /** @return list<array{idlieu:string,design:string,effectif:int}> */
    public function options(): array
    {
        return Database::select(
            'SELECT l.idlieu, l.design,
                    (SELECT COUNT(*) FROM employes e WHERE e.lieu = l.idlieu AND e.is_active = 1) AS effectif
             FROM lieux l WHERE l.is_active = 1 ORDER BY l.design',
        );
    }

    public function isUsed(string $idlieu): bool
    {
        return (int) Database::scalar(
            'SELECT (SELECT COUNT(*) FROM employes WHERE lieu = :id)
                  + (SELECT COUNT(*) FROM affectations WHERE ancienLieu = :id OR nouveauLieu = :id)',
            ['id' => $idlieu],
        ) > 0;
    }

    /** Nombre d'employés par lieu (utilisé par la carte de couverture). */
    public function distribution(): array
    {
        return Database::select(
            'SELECT l.design, l.province, COUNT(e.numEmp) AS effectif
             FROM lieux l
             LEFT JOIN employes e ON e.lieu = l.idlieu AND e.is_active = 1
             GROUP BY l.idlieu, l.design, l.province
             ORDER BY effectif DESC, l.design',
        );
    }
}
