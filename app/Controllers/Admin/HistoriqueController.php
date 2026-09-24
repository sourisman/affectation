<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\Affectation;
use App\Models\Lieu;

/**
 * Historique : recherche chronologique des affectations et journal d'audit.
 */
final class HistoriqueController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'term'   => (string) $request->query('q', ''),
            'statut' => (string) $request->query('statut', ''),
            'lieu'   => (string) $request->query('lieu', ''),
            'from'   => (string) $request->query('from', ''),
            'to'     => (string) $request->query('to', ''),
        ];

        $affectations = new Affectation();
        $total = $affectations->countFiltered($filters);
        $pagination = $this->paginate($request, $total, 30);

        // Regroupement par mois pour restituer une chronologie lisible.
        $rows = $affectations->listing($filters, $pagination['perPage'], $pagination['offset']);
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[substr((string) $row['dateAffect'], 0, 7)][] = $row;
        }

        return $this->console('app.historique.index', [
            'meta'       => ['title' => 'Historique — Espace AFFECTA', 'robots' => 'noindex'],
            'grouped'    => $grouped,
            'log'        => (new ActivityLog())->latest(12),
            'lieux'      => (new Lieu())->options(),
            'statuts'    => Affectation::STATUTS,
            'filters'    => $filters,
            'pagination' => $pagination,
            'pageTitle'  => 'Historique',
            'pageLead'   => 'Chronologie complète des mouvements et journal des actions sensibles.',
        ]);
    }
}
