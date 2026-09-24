<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Affectation;
use RuntimeException;

/**
 * Règles métier des affectations : création, application (transfert réel
 * de l'employé), annulation et statistiques d'ensemble.
 */
final class AffectationService
{
    use ActivityAware;

    public function __construct(private readonly Affectation $affectations = new Affectation())
    {
    }

    /**
     * Valide puis enregistre une affectation.
     *
     * @param array<string, mixed> $payload
     * @return array{success:bool,message:string,errors?:array<string,list<string>>,id?:int,reference?:string}
     */
    public function create(array $payload): array
    {
        $validator = new Validator($payload, [
            'numEmp'           => 'required|max:10',
            'ancienLieu'       => 'required|max:10',
            'nouveauLieu'      => 'required|max:10|different:ancienLieu',
            'dateAffect'       => 'required|date',
            'datePriseService' => 'required|date|between_dates:dateAffect',
            'motif'            => 'nullable|in:' . implode(',', array_keys(Affectation::MOTIFS)),
            'observation'      => 'nullable|max:2000',
            'statut'           => 'nullable|in:' . implode(',', array_keys(Affectation::STATUTS)),
        ], [
            'numEmp'           => 'Employé',
            'ancienLieu'       => 'Lieu d\'origine',
            'nouveauLieu'      => 'Lieu d\'accueil',
            'dateAffect'       => 'Date d\'affectation',
            'datePriseService' => 'Date de prise de service',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'message' => $validator->firstError(), 'errors' => $validator->errors()];
        }

        $data = $validator->validated();

        // Cohérence référentielle : employé et lieux doivent exister.
        $employe = Database::selectOne('SELECT numEmp, lieu FROM employes WHERE numEmp = :id', ['id' => $data['numEmp']]);
        if ($employe === null) {
            return ['success' => false, 'message' => 'Employé introuvable.', 'errors' => ['numEmp' => ['Employé inconnu.']]];
        }

        $lieux = array_column(
            Database::select(
                'SELECT idlieu FROM lieux WHERE idlieu IN (:a, :b)',
                ['a' => $data['ancienLieu'], 'b' => $data['nouveauLieu']],
            ),
            'idlieu',
        );

        if (count($lieux) !== 2) {
            return ['success' => false, 'message' => 'Lieux invalides.', 'errors' => ['nouveauLieu' => ['Lieu inconnu.']]];
        }

        // Une seule affectation en préparation à la fois par employé.
        if ($data['statut'] !== 'annule' && $this->affectations->hasPlanifieConflict($data['numEmp'])) {
            return [
                'success' => false,
                'message' => 'Une affectation est déjà planifiée pour cet employé. Appliquez-la ou annulez-la avant d\'en créer une nouvelle.',
                'errors'  => ['numEmp' => ['Affectation en attente existante.']],
            ];
        }

        $data['numAffect'] = $this->affectations->nextReference(substr((string) $data['dateAffect'], 0, 4));
        $data['statut'] = $data['statut'] ?: 'planifie';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = auth_user()['id'] ?? null;

        $id = Database::transaction(function () use ($data): int {
            $id = $this->affectations->insert($data);

            // Statut « appliquée » : l'employé change immédiatement de lieu.
            if ($data['statut'] === 'applique') {
                $this->applyTransfer($data['numEmp'], $data['nouveauLieu']);
            }

            return $id;
        });

        $this->log('affectation.create', 'affectation', $data['numAffect'], sprintf(
            'Affectation %s : %s → %s',
            $data['numEmp'],
            $data['ancienLieu'],
            $data['nouveauLieu'],
        ));

        return [
            'success'   => true,
            'message'   => 'Affectation enregistrée avec succès.',
            'id'        => $id,
            'reference' => $data['numAffect'],
        ];
    }

    /** @param array<string, mixed> $payload */
    public function update(int $id, array $payload): array
    {
        $existing = $this->affectations->find($id);

        if ($existing === null) {
            return ['success' => false, 'message' => 'Affectation introuvable.'];
        }

        if ($existing['statut'] === 'annule') {
            return ['success' => false, 'message' => 'Une affectation annulée ne peut plus être modifiée.'];
        }

        $merged = array_merge($existing, array_filter($payload, static fn ($value): bool => $value !== null));
        $merged['numEmp'] = $existing['numEmp'];

        $validator = new Validator($merged, [
            'dateAffect'       => 'required|date',
            'datePriseService' => 'required|date|between_dates:dateAffect',
            'motif'            => 'nullable|in:' . implode(',', array_keys(Affectation::MOTIFS)),
            'observation'      => 'nullable|max:2000',
        ], [
            'dateAffect'       => 'Date d\'affectation',
            'datePriseService' => 'Date de prise de service',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'message' => $validator->firstError(), 'errors' => $validator->errors()];
        }

        $data = $validator->validated();
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->affectations->update($id, $data);
        $this->log('affectation.update', 'affectation', (string) $id, 'Mise à jour d\'une affectation');

        return ['success' => true, 'message' => 'Affectation mise à jour.'];
    }

