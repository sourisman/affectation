<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Services\AffectationService;

/**
 * Console opérationnelle : indicateurs consolidés, tendances et activité récente.
 */
final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $affectations = new AffectationService();
        $dashboard = $affectations->dashboard();
        $activity = (new ActivityLog())->dailyActivity(14);

        $lieux = \App\Core\Database::select(
            'SELECT l.design, l.province, COUNT(e.numEmp) AS effectif,
                    (SELECT COUNT(*) FROM affectations a
                     WHERE a.nouveauLieu = l.idlieu AND a.statut = \'planifie\') AS entrantes
             FROM lieux l
             LEFT JOIN employes e ON e.lieu = l.idlieu AND e.is_active = 1
             GROUP BY l.idlieu, l.design, l.province
             ORDER BY effectif DESC
             LIMIT 8',
        );

        $totaux = [
            'employes'  => (int) \App\Core\Database::scalar('SELECT COUNT(*) FROM employes WHERE is_active = 1'),
            'lieux'     => (int) \App\Core\Database::scalar('SELECT COUNT(*) FROM lieux WHERE is_active = 1'),
            'postes'    => (int) \App\Core\Database::scalar('SELECT COUNT(DISTINCT poste) FROM employes'),
            'inactifs'  => (int) \App\Core\Database::scalar('SELECT COUNT(*) FROM employes WHERE is_active = 0'),
            'messages'  => (new ContactMessage())->unreadCount(),
            'non_affectes' => count((new \App\Models\Employe())->nonAffectes()),
        ];

        $maxEffectif = max(array_map(static fn (array $l): int => (int) $l['effectif'], $lieux) ?: [1]);

        return $this->console('app.dashboard', [
            'meta' => ['title' => 'Tableau de bord — Espace AFFECTA', 'robots' => 'noindex'],
            'dashboard'    => $dashboard,
            'activity'     => $activity,
            'lieux'        => $lieux,
            'maxEffectif'  => $maxEffectif,
            'totaux'       => $totaux,
            'recentes'     => $dashboard['recents'],
            'pageTitle'    => 'Tableau de bord',
            'pageLead'     => 'Vue d\'ensemble des effectifs, des lieux et des mouvements en cours.',
        ]);
    }
}
