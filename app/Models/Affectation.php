<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Affectation extends Model
{
    protected string $table = 'affectations';

    protected string $primaryKey = 'id';

    protected array $fillable = [
        'numAffect', 'numEmp', 'ancienLieu', 'nouveauLieu', 'dateAffect',
        'datePriseService', 'motif', 'observation', 'statut', 'created_by',
        'created_at', 'updated_at',
    ];

    public const STATUTS = [
        'planifie' => 'Planifiée',
        'applique' => 'Appliquée',
        'annule'   => 'Annulée',
    ];

    public const MOTIFS = [
        'mutation'      => 'Mutation',
        'promotion'     => 'Promotion',
        'besoin_service' => 'Besoin de service',
        'demande_agent' => 'Demande de l\'agent',
        'reorganisation' => 'Réorganisation',
        'affectation_initiale' => 'Affectation initiale',
    ];

    /**
     * @param array{term?:string,statut?:string,lieu?:string,from?:string,to?:string} $filters
     * @return list<array<string, mixed>>
     */
    public function listing(array $filters = [], int $limit = 200, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);

        $sql = "SELECT a.*,
                       e.nom, e.prenom, e.civilite, e.poste,
                       la.design AS ancien_design, ln.design AS nouveau_design,
                       u.name AS auteur
                FROM affectations a
                LEFT JOIN employes e ON e.numEmp = a.numEmp
                LEFT JOIN lieux la ON la.idlieu = a.ancienLieu
                LEFT JOIN lieux ln ON ln.idlieu = a.nouveauLieu
                LEFT JOIN users u ON u.id = a.created_by
                WHERE {$where}
                ORDER BY a.dateAffect DESC, a.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        return Database::select($sql, $params);
    }

    /** @param array{term?:string,statut?:string,lieu?:string,from?:string,to?:string} $filters */
    public function countFiltered(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);

        return (int) Database::scalar("SELECT COUNT(*) FROM affectations a WHERE {$where}", $params);
    }

    /** @param array{term?:string,statut?:string,lieu?:string,from?:string,to?:string} $filters @return array{0:string,1:array<string,mixed>} */
    private function buildFilters(array $filters): array
    {
        $where = '1=1';
        $params = [];

        $term = trim($filters['term'] ?? '');
        if ($term !== '') {
            $where .= ' AND (a.numAffect LIKE :term OR a.numEmp LIKE :term OR a.motif LIKE :term
                        OR e.nom LIKE :term OR e.prenom LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        if (!empty($filters['statut'])) {
            $where .= ' AND a.statut = :statut';
            $params['statut'] = $filters['statut'];
        }

        if (!empty($filters['lieu'])) {
            $where .= ' AND (a.ancienLieu = :lieu OR a.nouveauLieu = :lieu)';
            $params['lieu'] = $filters['lieu'];
        }

        if (!empty($filters['from'])) {
            $where .= ' AND a.dateAffect >= :from';
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where .= ' AND a.dateAffect <= :to';
            $params['to'] = $filters['to'];
        }

        return [$where, $params];
    }

    /** @return array<string, mixed>|null */
    public function findDetailed(int $id): ?array
    {
        return Database::selectOne(
            'SELECT a.*, e.nom, e.prenom, e.civilite, e.poste, e.mail,
                    la.design AS ancien_design, la.province AS ancien_province,
                    ln.design AS nouveau_design, ln.province AS nouveau_province,
                    u.name AS auteur
             FROM affectations a
             LEFT JOIN employes e ON e.numEmp = a.numEmp
             LEFT JOIN lieux la ON la.idlieu = a.ancienLieu
             LEFT JOIN lieux ln ON ln.idlieu = a.nouveauLieu
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = :id LIMIT 1',
            ['id' => $id],
        );
    }

    /** Historique complet d'un agent (parcours de carrière). */
    public function forEmploye(string $numEmp): array
    {
        return Database::select(
            'SELECT a.*, la.design AS ancien_design, ln.design AS nouveau_design
             FROM affectations a
             LEFT JOIN lieux la ON la.idlieu = a.ancienLieu
             LEFT JOIN lieux ln ON ln.idlieu = a.nouveauLieu
             WHERE a.numEmp = :id
             ORDER BY a.dateAffect DESC',
            ['id' => $numEmp],
        );
    }

    public function hasPlanifieConflict(string $numEmp): bool
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM affectations WHERE numEmp = :id AND statut = 'planifie'",
            ['id' => $numEmp],
        ) > 0;
    }

    /** Numérotation automatique : AFF-2026-0001. */
    public function nextReference(string $year = ''): string
    {
        $year = $year !== '' ? $year : date('Y');
        $prefix = 'AFF-' . $year . '-';

        $last = Database::scalar(
            'SELECT numAffect FROM affectations WHERE numAffect LIKE :prefix ORDER BY numAffect DESC LIMIT 1',
            ['prefix' => $prefix . '%'],
        );

        $sequence = $last !== null ? (int) substr((string) $last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /** @return array<string, int> */
    public function countByStatut(): array
    {
        $rows = Database::select('SELECT statut, COUNT(*) AS total FROM affectations GROUP BY statut');
        $counts = ['planifie' => 0, 'applique' => 0, 'annule' => 0];

        foreach ($rows as $row) {
            $counts[(string) $row['statut']] = (int) $row['total'];
        }

        return $counts;
    }

    /** Volume mensuel des affectations (12 derniers mois) pour le graphique du dashboard. */
    public function monthlyVolume(int $months = 12): array
    {
        $rows = Database::select(
            'SELECT dateAffect, statut FROM affectations
             WHERE dateAffect >= :since ORDER BY dateAffect',
            ['since' => date('Y-m-d', strtotime("-{$months} months"))],
        );

        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-{$i} months"));
            $series[$key] = 0;
        }

        foreach ($rows as $row) {
            $key = substr((string) $row['dateAffect'], 0, 7);

            if (isset($series[$key])) {
                $series[$key]++;
            }
        }

        return $series;
    }

    /** Flux d'affectations entre provinces (matrice origine → destination). */
    public function provinceFlows(int $limit = 6): array
    {
        return Database::select(
            "SELECT la.province AS origine, ln.province AS destination, COUNT(*) AS total
             FROM affectations a
             LEFT JOIN lieux la ON la.idlieu = a.ancienLieu
             LEFT JOIN lieux ln ON ln.idlieu = a.nouveauLieu
             WHERE a.statut IN ('planifie', 'applique')
             GROUP BY la.province, ln.province
             ORDER BY total DESC
             LIMIT {$limit}",
        );
    }

    /** Activité récente pour le flux du dashboard. */
    public function recent(int $limit = 8): array
    {
        return Database::select(
            "SELECT a.id, a.numAffect, a.dateAffect, a.statut, a.numEmp, e.nom, e.prenom,
                    la.design AS ancien_design, ln.design AS nouveau_design
             FROM affectations a
             LEFT JOIN employes e ON e.numEmp = a.numEmp
             LEFT JOIN lieux la ON la.idlieu = a.ancienLieu
             LEFT JOIN lieux ln ON ln.idlieu = a.nouveauLieu
             ORDER BY a.id DESC
             LIMIT {$limit}",
        );
    }
}
