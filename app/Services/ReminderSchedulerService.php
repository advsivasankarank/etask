<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Repositories\ReminderRepository;
use DateTimeImmutable;

final class ReminderSchedulerService
{
    public function __construct(
        private readonly ReminderRepository $reminders = new ReminderRepository(),
        private readonly DashboardNotificationChannel $dashboardChannel = new DashboardNotificationChannel(),
        private readonly EmailNotificationChannel $emailChannel = new EmailNotificationChannel()
    ) {
    }

    public function run(): array
    {
        $created = 0;
        $triggered = 0;
        $escalated = 0;

        $created += $this->syncReminderType('PENDING_DOCUMENTS', $this->reminders->candidatePendingDocuments());
        $created += $this->syncReminderType('PENDING_PSO', $this->reminders->candidatePendingPso());
        $created += $this->syncReminderType('PENDING_SERVICE_ORDERS', $this->reminders->candidatePendingServiceOrders());
        $created += $this->syncReminderType('WORKFLOW_FOLLOW_UP', $this->reminders->candidateWorkflowFollowUps());
        $created += $this->syncReminderType('INVOICE_DUE', $this->reminders->candidateInvoiceDue());
        $created += $this->syncReminderType('OVERDUE_INVOICE', $this->reminders->candidateOverdueInvoices());
        $created += $this->syncReminderType('CONSULTANT_DELIVERABLES', $this->reminders->candidateConsultantDeliverables());
        $created += $this->syncReminderType('CLIENT_CLARIFICATION_PENDING', $this->reminders->candidateClientClarifications());
        $created += $this->syncReminderType('COMPLIANCE_DUE_DATES', $this->reminders->candidateComplianceDueDates());

        foreach ($this->reminders->dueRemindersForDispatch() as $reminder) {
            $triggered += $this->dispatchReminder($reminder);
        }

        foreach ($this->reminders->escalationCandidates() as $reminder) {
            $escalated += $this->processEscalation($reminder);
        }

        Logger::info('reminders.scheduler_completed', [
            'created' => $created,
            'triggered' => $triggered,
            'escalated' => $escalated,
        ]);

        return [
            'created' => $created,
            'triggered' => $triggered,
            'escalated' => $escalated,
        ];
    }

    private function syncReminderType(string $reminderType, array $rows): int
    {
        $created = 0;
        $activeKeys = [];

        foreach ($rows as $row) {
            $dedupeKey = $this->dedupeKey($reminderType, $row);
            $activeKeys[$dedupeKey] = true;

            if ($this->reminders->openReminderByDedupeKey($dedupeKey) !== null) {
                continue;
            }

            $context = $this->normalizeContext($reminderType, $row, $dedupeKey);
            $template = $this->firstTemplateId($reminderType);
            $reminderId = $this->reminders->createReminder([
                'service_order_id' => $context['service_order_id'],
                'client_id' => $context['client_id'],
                'pso_id' => $context['pso_id'],
                'invoice_id' => $context['invoice_id'],
                'consultant_assignment_id' => $context['consultant_assignment_id'],
                'reminder_type' => $reminderType,
                'reminder_code' => $dedupeKey,
                'schedule_day_no' => null,
                'due_at' => $context['due_at'],
                'status' => 'PENDING',
                'assigned_to' => $context['assigned_to'],
                'recipient_contact_id' => $context['recipient_contact_id'],
                'recipient_email' => $context['recipient_email'],
                'created_by' => null,
                'template_id' => $template,
                'linked_module' => $context['linked_module'],
                'linked_id' => $context['linked_id'],
                'title' => $context['title'],
                'notes' => $context['notes'],
                'escalation_level' => 0,
                'dedupe_key' => $dedupeKey,
                'created_via' => 'SCHEDULER',
            ]);
            $this->reminders->insertReminderLog($reminderId, 'CREATED', null, 'Auto-created by reminder scheduler.');
            $created++;
        }

        foreach ($this->reminders->openRemindersForSync($reminderType) as $openReminder) {
            if (!isset($activeKeys[(string) $openReminder['dedupe_key']])) {
                $this->reminders->resolveReminder((int) $openReminder['id'], 'Resolved automatically because the source condition is no longer active.');
            }
        }

        return $created;
    }

