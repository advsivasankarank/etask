<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReminderRepository;

final class DashboardNotificationChannel
{
    public function __construct(
        private readonly ReminderRepository $reminders = new ReminderRepository()
    ) {
    }

    public function send(array $reminder, array $template, array $rendered, ?int $recipientUserId, ?int $recipientContactId): array
    {
        $notificationId = $this->reminders->createNotification([
            'user_id' => $recipientUserId,
            'client_contact_id' => $recipientContactId,
            'reminder_id' => (int) $reminder['id'],
            'template_id' => (int) ($template['id'] ?? 0) ?: null,
            'channel' => 'IN_APP',
            'subject' => $rendered['subject'] ?: null,
            'message' => $rendered['message'],
            'recipient_email' => null,
            'error_message' => null,
            'payload_json' => json_encode($rendered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'linked_module' => (string) ($reminder['linked_module'] ?? 'REMINDER'),
            'linked_id' => (int) ($reminder['linked_id'] ?? 0) ?: null,
            'sent_at' => date('Y-m-d H:i:s'),
            'delivery_status' => 'SENT',
            'delivery_attempts' => 1,
        ]);

        return [
            'status' => 'SENT',
            'notification_id' => $notificationId,
            'error' => null,
        ];
    }
}
