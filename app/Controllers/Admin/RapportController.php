<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Affectation;
use App\Models\Employe;
use App\Models\Lieu;

/**
 * Rapports consolidés : couverture des sites, mouvements par période,
 * agents non affectés et synthèse imprimable.
 */
final class RapportController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) $request->query('annee', (int) date('Y'));
        $year = $year >= 2000 && $year <= 2100 ? $year : (int) date('Y');

        return $this->console('app.rapport.index', [
            'meta'      => ['title' => 'Rapports — Espace AFFECTA', 'robots' => 'noindex'],
            'year'      => $year,
            'annees'    => $this->availableYears(),
            'couverture' => (new Lieu())->distribution(),
            'mensuel'   => $this->monthlyByYear($year),
            'flux'      => (new Affectation())->provinceFlows(8),
            'parMotif'  => $this->byMotif(),
            'nonAffectes' => (new Employe())->nonAffectes(),
            'synthese'  => $this->synthese(),
            'pageTitle' => 'Rapports',
            'pageLead'  => 'Analysez la couverture territoriale et les mouvements de personnel.',
        ]);
    }

    public function nonAffectes(Request $request): Response
    {
        $employes = (new Employe())->nonAffectes();

        return $this->console('app.rapport.non_affectes', [
            'meta'      => ['title' => 'Agents non affectés — AFFECTA', 'robots' => 'noindex'],
            'employes'  => $employes,
            'pageTitle' => 'Agents non affectés',
            'pageLead'  => 'Agents actifs ne disposant d\'aucune affectation en cours ni planifiée.',
        ]);
    }

    /** Vue imprimable (impression navigateur / export PDF côté client). */
    public function print(Request $request): Response
    {
        $year = (int) $request->query('annee', (int) date('Y'));

        return $this->view('app.rapport.print', [
            'meta'      => ['title' => 'Rapport d\'affectation ' . $year, 'robots' => 'noindex'],
            'year'      => $year,
            'couverture' => (new Lieu())->distribution(),
            'mensuel'   => $this->monthlyByYear($year),
            'synthese'  => $this->synthese(),
            'affectations' => (new Affectation())->listing(
                ['from' => $year . '-01-01', 'to' => $year . '-12-31'],
                500,
            ),
        ], 'layouts.print');
    }

    /** @return list<int> */
    private function availableYears(): array
    {
        $rows = Database::select('SELECT DISTINCT SUBSTR(dateAffect, 1, 4) AS annee FROM affectations ORDER BY annee DESC');
        $years = array_map(static fn (array $row): int => (int) $row['annee'], $rows);

        if (!in_array((int) date('Y'), $years, true)) {
            array_unshift($years, (int) date('Y'));
        }

        return $years;
    }

    /** @return array<string, int> */
    private function monthlyByYear(int $year): array
    {
        $rows = Database::select(
            'SELECT dateAffect, statut FROM affectations WHERE dateAffect LIKE :year',
            ['year' => $year . '%'],
        );

        $series = [];
        for ($month = 1; $month <= 12; $month++) {
            $series[sprintf('%02d', $month)] = 0;
        }

        foreach ($rows as $row) {
            $month = substr((string) $row['dateAffect'], 5, 2);

            if (isset($series[$month])) {
                $series[$month]++;
            }
        }

        return $series;
    }

    /** @return list<array<string, mixed>> */
    private function byMotif(): array
    {
        $rows = Database::select(
            'SELECT motif, COUNT(*) AS total FROM affectations GROUP BY motif ORDER BY total DESC',
        );

        return array_map(static fn (array $row): array => [
            'motif' => Affectation::MOTIFS[(string) $row['motif']] ?? 'Non précisé',
            'total' => (int) $row['total'],
        ], $rows);
    }

    /** @return array<string, int|float> */
    private function synthese(): array
    {
        $employes = (int) Database::scalar('SELECT COUNT(*) FROM employes');
        $affectations = (int) Database::scalar('SELECT COUNT(*) FROM affectations');
        $appliquees = (int) Database::scalar("SELECT COUNT(*) FROM affectations WHERE statut = 'applique'");
        $lieux = (int) Database::scalar('SELECT COUNT(*) FROM lieux');

        return [
            'employes'       => $employes,
            'lieux'          => $lieux,
            'affectations'   => $affectations,
            'appliquees'     => $appliquees,
            'ratio_mobilite' => $employes > 0 ? round($affectations / $employes * 100, 1) : 0.0,
            'moyenne_agents' => $lieux > 0 ? round($employes / $lieux, 1) : 0.0,
        ];
    }
}