    private function dispatchReminder(array $reminder): int
    {
        $count = 0;
        $templates = $this->reminders->activeTemplatesByType((string) $reminder['reminder_type']);

        foreach ($templates as $template) {
            $rendered = $this->renderTemplate($template, $reminder);
            $recipient = $this->resolveRecipient($reminder);
            if ($recipient === null) {
                continue;
            }

            if ((string) $template['channel'] === 'IN_APP') {
                $result = $this->dashboardChannel->send($reminder, $template, $rendered, $recipient['user_id'], $recipient['contact_id']);
            } elseif ((string) $template['channel'] === 'EMAIL') {
                $result = $this->emailChannel->send((string) ($recipient['email'] ?? ''), (string) $rendered['subject'], (string) $rendered['message']);
            } else {
                $result = ['status' => 'SKIPPED', 'error' => 'Channel not implemented yet.'];
            }

            $this->reminders->deliveryLog([
                'reminder_id' => (int) $reminder['id'],
                'template_id' => (int) ($template['id'] ?? 0) ?: null,
                'recipient_user_id' => $recipient['user_id'],
                'recipient_contact_id' => $recipient['contact_id'],
                'recipient_email' => $recipient['email'],
                'delivery_channel' => (string) $template['channel'],
                'delivery_status' => (string) $result['status'],
                'error_message' => $result['error'],
                'notification_id' => $result['notification_id'] ?? null,
            ]);
            $this->reminders->insertReminderLog((int) $reminder['id'], 'NOTIFIED', null, 'Reminder sent via ' . $template['channel'] . ' with status ' . $result['status']);
            $count++;
        }

        $this->reminders->markReminderTriggered((int) $reminder['id'], ((string) ($reminder['due_at'] ?? '')) < date('Y-m-d H:i:s') ? 'OVERDUE' : 'SENT');

        return $count;
    }

    private function processEscalation(array $reminder): int
    {
        $dueAt = new DateTimeImmutable((string) $reminder['due_at']);
        $daysOpen = max(0, (int) $dueAt->diff(new DateTimeImmutable())->format('%a'));
        $count = 0;

        foreach ($this->reminders->activeEscalationRulesByType((string) $reminder['reminder_type']) as $rule) {
            $dayOffset = (int) ($rule['day_offset'] ?? 0);
            if ($daysOpen < $dayOffset || (int) ($reminder['escalation_level'] ?? 0) >= $dayOffset) {
                continue;
            }

            $recipient = $this->resolveEscalationRecipient($reminder, $rule);
            if ($recipient === null) {
                continue;
            }

            $rendered = [
                'subject' => 'Escalation: ' . ((string) ($reminder['title'] ?? $reminder['reminder_type'])),
                'message' => 'Escalation triggered for reminder type ' . $reminder['reminder_type'] . ' for ' . ($reminder['client_name'] ?? 'record') . '.',
            ];

            if ((string) $rule['channel'] === 'IN_APP') {
                $result = $this->dashboardChannel->send($reminder, ['id' => null], $rendered, $recipient['user_id'], $recipient['contact_id']);
            } elseif ((string) $rule['channel'] === 'EMAIL') {
                $result = $this->emailChannel->send((string) ($recipient['email'] ?? ''), $rendered['subject'], $rendered['message']);
            } else {
                $result = ['status' => 'SKIPPED', 'error' => 'Channel not implemented yet.'];
            }

            $this->reminders->deliveryLog([
                'reminder_id' => (int) $reminder['id'],
                'template_id' => null,
                'recipient_user_id' => $recipient['user_id'],
                'recipient_contact_id' => $recipient['contact_id'],
                'recipient_email' => $recipient['email'],
                'delivery_channel' => (string) $rule['channel'],
                'delivery_status' => (string) $result['status'],
                'error_message' => $result['error'],
                'notification_id' => $result['notification_id'] ?? null,
            ]);
            $this->reminders->updateEscalationLevel((int) $reminder['id'], $dayOffset);
            $this->reminders->insertReminderLog((int) $reminder['id'], 'ESCALATED', null, 'Escalated on day ' . $dayOffset . ' through ' . $rule['channel']);
            $count++;
        }

        return $count;
    }

    private function resolveRecipient(array $reminder): ?array
    {
        if (!empty($reminder['assigned_to'])) {
            return [
                'user_id' => (int) $reminder['assigned_to'],
                'contact_id' => null,
                'email' => (string) ($reminder['assigned_user_email'] ?? ''),
            ];
        }

        if (!empty($reminder['recipient_contact_id'])) {
            return [
                'user_id' => null,
                'contact_id' => (int) $reminder['recipient_contact_id'],
                'email' => (string) (($reminder['recipient_email'] ?? '') ?: ($reminder['contact_email'] ?? '')),
            ];
        }

        if (!empty($reminder['recipient_email'])) {
            return [
                'user_id' => null,
                'contact_id' => null,
                'email' => (string) $reminder['recipient_email'],
            ];
        }

        return null;
    }

