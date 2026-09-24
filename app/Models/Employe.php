<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Employe extends Model
{
    protected string $table = 'employes';

    protected string $primaryKey = 'numEmp';

    protected array $fillable = [
        'numEmp', 'civilite', 'nom', 'prenom', 'mail', 'telephone', 'poste',
        'lieu', 'date_embauche', 'is_active', 'created_at', 'updated_at',
    ];

    /**
     * @param array{term?:string,lieu?:string,poste?:string,actif?:bool} $filters
     * @return list<array<string, mixed>>
     */
    public function listing(array $filters = [], int $limit = 300, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);

        $sql = "SELECT e.*, l.design AS nom_lieu, l.province AS province
                FROM employes e
                LEFT JOIN lieux l ON l.idlieu = e.lieu
                WHERE {$where}
                ORDER BY e.nom, e.prenom
                LIMIT {$limit} OFFSET {$offset}";

        return Database::select($sql, $params);
    }

    /** @param array{term?:string,lieu?:string,poste?:string,actif?:bool} $filters */
    public function countFiltered(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);

        return (int) Database::scalar("SELECT COUNT(*) FROM employes e WHERE {$where}", $params);
    }

    /** @param array{term?:string,lieu?:string,poste?:string,actif?:bool} $filters @return array{0:string,1:array<string,mixed>} */
    private function buildFilters(array $filters): array
    {
        $where = '1=1';
        $params = [];

        $term = trim($filters['term'] ?? '');
        if ($term !== '') {
            $where .= ' AND (e.numEmp LIKE :term OR e.nom LIKE :term OR e.prenom LIKE :term
                        OR e.mail LIKE :term OR e.poste LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        if (!empty($filters['lieu'])) {
            $where .= ' AND e.lieu = :lieu';
            $params['lieu'] = $filters['lieu'];
        }

        if (!empty($filters['poste'])) {
            $where .= ' AND e.poste LIKE :poste';
            $params['poste'] = '%' . $filters['poste'] . '%';
        }

        if (($filters['actif'] ?? null) === true) {
            $where .= ' AND e.is_active = 1';
        }

        return [$where, $params];
    }

    /** @return array<string, mixed>|null */
    public function findWithLieu(string $numEmp): ?array
    {
        return Database::selectOne(
            'SELECT e.*, l.design AS nom_lieu, l.province
             FROM employes e LEFT JOIN lieux l ON l.idlieu = e.lieu
             WHERE e.numEmp = :id LIMIT 1',
            ['id' => $numEmp],
        );
    }

    /** Employés sans affectation active (dashboard « non affectés »). */
    public function nonAffectes(): array
    {
        return Database::select(
            'SELECT e.*, l.design AS nom_lieu
             FROM employes e
             LEFT JOIN lieux l ON l.idlieu = e.lieu
             WHERE e.is_active = 1
               AND NOT EXISTS (
                    SELECT 1 FROM affectations a
                    WHERE a.numEmp = e.numEmp AND a.statut IN (:planifie, :applique)
               )
             ORDER BY e.nom',
            ['planifie' => 'planifie', 'applique' => 'applique'],
        );
    }

    /** @return list<string> */
    public function postes(): array
    {
        $rows = Database::select('SELECT DISTINCT poste FROM employes ORDER BY poste');

        return array_map(static fn (array $row): string => (string) $row['poste'], $rows);
    }

    public function emailTaken(string $mail, ?string $exceptNumEmp = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM employes WHERE mail = :mail';
        $params = ['mail' => mb_strtolower($mail)];

        if ($exceptNumEmp !== null) {
            $sql .= ' AND numEmp <> :id';
            $params['id'] = $exceptNumEmp;
        }

        return (int) Database::scalar($sql, $params) > 0;
    }

    public function hasAffectations(string $numEmp): bool
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM affectations WHERE numEmp = :id',
            ['id' => $numEmp],
        ) > 0;
    }

    /** @return array<int, list<array<string, mixed>>> */
    public function groupedByLieu(): array
    {
        $rows = Database::select(
            'SELECT e.*, l.design AS nom_lieu
             FROM employes e LEFT JOIN lieux l ON l.idlieu = e.lieu
             WHERE e.is_active = 1 ORDER BY l.design, e.nom',
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(string) ($row['nom_lieu'] ?? 'Non rattaché')][] = $row;
        }

        return $grouped;
    }
}
