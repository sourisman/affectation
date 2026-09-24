<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\AffectationService;

/**
 * Tableau de bord d'administration : santé applicative, activité,
 * volumétrie de la base et messages non traités.
 */
final class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $users = new User();
        $messages = new ContactMessage();
        $affectations = new AffectationService();

        $activite = (new ActivityLog())->dailyActivity(14);
        $roles = $users->countByRole();

        return $this->admin('admin.dashboard', [
            'meta'      => ['title' => 'Administration — AFFECTA', 'robots' => 'noindex'],
            'totaux'    => [
                'users'        => $users->count(),
                'actifs'       => (int) Database::scalar('SELECT COUNT(*) FROM users WHERE is_active = 1'),
                'admins'       => $roles['admin'] ?? 0,
                'employes'     => (int) Database::scalar('SELECT COUNT(*) FROM employes'),
                'lieux'        => (int) Database::scalar('SELECT COUNT(*) FROM lieux'),
                'affectations' => (int) Database::scalar('SELECT COUNT(*) FROM affectations'),
                'messages'     => $messages->count(),
                'non_lus'      => $messages->unreadCount(),
            ],
            'activite'   => $activite,
            'dashboard'  => $affectations->dashboard(),
            'journal'    => (new ActivityLog())->latest(10),
            'derniers'   => $users->listing('', ''),
            'messages'   => $messages->listing('', '', 5, 0),
            'environnement' => [
                'php'      => PHP_VERSION,
                'driver'   => Database::driver(),
                'env'      => (string) config('app.env'),
                'debug'    => (bool) config('app.debug') ? 'activé' : 'désactivé',
                'memoire'  => round(memory_get_peak_usage(true) / 1048576, 1) . ' Mo',
            ],
            'pageTitle' => 'Vue d\'ensemble',
            'pageLead'  => 'Supervision de la plateforme et des comptes autorisés.',
        ]);
    }
}
