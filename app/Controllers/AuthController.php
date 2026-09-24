<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Services\ActivityAware;

/**
 * Authentification : session sécurisée, limitation des tentatives,
 * régénération d'identifiant et journalisation des accès.
 */
final class AuthController extends Controller
{
    use ActivityAware;

    private const MAX_ATTEMPTS = 5;
    private const LOCK_WINDOW = 900; // 15 minutes

    public function showLogin(Request $request): Response
    {
        return $this->view('auth.login', [
            'meta' => [
                'title'       => 'Connexion — Espace AFFECTA',
                'description' => 'Accédez à votre espace de pilotage des affectations.',
                'robots'      => 'noindex, nofollow',
            ],
        ], null);
    }

    public function login(Request $request): Response
    {
        $email = mb_strtolower((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $validator = $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], ['email' => 'Email', 'password' => 'Mot de passe']);

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/login', ['email' => $email]);
        }

        // Anti-bruteforce : comptage des échecs récents par email + IP.
        $attempts = (int) Database::scalar(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = :email AND ip = :ip AND successful = 0 AND attempted_at >= :since',
            ['email' => $email, 'ip' => $request->ip(), 'since' => date('Y-m-d H:i:s', time() - self::LOCK_WINDOW)],
        );

        if ($attempts >= self::MAX_ATTEMPTS) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Trop de tentatives de connexion. Merci de réessayer dans quelques minutes.',
            ], '/login', ['email' => $email]);
        }

        $user = (new User())->findByEmail($email);

        // Message volontairement identique dans les deux cas (pas d'énumération de comptes).
        $invalid = ['success' => false, 'message' => 'Identifiants incorrects.'];

        if ($user === null || !password_verify($password, (string) $user['password'])) {
            $this->recordAttempt($email, $request->ip(), false);

            return $this->respond($request, $invalid, '/login', ['email' => $email]);
        }

        if ((int) $user['is_active'] !== 1) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Ce compte est désactivé. Contactez un administrateur.',
            ], '/login', ['email' => $email]);
        }

        // Ré-hachage transparent si l'algorithme par défaut a évolué.
        if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
            Database::statement(
                'UPDATE users SET password = :hash WHERE id = :id',
                ['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']],
            );
        }

        session_regenerate_id(true);
        $this->recordAttempt($email, $request->ip(), true);

        Session::put('auth_user', [
            'id'           => (int) $user['id'],
            'name'         => (string) $user['name'],
            'email'        => (string) $user['email'],
            'role'         => (string) $user['role'],
            'job_title'    => (string) ($user['job_title'] ?? ''),
            'avatar_path'  => $user['avatar_path'] ?? null,
            'is_active'    => (int) $user['is_active'],
        ]);

        (new User())->touchLogin((int) $user['id']);
        $this->log('auth.login', 'user', (string) $user['id'], 'Connexion réussie');

        $intended = Session::get('intended_url', '/app');
        Session::forget('intended_url');

        $destination = $user['role'] === 'admin' || str_starts_with((string) $intended, '/admin')
            ? (string) $intended
            : '/app';

        return $this->respond($request, [
            'success'  => true,
            'message'  => 'Connexion réussie. Bienvenue ' . $user['name'] . '.',
            'redirect' => $destination,
        ], $destination);
    }

    public function logout(Request $request): Response
    {
        $user = auth_user();

        if ($user !== null) {
            $this->log('auth.logout', 'user', (string) $user['id'], 'Déconnexion');
        }

        Session::destroy();

        return $this->redirect('/login', 'Vous êtes déconnecté. À bientôt.');
    }

    private function recordAttempt(string $email, string $ip, bool $successful): void
    {
        Database::statement(
            'INSERT INTO login_attempts (email, ip, successful, attempted_at) VALUES (:email, :ip, :successful, :at)',
            [
                'email'      => $email,
                'ip'         => $ip,
                'successful' => $successful ? 1 : 0,
                'at'         => date('Y-m-d H:i:s'),
            ],
        );
    }
}
