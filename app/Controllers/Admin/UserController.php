<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\ActivityAware;

/**
 * Administration des comptes : création, modification, activation,
 * réinitialisation de mot de passe et suppression protégée.
 */
final class UserController extends Controller
{
    use ActivityAware;

    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(Request $request): Response
    {
        $search = (string) $request->query('q', '');
        $role = (string) $request->query('role', '');

        return $this->admin('admin.users.index', [
            'meta'   => ['title' => 'Utilisateurs — Administration AFFECTA', 'robots' => 'noindex'],
            'users'  => $this->users->listing($search, $role),
            'roles'  => ['admin' => 'Administrateur', 'manager' => 'Responsable', 'viewer' => 'Consultation'],
            'search' => $search,
            'role'   => $role,
            'pageTitle' => 'Utilisateurs',
            'pageLead'  => 'Comptes autorisés, rôles et derniers accès.',
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = $this->validate($request, [
            'name'     => 'required|min:3|max:120',
            'email'    => 'required|email|max:190',
            'password' => 'required|strong_password',
            'role'     => 'required|in:admin,manager,viewer',
            'job_title' => 'nullable|max:120',
        ], [
            'name'      => 'Nom complet',
            'email'     => 'Email',
            'password'  => 'Mot de passe',
            'role'      => 'Rôle',
            'job_title' => 'Fonction',
        ]);

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/admin/utilisateurs');
        }

        $data = $validator->validated();

        if ($this->users->emailTaken((string) $data['email'])) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Cette adresse email est déjà utilisée.',
                'errors'  => ['email' => ['Email déjà utilisé.']],
            ], '/admin/utilisateurs');
        }

        $id = $this->users->insert([
            'name'       => $data['name'],
            'email'      => mb_strtolower((string) $data['email']),
            'password'   => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role'       => $data['role'],
            'job_title'  => $data['job_title'] ?: null,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log('user.create', 'user', (string) $id, 'Création d\'un compte utilisateur');

        return $this->respond($request, [
            'success' => true,
            'message' => 'Compte créé. Transmettez le mot de passe par un canal sécurisé.',
        ], '/admin/utilisateurs');
    }

    public function update(Request $request, string $id): Response
    {
        $user = $this->users->find((int) $id);

        if ($user === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Utilisateur introuvable.'], '/admin/utilisateurs');
        }

        $validator = $this->validate($request, [
            'name'      => 'required|min:3|max:120',
            'email'     => 'required|email|max:190',
            'role'      => 'required|in:admin,manager,viewer',
            'job_title' => 'nullable|max:120',
            'password'  => 'nullable|strong_password',
            'is_active' => 'nullable|in:0,1',
        ], [
            'name'    => 'Nom complet',
            'email'   => 'Email',
            'role'    => 'Rôle',
            'password' => 'Nouveau mot de passe',
        ]);

        if ($validator->fails()) {
            return $this->respond($request, [
                'success' => false,
                'message' => $validator->firstError(),
                'errors'  => $validator->errors(),
            ], '/admin/utilisateurs');
        }

        $data = $validator->validated();

        if ($this->users->emailTaken((string) $data['email'], (int) $id)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Cette adresse email est déjà utilisée par un autre compte.',
                'errors'  => ['email' => ['Email déjà utilisé.']],
            ], '/admin/utilisateurs');
        }

        // Un administrateur ne peut pas se retirer ses propres droits ni se désactiver.
        $self = (int) ($user['id'] === (int) (auth_user()['id'] ?? 0));
        $isActive = (int) $request->input('is_active', 1);

        if ($self && ($data['role'] !== 'admin' || $isActive !== 1)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre rôle ni désactiver votre compte.',
            ], '/admin/utilisateurs');
        }

        $payload = [
            'name'       => $data['name'],
            'email'      => mb_strtolower((string) $data['email']),
            'role'       => $data['role'],
            'job_title'  => $data['job_title'] ?: null,
            'is_active'  => $isActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        $this->users->update((int) $id, $payload);
        $this->log('user.update', 'user', (string) $id, 'Mise à jour d\'un compte');

        return $this->respond($request, ['success' => true, 'message' => 'Compte mis à jour.'], '/admin/utilisateurs');
    }

    public function destroy(Request $request, string $id): Response
    {
        $userId = (int) $id;

        if ($userId === (int) (auth_user()['id'] ?? 0)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], '/admin/utilisateurs');
        }

        if ((int) Database::scalar('SELECT COUNT(*) FROM users') <= 1) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Le dernier compte administrateur ne peut pas être supprimé.',
            ], '/admin/utilisateurs');
        }

        $this->users->delete($userId);
        $this->log('user.delete', 'user', (string) $userId, 'Suppression d\'un compte');

        return $this->respond($request, ['success' => true, 'message' => 'Compte supprimé.'], '/admin/utilisateurs');
    }
}
