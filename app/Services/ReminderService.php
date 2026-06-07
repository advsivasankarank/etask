<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ReminderRepository;
use RuntimeException;
use Throwable;

final class ReminderService
{
    public function __construct(
        private readonly ReminderRepository $reminders = new ReminderRepository()
    ) {
    }

    public function overview(): array
    {
        return [
            'summary' => $this->reminders->summary(),
            'templates' => $this->reminders->templates(),
            'escalation_rules' => $this->reminders->escalationRules(),
        ];
    }

    public function templateOptions(): array
    {
        return [
            'reminder_types' => $this->reminderTypes(),
            'channels' => ['IN_APP', 'EMAIL', 'WHATSAPP'],
        ];
    }

    public function escalationOptions(): array
    {
        return [
            'reminder_types' => $this->reminderTypes(),
            'target_types' => ['ASSIGNED_USER', 'CLIENT_CONTACT', 'ROLE'],
            'channels' => ['IN_APP', 'EMAIL', 'WHATSAPP'],
            'roles' => $this->reminders->roleOptions(),
        ];
    }

    public function saveTemplate(array $input): int
    {
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $reminderType = trim((string) ($input['reminder_type'] ?? ''));
        $channel = trim((string) ($input['channel'] ?? 'IN_APP'));
        $message = trim((string) ($input['message'] ?? ''));

        if ($code === '' || $reminderType === '' || $channel === '' || $message === '') {
            throw new RuntimeException('Code, reminder type, channel, and message are required.');
        }

        $payload = [
            'code' => $code,
            'reminder_type' => $reminderType,
            'channel' => $channel,
            'subject' => trim((string) ($input['subject'] ?? '')) ?: null,
            'message' => $message,
            'is_active' => ((string) ($input['is_active'] ?? '1')) === '1' ? 1 : 0,
        ];

        $templateId = (int) ($input['id'] ?? 0) ?: null;
        $savedId = $this->reminders->saveTemplate($payload, $templateId);
        Logger::info('reminders.template_saved', ['template_id' => $savedId, 'actor_user_id' => Auth::id()]);

        return $savedId;
    }

    public function saveEscalationRule(array $input): int
    {
        $reminderType = trim((string) ($input['reminder_type'] ?? ''));
        $targetType = trim((string) ($input['target_type'] ?? ''));
        $channel = trim((string) ($input['channel'] ?? 'IN_APP'));
        $dayOffset = (int) ($input['day_offset'] ?? 0);

        if ($reminderType === '' || $targetType === '' || $channel === '' || $dayOffset < 0) {
            throw new RuntimeException('Reminder type, day offset, target type, and channel are required.');
        }

        $payload = [
            'reminder_type' => $reminderType,
            'day_offset' => $dayOffset,
            'target_type' => $targetType,
            'target_role_code' => trim((string) ($input['target_role_code'] ?? '')) ?: null,
            'channel' => $channel,
            'is_active' => ((string) ($input['is_active'] ?? '1')) === '1' ? 1 : 0,
        ];

        $ruleId = (int) ($input['id'] ?? 0) ?: null;
        $savedId = $this->reminders->saveEscalationRule($payload, $ruleId);
        Logger::info('reminders.escalation_rule_saved', ['rule_id' => $savedId, 'actor_user_id' => Auth::id()]);

        return $savedId;
    }

    public function register(array $filters, int $page = 1): array
    {
        return $this->reminders->registerReport($filters, $page, 25);
    }

    public function pendingReport(array $filters, int $page = 1): array
    {
        return $this->reminders->pendingReport($filters, $page, 25);
    }

    public function effectivenessReport(array $filters): array
    {
        return $this->reminders->effectivenessReport($filters);
    }

    public function escalationReport(array $filters, int $page = 1): array
    {
        return $this->reminders->escalationReport($filters, $page, 25);
    }

    public function runScheduler(): array
    {
        return (new ReminderSchedulerService())->run();
    }

    public function reminderTypes(): array
    {
        return [
            'PENDING_DOCUMENTS',
            'PENDING_PSO',
            'PENDING_SERVICE_ORDERS',
            'WORKFLOW_FOLLOW_UP',
            'INVOICE_DUE',
            'OVERDUE_INVOICE',
            'CONSULTANT_DELIVERABLES',
            'CLIENT_CLARIFICATION_PENDING',
            'COMPLIANCE_DUE_DATES',
            'E_VERIFICATION',
        ];
    }

    public function runInTransaction(callable $callback): mixed
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $result = $callback();
            $connection->commit();
            return $result;
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }
}
