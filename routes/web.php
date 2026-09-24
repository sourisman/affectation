<?php

declare(strict_types=1);

/**
 * Table de routage de l'application.
 *
 * Chaque route reçoit la requête et renvoie une Reponse. Les middlewares
 * s'exécutent dans l'ordre de déclaration : security → auth → csrf, etc.
 */

use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AffectationController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EmployeController;
use App\Controllers\Admin\HistoriqueController;
use App\Controllers\Admin\LieuController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\RapportController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\UserController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\SitemapController;
use App\Core\Router;

return static function (Router $router): void {
    // Les en-têtes de sécurité s'appliquent à toutes les réponses.
    $router->group(['middleware' => ['security']], function (Router $router): void {
        /* ── Site public ──────────────────────────────────────────────── */
        $router->get('/', [HomeController::class, 'index'], 'home');
        $router->get('/services', [HomeController::class, 'services'], 'services');
        $router->get('/realisations', [HomeController::class, 'portfolio'], 'portfolio');
        $router->get('/a-propos', [HomeController::class, 'about'], 'about');
        $router->get('/legal/{page}', [HomeController::class, 'legal'], 'legal');

        $router->get('/sitemap.xml', [SitemapController::class, 'index'], 'sitemap');
        $router->get('/robots.txt', [SitemapController::class, 'robots'], 'robots');

        /* ── Contact (anti-spam + CSRF) ───────────────────────────────── */
        $router->post('/api/contact', [ContactController::class, 'store'], 'contact.store', ['csrf', 'throttle']);

        /* ── Authentification ─────────────────────────────────────────── */
        $router->get('/login', [AuthController::class, 'showLogin'], 'login', ['guest']);
        $router->post('/login', [AuthController::class, 'login'], 'login.attempt', ['guest', 'csrf', 'throttle']);
        $router->post('/logout', [AuthController::class, 'logout'], 'logout', ['auth', 'csrf']);

        /* ── Console métier (/app) ────────────────────────────────────── */
        $router->group(['prefix' => '/app', 'middleware' => ['auth']], function (Router $router): void {
            $router->get('', [DashboardController::class, 'index'], 'app.dashboard');

            // Employés
            $router->get('/employes', [EmployeController::class, 'index'], 'app.employes');
            $router->get('/employes/{numEmp}', [EmployeController::class, 'show'], 'app.employes.show');
            $router->post('/employes', [EmployeController::class, 'store'], 'app.employes.store', ['csrf']);
            $router->put('/employes/{numEmp}', [EmployeController::class, 'update'], 'app.employes.update', ['csrf']);
            $router->delete('/employes/{numEmp}', [EmployeController::class, 'destroy'], 'app.employes.destroy', ['csrf']);

            // Lieux
            $router->get('/lieux', [LieuController::class, 'index'], 'app.lieux');
            $router->get('/lieux/{idlieu}', [LieuController::class, 'show'], 'app.lieux.show');
            $router->post('/lieux', [LieuController::class, 'store'], 'app.lieux.store', ['csrf']);
            $router->put('/lieux/{idlieu}', [LieuController::class, 'update'], 'app.lieux.update', ['csrf']);
            $router->delete('/lieux/{idlieu}', [LieuController::class, 'destroy'], 'app.lieux.destroy', ['csrf']);

            // Affectations
            $router->get('/affectations', [AffectationController::class, 'index'], 'app.affectations');
            $router->get('/affectations/suivi', [AffectationController::class, 'board'], 'app.affectations.board');
            $router->get('/affectations/export', [AffectationController::class, 'export'], 'app.affectations.export');
            $router->get('/affectations/{id}', [AffectationController::class, 'show'], 'app.affectations.show');
            $router->post('/affectations', [AffectationController::class, 'store'], 'app.affectations.store', ['csrf']);
            $router->put('/affectations/{id}', [AffectationController::class, 'update'], 'app.affectations.update', ['csrf']);
            $router->post('/affectations/{id}/appliquer', [AffectationController::class, 'apply'], 'app.affectations.apply', ['csrf']);
            $router->post('/affectations/{id}/annuler', [AffectationController::class, 'cancel'], 'app.affectations.cancel', ['csrf']);
            $router->delete('/affectations/{id}', [AffectationController::class, 'destroy'], 'app.affectations.destroy', ['csrf']);

            // Historique & rapports
            $router->get('/historique', [HistoriqueController::class, 'index'], 'app.historique');
            $router->get('/rapports', [RapportController::class, 'index'], 'app.rapports');
            $router->get('/rapports/non-affectes', [RapportController::class, 'nonAffectes'], 'app.rapports.non_affectes');
            $router->get('/rapports/impression', [RapportController::class, 'print'], 'app.rapports.print');
        });

        /* ── Administration (/admin) ──────────────────────────────────── */
        $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function (Router $router): void {
            $router->get('', [AdminDashboardController::class, 'index'], 'admin.dashboard');

            // Utilisateurs
            $router->get('/utilisateurs', [UserController::class, 'index'], 'admin.users');
            $router->post('/utilisateurs', [UserController::class, 'store'], 'admin.users.store', ['csrf']);
            $router->put('/utilisateurs/{id}', [UserController::class, 'update'], 'admin.users.update', ['csrf']);
            $router->delete('/utilisateurs/{id}', [UserController::class, 'destroy'], 'admin.users.destroy', ['csrf']);

            // Messages de contact
            $router->get('/messages', [MessageController::class, 'index'], 'admin.messages');
            $router->get('/messages/{id}', [MessageController::class, 'show'], 'admin.messages.show');
            $router->put('/messages/{id}', [MessageController::class, 'update'], 'admin.messages.update', ['csrf']);
            $router->delete('/messages/{id}', [MessageController::class, 'destroy'], 'admin.messages.destroy', ['csrf']);

            // Configuration du site
            $router->get('/configuration', [SettingController::class, 'index'], 'admin.settings');
            $router->put('/configuration', [SettingController::class, 'update'], 'admin.settings.update', ['csrf']);

            // Raccourcis vers les modules métier (gestion des contenus et des données)
            $router->get('/contenus', [SettingController::class, 'index'], 'admin.content');
        });
    });
};
