<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Affectation;
use App\Models\Employe;
use App\Models\Lieu;
use App\Services\ActivityAware;

/**
 * Gestion des agents : recherche multi-critères, création, modification,
 * suppression protégée et fiche détaillée avec parcours de carrière.
 */
final class EmployeController extends Controller
{
    use ActivityAware;

    private Employe $employes;

    private Lieu $lieux;

    public function __construct()
    {
        $this->employes = new Employe();
        $this->lieux = new Lieu();
    }

    public function index(Request $request): Response
    {
        $filters = [
            'term'  => (string) $request->query('q', ''),
            'lieu'  => (string) $request->query('lieu', ''),
            'poste' => (string) $request->query('poste', ''),
            'actif' => $request->query('statut') === 'inactifs' ? false : true,
        ];

        $total = $this->employes->countFiltered($filters);
        $pagination = $this->paginate($request, $total, 25);

        return $this->console('app.employes.index', [
            'meta'       => ['title' => 'Employés — Espace AFFECTA', 'robots' => 'noindex'],
            'employes'   => $this->employes->listing($filters, $pagination['perPage'], $pagination['offset']),
            'lieux'      => $this->lieux->options(),
            'postes'     => $this->employes->postes(),
            'filters'    => $filters,
            'pagination' => $pagination,
            'pageTitle'  => 'Employés',
            'pageLead'   => 'Recherchez, mettez à jour et suivez le rattachement de chaque agent.',
        ]);
    }

    public function show(Request $request, string $numEmp): Response
    {
        $employe = $this->employes->findWithLieu($numEmp);

        if ($employe === null) {
            return $this->redirect('/app/employes', 'Employé introuvable.', 'error');
        }

        return $this->console('app.employes.show', [
            'meta'       => ['title' => $employe['prenom'] . ' ' . $employe['nom'] . ' — AFFECTA', 'robots' => 'noindex'],
            'employe'    => $employe,
            'historique' => (new Affectation())->forEmploye($numEmp),
            'pageTitle'  => $employe['civilite'] . ' ' . $employe['prenom'] . ' ' . $employe['nom'],
            'pageLead'   => 'Fiche agent et historique complet des affectations.',
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = $this->validate($request, $this->rules(), $this->labels());

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/app/employes', $request->all());
        }

        $data = $validator->validated();

        if ($this->employes->exists('numEmp', $data['numEmp'])) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Ce numéro employé existe déjà.',
                'errors'  => ['numEmp' => ['Numéro déjà utilisé.']],
            ], '/app/employes', $request->all());
        }

        if ($this->employes->emailTaken((string) $data['mail'])) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Cette adresse email est déjà associée à un agent.',
                'errors'  => ['mail' => ['Email déjà utilisé.']],
            ], '/app/employes', $request->all());
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = 1;
        $data['date_embauche'] = $data['date_embauche'] ?: null;
        $data['telephone'] = $data['telephone'] ?: null;

        $this->employes->insert($data);
        $this->log('employe.create', 'employe', (string) $data['numEmp'], 'Création de la fiche agent');

        return $this->respond($request, [
            'success' => true,
            'message' => 'Agent ' . $data['prenom'] . ' ' . $data['nom'] . ' créé avec succès.',
        ], '/app/employes');
    }

    public function update(Request $request, string $numEmp): Response
    {
        $employe = $this->employes->find($numEmp);

        if ($employe === null) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Employé introuvable.',
            ], '/app/employes', [], 404);
        }

        $validator = $this->validate($request, $this->rules($numEmp), $this->labels());

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/app/employes', $request->all());
        }

        $data = $validator->validated();
        unset($data['numEmp']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = (int) $request->input('is_active', 1) === 1 ? 1 : 0;

        $this->employes->update($numEmp, $data);
        $this->log('employe.update', 'employe', $numEmp, 'Modification de la fiche agent');

        return $this->respond($request, [
            'success' => true,
            'message' => 'Fiche agent mise à jour.',
        ], '/app/employes');
    }

    public function destroy(Request $request, string $numEmp): Response
    {
        $employe = $this->employes->find($numEmp);

        if ($employe === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Employé introuvable.'], '/app/employes', null, 404);
        }

        // Intégrité référentielle : on n'efface jamais un historique d'affectation.
        if ($this->employes->hasAffectations($numEmp)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Suppression impossible : cet agent possède un historique d\'affectations. Désactivez-le plutôt.',
            ], '/app/employes');
        }

        $this->employes->delete($numEmp);
        $this->log('employe.delete', 'employe', $numEmp, 'Suppression de la fiche agent');

        return $this->respond($request, ['success' => true, 'message' => 'Agent supprimé.'], '/app/employes');
    }

    /** @return array<string, string> */
    private function rules(?string $except = null): array
    {
        return [
            'numEmp'        => $except === null ? 'required|alpha_dash|max:10' : 'nullable',
            'civilite'      => 'required|in:M.,Mme,Mlle',
            'nom'           => 'required|min:2|max:80',
            'prenom'        => 'required|min:2|max:80',
            'mail'          => 'required|email|max:150',
            'telephone'     => 'nullable|phone|max:30',
            'poste'         => 'required|min:2|max:100',
            'lieu'          => 'required|max:10',
            'date_embauche' => 'nullable|date',
        ];
    }

    /** @return array<string, string> */
    private function labels(): array
    {
        return [
            'numEmp'        => 'Numéro employé',
            'civilite'      => 'Civilité',
            'nom'           => 'Nom',
            'prenom'        => 'Prénom',
            'mail'          => 'Email',
            'telephone'     => 'Téléphone',
            'poste'         => 'Poste',
            'lieu'          => 'Lieu d\'affectation',
            'date_embauche' => 'Date d\'embauche',
        ];
    }
}
