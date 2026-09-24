<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\ContactMessage;
use App\Services\Mailer;
use App\Services\Validator;

/**
 * Formulaire de contact : validation serveur, protection CSRF (middleware),
 * anti-spam (honeypot, délai minimal, limitation de débit) et envoi d'email.
 */
final class ContactController extends Controller
{
    public function store(Request $request): Response
    {
        $payload = $request->only('name', 'email', 'phone', 'subject', 'message', 'consent', 'website', 'form_started_at', 'rgpd');

        // ── 1. Anti-spam : pot de miel + délai de remplissage ────────────────
        if (!empty($payload['website'])) {
            // Réponse neutre : on ne renseigne pas le robot sur le motif du rejet.
            return $this->ok('Merci, votre message a bien été pris en compte.', ['redirect' => '/#contact']);
        }

        $minDelay = (int) config('app.contact.min_delay', 3);
        $startedAt = (int) ($payload['form_started_at'] ?? 0);

        if ($startedAt > 0 && (time() - $startedAt) < $minDelay) {
            return $this->fail(
                'Votre envoi a été jugé trop rapide. Merci de compléter le formulaire puis de réessayer.',
                [],
                422,
            );
        }

        // ── 2. Anti-spam : volume par IP sur la fenêtre configurée ──────────
        $window = (int) config('app.contact.rate_window', 3600);
        $recent = (new ContactMessage())->recentCountFrom($request->ip(), $window);

        if ($recent >= (int) config('app.contact.rate_limit', 5)) {
            return $this->fail(
                'Nous avons reçu plusieurs messages depuis votre connexion. Merci de réessayer plus tard ou de nous appeler directement.',
                [],
                429,
            );
        }

        // ── 3. Validation serveur ───────────────────────────────────────────
        $validator = new Validator($payload, [
            'name'    => 'required|min:2|max:120',
            'email'   => 'required|email|max:190',
            'phone'   => 'nullable|phone|max:40',
            'subject' => 'required|min:3|max:160',
            'message' => 'required|min:20|max:4000',
            'consent' => 'required',
        ], [
            'name'    => 'Nom complet',
            'email'   => 'Email professionnel',
            'phone'   => 'Téléphone',
            'subject' => 'Sujet',
            'message' => 'Message',
            'consent' => 'Consentement',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->firstError(), $validator->errors());
        }

        $data = $validator->validated();

        // ── 4. Persistance ──────────────────────────────────────────────────
        $id = Database::transaction(static fn (): int => ContactMessage::create([
            'name'       => $data['name'],
            'email'      => mb_strtolower((string) $data['email']),
            'phone'      => $data['phone'] ?: null,
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'status'     => 'new',
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        // ── 5. Notification email (driver `log` par défaut) ────────────────
        $recipient = (string) config('app.contact.recipient', 'contact@affecta.dev');
        $mailer = new Mailer();

        $mailer->send(
            $recipient,
            '[Contact] ' . $data['subject'],
            $this->notificationBody($data, $request->ip(), $id),
            (string) $data['email'],
        );

        $mailer->send(
            (string) $data['email'],
            'Nous avons bien reçu votre message — AFFECTA',
            $this->acknowledgementBody((string) $data['name']),
        );

        return $this->ok(
            'Merci ! Votre message a été transmis à notre équipe. Réponse sous 24 heures ouvrées.',
            ['redirect' => null, 'reload' => false],
        );
    }

    /** @param array<string, mixed> $data */
    private function notificationBody(array $data, string $ip, int $id): string
    {
        $row = static fn (string $label, string $value): string =>
            '<tr><td style="padding:6px 12px 6px 0;color:#667085;font-size:13px">' . $label
            . '</td><td style="padding:6px 0;font-size:14px;color:#0a0c12">' . e($value) . '</td></tr>';

        return '<div style="font-family:Arial,sans-serif;max-width:600px">'
            . '<h2 style="font-size:18px">Nouveau message de contact #' . $id . '</h2>'
            . '<table style="border-collapse:collapse">'
            . $row('Nom', (string) $data['name'])
            . $row('Email', (string) $data['email'])
            . $row('Téléphone', (string) ($data['phone'] ?: '—'))
            . $row('Sujet', (string) $data['subject'])
            . $row('IP', $ip)
            . '</table>'
            . '<p style="white-space:pre-wrap;font-size:14px;color:#2c3341;margin-top:16px">'
            . e((string) $data['message']) . '</p></div>';
    }

    private function acknowledgementBody(string $name): string
    {
        return '<div style="font-family:Arial,sans-serif;max-width:600px">'
            . '<h2 style="font-size:18px">Bonjour ' . e($name) . ',</h2>'
            . '<p style="font-size:14px;color:#2c3341">Nous avons bien reçu votre message et il a été '
            . 'transmis à l\'équipe concernée. Vous recevrez une réponse sous 24 heures ouvrées.</p>'
            . '<p style="font-size:14px;color:#2c3341">Pour toute urgence, vous pouvez nous joindre au '
            . e((string) config('app.company.phone', '')) . '.</p>'
            . '<p style="font-size:14px;color:#667085">— L\'équipe AFFECTA</p></div>';
    }
}
