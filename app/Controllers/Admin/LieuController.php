<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employe;
use App\Models\Lieu;
use App\Services\ActivityAware;

/**
 * Gestion du référentiel des lieux d'affectation et de leur couverture.
 */
final class LieuController extends Controller
{
    use ActivityAware;

    private Lieu $lieux;

    public function __construct()
    {
        $this->lieux = new Lieu();
    }

    public function index(Request $request): Response
    {
        $term = (string) $request->query('q', '');
        $province = (string) $request->query('province', '');
        $rows = $this->lieux->search($term, $province);

        $maxEffectif = max(array_map(static fn (array $row): int => (int) $row['effectif'], $rows) ?: [1]);

        return $this->console('app.lieux.index', [
            'meta'        => ['title' => 'Lieux — Espace AFFECTA', 'robots' => 'noindex'],
            'lieux'       => $rows,
            'provinces'   => $this->lieux->provinces(),
            'term'        => $term,
            'province'    => $province,
            'maxEffectif' => $maxEffectif,
            'pageTitle'   => 'Lieux d\'affectation',
            'pageLead'    => 'Référentiel des sites, capacités et effectifs rattachés.',
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = $this->validate($request, [
            'idlieu'           => 'required|alpha_dash|max:10',
            'design'           => 'required|min:3|max:120',
            'province'         => 'required|min:2|max:100',
            'code_analytique'  => 'nullable|alpha_dash|max:20',
            'capacite'         => 'nullable|integer',
        ], [
            'idlieu'   => 'Identifiant',
            'design'   => 'Désignation',
            'province' => 'Province',
            'capacite' => 'Capacité d\'accueil',
        ]);

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/app/lieux', $request->all());
        }

        $data = $validator->validated();

        if ($this->lieux->exists('idlieu', $data['idlieu'])) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Cet identifiant de lieu existe déjà.',
                'errors'  => ['idlieu' => ['Identifiant déjà utilisé.']],
            ], '/app/lieux', $request->all());
        }

        $data['capacite'] = $data['capacite'] !== null ? (int) $data['capacite'] : null;
        $data['code_analytique'] = $data['code_analytique'] ?: null;
        $data['is_active'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->lieux->insert($data);
        $this->log('lieu.create', 'lieu', (string) $data['idlieu'], 'Création d\'un lieu');

        return $this->respond($request, [
            'success' => true,
            'message' => 'Lieu « ' . $data['design'] . ' » créé.',
        ], '/app/lieux');
    }

    public function update(Request $request, string $idlieu): Response
    {
        if ($this->lieux->find($idlieu) === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Lieu introuvable.'], '/app/lieux');
        }

        $validator = $this->validate($request, [
            'design'          => 'required|min:3|max:120',
            'province'        => 'required|min:2|max:100',
            'code_analytique' => 'nullable|alpha_dash|max:20',
            'capacite'        => 'nullable|integer',
            'is_active'       => 'nullable|in:0,1',
        ], ['design' => 'Désignation', 'province' => 'Province']);

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/app/lieux', $request->all());
        }

        $data = $validator->validated();
        unset($data['idlieu']);
        $data['is_active'] = (int) $request->input('is_active', 1);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['capacite'] = $data['capacite'] !== null ? (int) $data['capacite'] : null;

        $this->lieux->update($idlieu, $data);
        $this->log('lieu.update', 'lieu', $idlieu, 'Mise à jour d\'un lieu');

        return $this->respond($request, ['success' => true, 'message' => 'Lieu mis à jour.'], '/app/lieux');
    }

    public function destroy(Request $request, string $idlieu): Response
    {
        if ($this->lieux->find($idlieu) === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Lieu introuvable.'], '/app/lieux');
        }

        if ($this->lieux->isUsed($idlieu)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Suppression impossible : des agents ou des affectations sont rattachés à ce lieu.',
            ], '/app/lieux');
        }

        $this->lieux->delete($idlieu);
        $this->log('lieu.delete', 'lieu', $idlieu, 'Suppression d\'un lieu');

        return $this->respond($request, ['success' => true, 'message' => 'Lieu supprimé.'], '/app/lieux');
    }

    /** Fiche détaillée : effectifs et mouvements du site. */
    public function show(Request $request, string $idlieu): Response
    {
        $lieu = $this->lieux->find($idlieu);

        if ($lieu === null) {
            return $this->redirect('/app/lieux', 'Lieu introuvable.', 'error');
        }

        return $this->console('app.lieux.show', [
            'meta'       => ['title' => $lieu['design'] . ' — AFFECTA', 'robots' => 'noindex'],
            'lieu'       => $lieu,
            'equipe'     => (new Employe())->listing(['lieu' => $idlieu], 200),
            'arrivees'   => \App\Core\Database::select(
                'SELECT a.*, e.nom, e.prenom FROM affectations a
                 LEFT JOIN employes e ON e.numEmp = a.numEmp
                 WHERE a.nouveauLieu = :id ORDER BY a.dateAffect DESC LIMIT 12',
                ['id' => $idlieu],
            ),
            'departs'    => \App\Core\Database::select(
                'SELECT a.*, e.nom, e.prenom FROM affectations a
                 LEFT JOIN employes e ON e.numEmp = a.numEmp
                 WHERE a.ancienLieu = :id ORDER BY a.dateAffect DESC LIMIT 12',
                ['id' => $idlieu],
            ),
            'pageTitle'  => $lieu['design'],
            'pageLead'   => $lieu['province'] . ' — effectifs et mouvements récents.',
        ]);
    }
}