    /** Marque l'affectation comme appliquée et transfère l'employé. */
    public function apply(int $id): array
    {
        $affectation = $this->affectations->find($id);

        if ($affectation === null) {
            return ['success' => false, 'message' => 'Affectation introuvable.'];
        }

        if ($affectation['statut'] === 'applique') {
            return ['success' => false, 'message' => 'Cette affectation est déjà appliquée.'];
        }

        if ($affectation['statut'] === 'annule') {
            return ['success' => false, 'message' => 'Cette affectation a été annulée.'];
        }

        Database::transaction(function () use ($affectation, $id): void {
            $this->applyTransfer((string) $affectation['numEmp'], (string) $affectation['nouveauLieu']);

            $this->affectations->update($id, [
                'statut'     => 'applique',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        });

        $this->log('affectation.apply', 'affectation', (string) $id, 'Affectation appliquée');

        return ['success' => true, 'message' => 'Affectation appliquée et employé transféré.'];
    }

    public function cancel(int $id, ?string $reason = null): array
    {
        $affectation = $this->affectations->find($id);

        if ($affectation === null) {
            return ['success' => false, 'message' => 'Affectation introuvable.'];
        }

        if ($affectation['statut'] === 'annule') {
            return ['success' => false, 'message' => 'Affectation déjà annulée.'];
        }

        // Annulation d'une affectation déjà appliquée : on remet l'employé à son poste d'origine.
        if ($affectation['statut'] === 'applique') {
            $this->applyTransfer((string) $affectation['numEmp'], (string) $affectation['ancienLieu']);
        }

        $observation = trim((string) $affectation['observation'] . "\n" . ($reason ?? ''));

        $this->affectations->update($id, [
            'statut'      => 'annule',
            'observation' => trim($observation) ?: null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->log('affectation.cancel', 'affectation', (string) $id, 'Affectation annulée');

        return ['success' => true, 'message' => 'Affectation annulée.'];
    }

    public function delete(int $id): array
    {
        $affectation = $this->affectations->find($id);

        if ($affectation === null) {
            return ['success' => false, 'message' => 'Affectation introuvable.'];
        }

        if ($affectation['statut'] === 'applique') {
            return [
                'success' => false,
                'message' => 'Une affectation appliquée est archivée : annulez-la plutôt que de la supprimer.',
            ];
        }

        $this->affectations->delete($id);
        $this->log('affectation.delete', 'affectation', (string) $id, 'Suppression d\'une affectation');

        return ['success' => true, 'message' => 'Affectation supprimée.'];
    }

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $statuts = $this->affectations->countByStatut();
        $dernier = Database::selectOne(
            "SELECT COUNT(*) AS total FROM affectations
             WHERE strftime('%Y-%m', dateAffect) = :mois OR dateAffect LIKE :like",
            ['mois' => date('Y-m'), 'like' => date('Y-m') . '%'],
        );

        return [
            'total'        => array_sum($statuts),
            'planifiees'   => $statuts['planifie'],
            'appliquees'   => $statuts['applique'],
            'annulees'     => $statuts['annule'],
            'mois_courant' => (int) ($dernier['total'] ?? 0),
            'volume'       => $this->affectations->monthlyVolume(),
            'flux'         => $this->affectations->provinceFlows(),
            'recents'      => $this->affectations->recent(),
        ];
    }

    /** @return array{success:bool, message:string, chemin?:string, nom?:string} */
    public function exportCsv(array $filters = []): array
    {
        $rows = $this->affectations->listing($filters, 5000);

        if ($rows === []) {
            return ['success' => false, 'message' => 'Aucune donnée à exporter pour ces critères.'];
        }

        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Impossible de préparer l\'export.');
        }

        // BOM UTF-8 pour une ouverture correcte dans Excel.
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
            'Référence', 'Employé', 'Nom', 'Prénom', 'Poste', 'Lieu d\'origine',
            'Lieu d\'accueil', 'Date d\'affectation', 'Prise de service', 'Motif', 'Statut',
        ], ';');

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['numAffect'],
                $row['numEmp'],
                $row['nom'],
                $row['prenom'],
                $row['poste'],
                $row['ancien_design'],
                $row['nouveau_design'],
                $row['dateAffect'],
                $row['datePriseService'],
                Affectation::MOTIFS[$row['motif']] ?? $row['motif'],
                Affectation::STATUTS[$row['statut']] ?? $row['statut'],
            ], ';');
        }

        rewind($handle);
        $content = (string) stream_get_contents($handle);
        fclose($handle);

        $filename = 'affectations-' . date('Y-m-d-Hi') . '.csv';

        $this->log('affectation.export', 'affectation', null, 'Export CSV de ' . count($rows) . ' affectation(s)');

        return ['success' => true, 'message' => 'Export généré.', 'contenu' => $content, 'nom' => $filename];
    }

    private function applyTransfer(string $numEmp, string $nouveauLieu): void
    {
        Database::statement(
            'UPDATE employes SET lieu = :lieu, updated_at = :now WHERE numEmp = :id',
            ['lieu' => $nouveauLieu, 'now' => date('Y-m-d H:i:s'), 'id' => $numEmp],
        );
    }
}