    private function resolveEscalationRecipient(array $reminder, array $rule): ?array
    {
        return match ((string) ($rule['target_type'] ?? '')) {
            'ASSIGNED_USER' => $this->resolveRecipient($reminder),
            'CLIENT_CONTACT' => [
                'user_id' => null,
                'contact_id' => !empty($reminder['recipient_contact_id']) ? (int) $reminder['recipient_contact_id'] : null,
                'email' => (string) (($reminder['recipient_email'] ?? '') ?: ($reminder['client_email'] ?? '')),
            ],
            'ROLE' => $this->resolveRoleRecipient((string) ($rule['target_role_code'] ?? '')),
            default => null,
        };
    }

    private function resolveRoleRecipient(string $roleCode): ?array
    {
        $user = $this->reminders->firstActiveUserByRoles([$roleCode]);
        if ($user === null) {
            return null;
        }

        return [
            'user_id' => (int) $user['id'],
            'contact_id' => null,
            'email' => (string) ($user['email'] ?? ''),
        ];
    }

    private function renderTemplate(array $template, array $context): array
    {
        $map = [
            '{{client_name}}' => (string) ($context['client_name'] ?? ''),
            '{{so_no}}' => (string) ($context['so_no'] ?? ''),
            '{{pso_no}}' => (string) ($context['pso_no'] ?? ''),
            '{{invoice_no}}' => (string) ($context['invoice_no'] ?? ''),
            '{{current_stage}}' => (string) ($context['current_stage_code'] ?? ''),
            '{{due_at}}' => (string) ($context['due_at'] ?? ''),
            '{{amount}}' => isset($context['net_payable']) ? number_format((float) $context['net_payable'], 2) : '',
        ];

        return [
            'subject' => strtr((string) ($template['subject'] ?? ''), $map),
            'message' => strtr((string) ($template['message'] ?? ''), $map),
        ];
    }

    private function dedupeKey(string $reminderType, array $row): string
    {
        foreach (['query_id', 'invoice_id', 'consultant_assignment_id', 'pso_id', 'service_order_id'] as $field) {
            if (!empty($row[$field])) {
                return $reminderType . ':' . $field . ':' . (string) $row[$field];
            }
        }

        return $reminderType . ':' . md5(json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');
    }

    private function normalizeContext(string $reminderType, array $row, string $dedupeKey): array
    {
        $dueAt = date('Y-m-d 09:00:00');
        if (!empty($row['due_date'])) {
            $dueAt = date('Y-m-d 09:00:00', strtotime((string) $row['due_date']));
        } elseif (!empty($row['sla_due_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime((string) $row['sla_due_at']));
        } elseif (!empty($row['reference_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime((string) $row['reference_at'] . ' +1 day'));
        } elseif (!empty($row['raised_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime((string) $row['raised_at'] . ' +1 day'));
        } elseif (!empty($row['assigned_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime((string) $row['assigned_at'] . ' +1 day'));
        }

        return [
            'service_order_id' => isset($row['service_order_id']) ? (int) $row['service_order_id'] : null,
            'client_id' => isset($row['client_id']) ? (int) $row['client_id'] : null,
            'pso_id' => isset($row['pso_id']) ? (int) $row['pso_id'] : null,
            'invoice_id' => isset($row['invoice_id']) ? (int) $row['invoice_id'] : null,
            'consultant_assignment_id' => isset($row['consultant_assignment_id']) ? (int) $row['consultant_assignment_id'] : null,
            'due_at' => $dueAt,
            'assigned_to' => isset($row['assigned_user_id']) ? ((int) $row['assigned_user_id'] ?: null) : null,
            'recipient_contact_id' => isset($row['recipient_contact_id']) ? ((int) $row['recipient_contact_id'] ?: null) : null,
            'recipient_email' => isset($row['recipient_email']) ? (string) $row['recipient_email'] : (isset($row['client_email']) ? (string) $row['client_email'] : null),
            'linked_module' => match ($reminderType) {
                'PENDING_PSO' => 'PSO',
                'INVOICE_DUE', 'OVERDUE_INVOICE' => 'INVOICE',
                default => 'SO',
            },
            'linked_id' => isset($row['invoice_id']) ? (int) $row['invoice_id']
                : (isset($row['pso_id']) ? (int) $row['pso_id']
                : (isset($row['service_order_id']) ? (int) $row['service_order_id']
                : (isset($row['consultant_assignment_id']) ? (int) $row['consultant_assignment_id'] : null))),
            'title' => str_replace('_', ' ', $reminderType) . ' - ' . ((string) ($row['client_name'] ?? 'Record')),
            'notes' => 'Generated by scheduler with dedupe key ' . $dedupeKey,
        ];
    }

    private function firstTemplateId(string $reminderType): ?int
    {
        $templates = $this->reminders->activeTemplatesByType($reminderType);
        return $templates === [] ? null : (int) $templates[0]['id'];
    }
}
