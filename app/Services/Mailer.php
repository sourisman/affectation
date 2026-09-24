<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Service d'email minimal : driver `log` par défaut (aucune dépendance),
 * driver `smtp` dès que PHPMailer est installé et configuré.
 * Aucun identifiant n'est codé en dur : tout provient du .env.
 */
final class Mailer
{
    public function send(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
    {
        $driver = (string) Config::get('mail.driver', 'log');
        $from = Config::get('mail.from', []);

        if ($driver === 'smtp' && class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            return $this->sendSmtp($to, $subject, $htmlBody, $replyTo, $from);
        }

        return $this->sendToLog($to, $subject, $htmlBody, $replyTo);
    }

    private function sendSmtp(string $to, string $subject, string $htmlBody, ?string $replyTo, array $from): bool
    {
        try {
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = (string) Config::get('mail.host');
            $mailer->Port = (int) Config::get('mail.port', 587);
            $mailer->SMTPAuth = (string) Config::get('mail.username') !== '';
            $mailer->Username = (string) Config::get('mail.username');
            $mailer->Password = (string) Config::get('mail.password');
            $mailer->SMTPSecure = (string) Config::get('mail.encryption', 'tls');
            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom((string) ($from['address'] ?? 'no-reply@localhost'), (string) ($from['name'] ?? 'AFFECTA'));
            $mailer->addAddress($to);

            if ($replyTo !== null) {
                $mailer->addReplyTo($replyTo);
            }

            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = strip_tags($htmlBody);

            return $mailer->send();
        } catch (\Throwable $e) {
            error_log('[mail] ' . $e->getMessage());

            return $this->sendToLog($to, $subject, $htmlBody, $replyTo);
        }
    }

    private function sendToLog(string $to, string $subject, string $htmlBody, ?string $replyTo): bool
    {
        $directory = BASE_PATH . '/storage/logs';

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $entry = sprintf(
            "[%s]\nÀ       : %s\nRépondre: %s\nSujet   : %s\n\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $replyTo ?? '-',
            $subject,
            strip_tags(str_replace(['</p>', '<br>'], "\n", $htmlBody)),
            str_repeat('-', 72),
        );

        return (bool) @file_put_contents($directory . '/mail-' . date('Y-m-d') . '.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
