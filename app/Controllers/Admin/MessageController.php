<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContactMessage;
use App\Services\ActivityAware;

/**
 * Boîte de réception des messages de contact : consultation, statut, suppression.
 */
final class MessageController extends Controller
{
    use ActivityAware;

    private ContactMessage $messages;

    public function __construct()
    {
        $this->messages = new ContactMessage();
    }

    public function index(Request $request): Response
    {
        $status = (string) $request->query('statut', '');
        $term = (string) $request->query('q', '');

        $total = $this->messages->countFiltered($status, $term);
        $pagination = $this->paginate($request, $total, 20);

        return $this->admin('admin.messages.index', [
            'meta'       => ['title' => 'Messages — Administration AFFECTA', 'robots' => 'noindex'],
            'messages'   => $this->messages->listing($status, $term, $pagination['perPage'], $pagination['offset']),
            'statuses'   => ContactMessage::STATUSES,
            'status'     => $status,
            'term'       => $term,
            'pagination' => $pagination,
            'pageTitle'  => 'Messages de contact',
            'pageLead'   => 'Demandes entrantes, suivi du traitement et archivage.',
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $message = $this->messages->find((int) $id);

        if ($message === null) {
            return $this->redirect('/admin/messages', 'Message introuvable.', 'error');
        }

        $this->messages->markAsRead((int) $id);

        return $this->admin('admin.messages.show', [
            'meta'      => ['title' => 'Message #' . $id . ' — AFFECTA', 'robots' => 'noindex'],
            'message'   => $message,
            'statuses'  => ContactMessage::STATUSES,
            'pageTitle' => 'Message de ' . $message['name'],
            'pageLead'  => $message['subject'],
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        if ($this->messages->find((int) $id) === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Message introuvable.'], '/admin/messages');
        }

        $status = (string) $request->input('status', '');

        if (!array_key_exists($status, ContactMessage::STATUSES)) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Statut invalide.',
                'errors'  => ['status' => ['Statut non autorisé.']],
            ], '/admin/messages');
        }

        $this->messages->update((int) $id, [
            'status'     => $status,
            'read_at'    => $status === 'new' ? null : date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log('message.update', 'contact_message', (int) $id, 'Changement de statut d\'un message');

        return $this->respond($request, ['success' => true, 'message' => 'Statut du message mis à jour.'], '/admin/messages');
    }

    public function destroy(Request $request, string $id): Response
    {
        if ($this->messages->find((int) $id) === null) {
            return $this->respond($request, ['success' => false, 'message' => 'Message introuvable.'], '/admin/messages');
        }

        $this->messages->delete((int) $id);
        $this->log('message.delete', 'contact_message', (int) $id, 'Suppression d\'un message de contact');

        return $this->respond($request, ['success' => true, 'message' => 'Message supprimé.'], '/admin/messages');
    }
}
