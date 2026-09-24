<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Affectation;
use App\Models\Employe;
use App\Models\Lieu;
use App\Services\AffectationService;

/**
 * Cycle de vie d'une affectation : création, consultation, application,
 * annulation, suppression et export.
 */
final class AffectationController extends Controller
{
    private Affectation $affectations;

    private AffectationService $service;

    public function __construct()
    {
        $this->affectations = new Affectation();
        $this->service = new AffectationService($this->affectations);
    }

    public function index(Request $request): Response
    {
        $filters = [
            'term'   => (string) $request->query('q', ''),
            'statut' => (string) $request->query('statut', ''),
            'lieu'   => (string) $request->query('lieu', ''),
            'from'   => (string) $request->query('from', ''),
            'to'     => (string) $request->query('to', ''),
        ];

        $total = $this->affectations->countFiltered($filters);
        $pagination = $this->paginate($request, $total, 25);

        return $this->console('app.affectations.index', [
            'meta'       => ['title' => 'Affectations — Espace AFFECTA', 'robots' => 'noindex'],
            'affectations' => $this->affectations->listing($filters, $pagination['perPage'], $pagination['offset']),
            'lieux'      => (new Lieu())->options(),
            'employes'   => (new Employe())->listing(['actif' => true], 500),
            'statuts'    => Affectation::STATUTS,
            'motifs'     => Affectation::MOTIFS,
            'filters'    => $filters,
            'pagination' => $pagination,
            'reference'  => $this->affectations->nextReference(),
            'pageTitle'  => 'Affectations',
            'pageLead'   => 'Créez, appliquez et annulez les mouvements de personnel.',
        ]);
    }

    /** Vue kanban : répartition par statut. */
    public function board(Request $request): Response
    {
        $all = $this->affectations->listing([], 200);

        $grouped = ['planifie' => [], 'applique' => [], 'annule' => []];
        foreach ($all as $row) {
            $grouped[(string) $row['statut']][] = $row;
        }

        return $this->console('app.affectations.board', [
            'meta'      => ['title' => 'Suivi des mouvements — AFFECTA', 'robots' => 'noindex'],
            'grouped'   => $grouped,
            'pageTitle' => 'Suivi des mouvements',
            'pageLead'  => 'Vue d\'ensemble du pipeline : planifiées, appliquées, annulées.',
        ]);
    }

    public function store(Request $request): Response
    {
        $result = $this->service->create($request->all());

        return $this->respond($request, $result, '/app/affectations', $request->all());
    }

    public function show(Request $request, string $id): Response
    {
        $affectation = $this->affectations->findDetailed((int) $id);

        if ($affectation === null) {
            return $this->redirect('/app/affectations', 'Affectation introuvable.', 'error');
        }

        return $this->console('app.affectations.show', [
            'meta'        => ['title' => 'Affectation ' . $affectation['numAffect'], 'robots' => 'noindex'],
            'affectation' => $affectation,
            'parcours'    => $this->affectations->forEmploye((string) $affectation['numEmp']),
            'pageTitle'   => 'Affectation ' . $affectation['numAffect'],
            'pageLead'    => $affectation['prenom'] . ' ' . $affectation['nom'] . ' — ' . $affectation['poste'],
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $result = $this->service->update((int) $id, $request->all());

        return $this->respond($request, $result, '/app/affectations', $request->all());
    }

    public function apply(Request $request, string $id): Response
    {
        return $this->respond($request, $this->service->apply((int) $id), '/app/affectations');
    }

    public function cancel(Request $request, string $id): Response
    {
        return $this->respond(
            $request,
            $this->service->cancel((int) $id, (string) $request->input('reason', '')),
            '/app/affectations',
        );
    }

    public function destroy(Request $request, string $id): Response
    {
        return $this->respond($request, $this->service->delete((int) $id), '/app/affectations');
    }

    /** Export CSV du périmètre filtré. */
    public function export(Request $request): Response
    {
        $filters = [
            'statut' => (string) $request->query('statut', ''),
            'lieu'   => (string) $request->query('lieu', ''),
            'from'   => (string) $request->query('from', ''),
            'to'     => (string) $request->query('to', ''),
        ];

        $result = $this->service->exportCsv($filters);

        if ($result['success'] !== true) {
            return $this->redirect('/app/affectations', $result['message'], 'error');
        }

        return Response::download($result['contenu'], $result['nom'], 'text/csv; charset=utf-8');
    }
}
