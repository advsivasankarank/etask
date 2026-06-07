<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;

final class EmailNotificationChannel
{
    public function send(string $toEmail, string $subject, string $message): array
    {
        $toEmail = trim($toEmail);
        if ($toEmail === '') {
            return ['status' => 'FAILED', 'error' => 'Recipient email is empty.'];
        }

        $fromName = (string) config('app.mail_from_name', 'e-Pani');
        $fromEmail = (string) config('app.mail_from_address', 'noreply@localhost.test');

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
        ];

        $sent = @mail($toEmail, $subject, $message, implode("\r\n", $headers));

        Logger::info('notifications.email_attempt', [
            'to_email' => $toEmail,
            'subject' => $subject,
            'sent' => $sent,
        ]);

        return [
            'status' => $sent ? 'SENT' : 'FAILED',
            'error' => $sent ? null : 'mail() returned false.',
        ];
    }
}
