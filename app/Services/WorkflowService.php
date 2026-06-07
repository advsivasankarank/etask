<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\WorkflowRepository;
use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class WorkflowService
{
    public function __construct(
        private readonly ServiceOrderRepository $serviceOrders = new ServiceOrderRepository(),
        private readonly WorkflowRepository $workflows = new WorkflowRepository()
    ) {
    }

    public function getWorkflowContext(int $serviceOrderId): array
    {
        $order = $this->serviceOrders->findDetailedById($serviceOrderId);
        if ($order === null) {
            throw new RuntimeException('Service order not found.');
        }

        $this->refreshDerivedStatuses($serviceOrderId, $order);
        $order = $this->serviceOrders->findDetailedById($serviceOrderId);

        return [
            'order' => $order,
            'stages' => $this->workflows->stageDefinitions((int) $order['workflow_definition_id']),
            'history' => $this->workflows->stageHistory($serviceOrderId),
            'reminders' => $this->workflows->reminders($serviceOrderId),
            'closures' => $this->workflows->closures($serviceOrderId),
            'rules' => $this->availableActions($order),
        ];
    }

    public function advanceMilestone(int $serviceOrderId, int $userId): void
    {
        $this->runInTransaction(function () use ($serviceOrderId, $userId): void {
            $order = $this->requireLockedOrder($serviceOrderId);
            $currentStage = (string) $order['current_stage_code'];
            $serviceType = (string) $order['service_type_code'];

            if ($currentStage === 'PAYMENT_PENDING') {
                throw new RuntimeException('Use payment entry to move beyond Payment Pending.');
            }

            if ($currentStage === 'FILING_DONE') {
                throw new RuntimeException('Acknowledgement or ARN must be captured before the workflow can move forward.');
            }

            if ($serviceType === 'ITR' && $currentStage === 'ACKNOWLEDGEMENT_CAPTURED') {
                throw new RuntimeException('ITR cases move to E-Verification Pending after acknowledgement capture.');
            }

            if ($currentStage === 'E_VERIFICATION_PENDING') {
                throw new RuntimeException('Use the e-verification completion action for ITR cases.');
            }

            $nextStage = $this->nextStage($order);
            if ($nextStage === null) {
                throw new RuntimeException('No further milestone is available from the current stage.');
            }

            if ($nextStage['code'] === 'PROCEDURALLY_CLOSED') {
                $this->completeProceduralClosureInsideTransaction($order, $userId, 'Milestone closure');
                return;
            }

            $this->transitionToStage($order, $userId, $nextStage['code'], 'MANUAL_MILESTONE', 'Manual milestone advancement');

            if ($nextStage['code'] === 'FILING_DONE') {
                $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                    'is_document_pending' => 0,
                    'is_filing_done' => 1,
                ]);
            }
        });
    }

    public function recordTaxPayment(int $serviceOrderId, int $userId, string $referenceNo): void
    {
        $referenceNo = trim($referenceNo);
        if ($referenceNo === '') {
            throw new RuntimeException('Payment reference is required.');
        }

        $this->runInTransaction(function () use ($serviceOrderId, $userId, $referenceNo): void {
            $order = $this->requireLockedOrder($serviceOrderId);

            if ((string) $order['current_stage_code'] !== 'PAYMENT_PENDING') {
                throw new RuntimeException('Payment can only be recorded while the workflow is in Payment Pending.');
            }

            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'payment_reference_no' => $referenceNo,
                'payment_recorded_at' => date('Y-m-d H:i:s'),
            ]);

            $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                'is_payment_pending' => 0,
                'is_paid' => 1,
            ]);

            $this->transitionToStage($order, $userId, 'PAID', 'AUTO_PAYMENT', 'Tax payment captured: ' . $referenceNo);
        });
    }

    public function captureAcknowledgement(int $serviceOrderId, int $userId, string $referenceNo): void
    {
        $referenceNo = trim($referenceNo);
        if ($referenceNo === '') {
            throw new RuntimeException('Acknowledgement / ARN reference is required.');
        }

        $this->runInTransaction(function () use ($serviceOrderId, $userId, $referenceNo): void {
            $order = $this->requireLockedOrder($serviceOrderId);

            if ((string) $order['current_stage_code'] !== 'FILING_DONE') {
                throw new RuntimeException('Acknowledgement can only be captured after Filing Done.');
            }

            $now = date('Y-m-d H:i:s');
            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'filing_reference_no' => $referenceNo,
                'acknowledgement_no' => $referenceNo,
                'acknowledgement_captured_at' => $now,
            ]);

            $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                'is_acknowledgement_captured' => 1,
            ]);

            $this->transitionToStage($order, $userId, 'ACKNOWLEDGEMENT_CAPTURED', 'AUTO_ACK_UPLOAD', 'Acknowledgement captured: ' . $referenceNo);

            if ((string) $order['service_type_code'] === 'ITR') {
                $updatedOrder = $this->requireLockedOrder($serviceOrderId);
                $this->transitionToStage($updatedOrder, $userId, 'E_VERIFICATION_PENDING', 'SYSTEM', 'ITR acknowledgement captured; e-verification window started');
                $this->createEVerificationReminders($updatedOrder, $userId, new DateTimeImmutable($now));
            }
        });
    }

    public function markEVerificationDone(int $serviceOrderId, int $userId, string $note = ''): void
    {
        $this->runInTransaction(function () use ($serviceOrderId, $userId, $note): void {
            $order = $this->requireLockedOrder($serviceOrderId);

            if ((string) $order['service_type_code'] !== 'ITR') {
                throw new RuntimeException('E-verification applies only to ITR workflows.');
            }

            if ((string) $order['current_stage_code'] !== 'E_VERIFICATION_PENDING') {
                throw new RuntimeException('E-verification can only be completed from E-Verification Pending.');
            }

            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'e_verification_completed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                'is_e_verification_done' => 1,
                'is_overdue' => 0,
            ]);
            $this->serviceOrders->markReminderDone((int) $order['id']);

            $transitionNote = trim($note) !== '' ? $note : 'E-verification completed';
            $this->transitionToStage($order, $userId, 'E_VERIFICATION_DONE', 'MANUAL_MILESTONE', $transitionNote);
        });
    }

    public function completeProceduralClosure(int $serviceOrderId, int $userId, string $note = ''): void
    {
        $this->runInTransaction(function () use ($serviceOrderId, $userId, $note): void {
            $order = $this->requireLockedOrder($serviceOrderId);
            $this->completeProceduralClosureInsideTransaction($order, $userId, $note);
        });
    }

    public function completeAccountingClosure(int $serviceOrderId, int $userId, string $note = ''): void
    {
        $this->runInTransaction(function () use ($serviceOrderId, $userId, $note): void {
            $order = $this->requireLockedOrder($serviceOrderId);

            if ((int) $order['is_client_paid'] !== 1) {
                $message = 'Accounting closure is blocked until the client has fully paid.';
                $this->serviceOrders->updateClosure((int) $order['id'], 'ACCOUNTING', 'BLOCKED', $message, $note, null);
                throw new RuntimeException($message);
            }

            $this->serviceOrders->markAccountingClosed((int) $order['id']);
            $this->serviceOrders->updateClosure((int) $order['id'], 'ACCOUNTING', 'COMPLETED', null, $note, $userId);
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $order['id'],
                'ACCOUNTING_CLOSE',
                'Accounting closure completed',
                'WORKFLOW'
            );
        });
    }

    public function completeFinalClosure(int $serviceOrderId, int $userId, string $note = ''): void
    {
        $this->runInTransaction(function () use ($serviceOrderId, $userId, $note): void {
            $order = $this->requireLockedOrder($serviceOrderId);

            if ((int) $order['is_client_paid'] !== 1) {
                $message = 'Final closure is blocked until the client has fully paid.';
                $this->serviceOrders->updateClosure((int) $order['id'], 'FINAL', 'BLOCKED', $message, $note, null);
                throw new RuntimeException($message);
            }

            $consultantOutstanding = $this->workflows->consultantOutstandingAmount((int) $order['id']);
            if ($consultantOutstanding > 0.0 || (int) $order['is_consultant_payment_pending'] === 1) {
                $message = 'Final closure is blocked because consultant payment is pending.';
                $this->serviceOrders->updateClosure((int) $order['id'], 'FINAL', 'BLOCKED', $message, $note, null);
                throw new RuntimeException($message);
            }

            if ($order['procedural_closed_at'] === null) {
                throw new RuntimeException('Final closure requires procedural closure first.');
            }

            if ($order['accounting_closed_at'] === null) {
                throw new RuntimeException('Final closure requires accounting closure first.');
            }

            $this->serviceOrders->markFinalClosed((int) $order['id'], $userId);
            $this->serviceOrders->updateClosure((int) $order['id'], 'FINAL', 'COMPLETED', null, $note, $userId);
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $order['id'],
                'FINAL_CLOSE',
                'Final closure completed and order locked',
                'WORKFLOW'
            );
        });
    }

    public function logEVerificationFollowUp(int $serviceOrderId, int $reminderId, int $userId, string $note): void
    {
        $note = trim($note);
        if ($note === '') {
            throw new RuntimeException('Follow-up note is required.');
        }

        $this->runInTransaction(function () use ($serviceOrderId, $reminderId, $userId, $note): void {
            $this->requireLockedOrder($serviceOrderId);
            $this->serviceOrders->logReminderFollowUp($reminderId, $userId, $note);
            Logger::info('workflow.follow_up_logged', [
                'service_order_id' => $serviceOrderId,
                'reminder_id' => $reminderId,
                'user_id' => $userId,
            ]);
        });
    }

    public function refreshDerivedStatuses(int $serviceOrderId, ?array $order = null): void
    {
        $order = $order ?? $this->serviceOrders->findDetailedById($serviceOrderId);
        if ($order === null) {
            return;
        }

        if ((string) $order['service_type_code'] === 'ITR'
            && (int) $order['is_e_verification_required'] === 1
            && (int) $order['is_e_verification_done'] === 0
            && !empty($order['e_verification_due_date'])
            && new DateTimeImmutable((string) $order['e_verification_due_date']) < new DateTimeImmutable('today')
        ) {
            $this->serviceOrders->updateStatusFlags((int) $order['id'], ['is_overdue' => 1]);
            $this->serviceOrders->markOverdueReminders((int) $order['id']);
        }
    }

    public function availableActions(array $order): array
    {
        $actions = [
            'can_advance' => false,
            'can_record_payment' => false,
            'can_capture_ack' => false,
            'can_mark_everification_done' => false,
            'can_procedural_close' => false,
            'can_accounting_close' => (int) ($order['is_client_paid'] ?? 0) === 1,
            'can_final_close' => false,
        ];

        $currentStage = (string) $order['current_stage_code'];
        $serviceType = (string) $order['service_type_code'];
        $actions['can_record_payment'] = $currentStage === 'PAYMENT_PENDING';
        $actions['can_capture_ack'] = $currentStage === 'FILING_DONE';
        $actions['can_mark_everification_done'] = $serviceType === 'ITR' && $currentStage === 'E_VERIFICATION_PENDING';
        $actions['can_advance'] = in_array($currentStage, ['DOCUMENT_PENDING', 'PREPARATION', 'REVIEW', 'PAID', 'FILING_PENDING', 'ACKNOWLEDGEMENT_CAPTURED'], true);
        $actions['can_procedural_close'] = ($serviceType === 'ITR' && $currentStage === 'E_VERIFICATION_DONE')
            || ($serviceType !== 'ITR' && $currentStage === 'ACKNOWLEDGEMENT_CAPTURED');

        $actions['can_final_close'] = $actions['can_accounting_close']
            && ($order['procedural_closed_at'] !== null)
            && (float) $this->workflows->consultantOutstandingAmount((int) $order['id']) <= 0.0
            && (int) ($order['is_consultant_payment_pending'] ?? 0) === 0;

        return $actions;
    }

    private function completeProceduralClosureInsideTransaction(array $order, int $userId, string $note): void
    {
        if (empty($order['acknowledgement_no'])) {
            throw new RuntimeException('Acknowledgement / ARN is mandatory before procedural closure.');
        }

        $serviceType = (string) $order['service_type_code'];
        $currentStage = (string) $order['current_stage_code'];

        if ($serviceType === 'ITR' && $currentStage !== 'E_VERIFICATION_DONE') {
            throw new RuntimeException('ITR procedural closure requires e-verification completion.');
        }

        if ($serviceType !== 'ITR' && $currentStage !== 'ACKNOWLEDGEMENT_CAPTURED') {
            throw new RuntimeException('Procedural closure requires acknowledgement capture first.');
        }

        $this->transitionToStage($order, $userId, 'PROCEDURALLY_CLOSED', 'MANUAL_MILESTONE', $note !== '' ? $note : 'Procedural closure completed');
        $this->serviceOrders->markProceduralClosed((int) $order['id'], $userId);
        $this->serviceOrders->updateClosure((int) $order['id'], 'PROCEDURAL', 'COMPLETED', null, $note, $userId);
    }

    private function createEVerificationReminders(array $order, int $userId, DateTimeImmutable $baseDate): void
    {
        $scheduleDays = [10, 15, 20, 25, 26, 30];

        foreach ($scheduleDays as $dayNo) {
            if ($this->serviceOrders->reminderExists((int) $order['id'], 'E_VERIFICATION', $dayNo)) {
                continue;
            }

            $dueAt = $baseDate->add(new DateInterval('P' . $dayNo . 'D'))->setTime(9, 0);
            $reminderId = $this->serviceOrders->insertReminder(
                (int) $order['id'],
                'E_VERIFICATION',
                $dayNo,
                $dueAt->format('Y-m-d H:i:s'),
                $order['assigned_crm_id'] !== null ? (int) $order['assigned_crm_id'] : null,
                $userId,
                'ITR e-verification reminder for day ' . $dayNo
            );
            $this->serviceOrders->insertReminderLog($reminderId, 'CREATED', $userId, 'Auto-created for ITR e-verification follow-up');
        }
    }

    private function transitionToStage(array $order, int $userId, string $toStageCode, string $transitionType, string $note): void
    {
        $stageMap = $this->stageMap((int) $order['workflow_definition_id']);
        $fromStageCode = (string) $order['current_stage_code'];
        $toStageName = $stageMap[$toStageCode] ?? str_replace('_', ' ', $toStageCode);

        $this->serviceOrders->closeCurrentStageHistory((int) $order['id']);
        $this->serviceOrders->updateCurrentStage((int) $order['id'], $toStageCode);
        $this->serviceOrders->appendStageHistory((int) $order['id'], $userId, $toStageCode, $toStageName, $note);
        $this->serviceOrders->appendTransitionLog((int) $order['id'], $fromStageCode, $toStageCode, $transitionType, $note, $userId);
        $this->serviceOrders->recordActivity(
            $userId,
            (int) $order['id'],
            $toStageCode === 'PROCEDURALLY_CLOSED' ? 'PROCEDURAL_CLOSE' : 'WORKFLOW_TRANSITION',
            'Workflow moved from ' . $fromStageCode . ' to ' . $toStageCode,
            'WORKFLOW'
        );
        Logger::info('workflow.transition', [
            'service_order_id' => (int) $order['id'],
            'from_stage' => $fromStageCode,
            'to_stage' => $toStageCode,
            'transition_type' => $transitionType,
            'user_id' => $userId,
        ]);
    }

    private function nextStage(array $order): ?array
    {
        $sequence = $this->stageSequence((string) $order['service_type_code']);
        $currentStage = (string) $order['current_stage_code'];
        $currentIndex = array_search($currentStage, $sequence, true);

        if ($currentIndex === false || !isset($sequence[$currentIndex + 1])) {
            return null;
        }

        $stageCode = $sequence[$currentIndex + 1];
        $stageMap = $this->stageMap((int) $order['workflow_definition_id']);

        return [
            'code' => $stageCode,
            'name' => $stageMap[$stageCode] ?? str_replace('_', ' ', $stageCode),
        ];
    }

    private function stageSequence(string $serviceTypeCode): array
    {
        return match ($serviceTypeCode) {
            'ITR' => [
                'DOCUMENT_PENDING',
                'PREPARATION',
                'REVIEW',
                'PAYMENT_PENDING',
                'PAID',
                'FILING_PENDING',
                'FILING_DONE',
                'ACKNOWLEDGEMENT_CAPTURED',
                'E_VERIFICATION_PENDING',
                'E_VERIFICATION_DONE',
                'PROCEDURALLY_CLOSED',
            ],
            'GST' => [
                'DOCUMENT_PENDING',
                'PREPARATION',
                'REVIEW',
                'PAYMENT_PENDING',
                'PAID',
                'FILING_PENDING',
                'FILING_DONE',
                'ACKNOWLEDGEMENT_CAPTURED',
                'PROCEDURALLY_CLOSED',
            ],
            default => [
                'DOCUMENT_PENDING',
                'PREPARATION',
                'REVIEW',
                'FILING_PENDING',
                'FILING_DONE',
                'ACKNOWLEDGEMENT_CAPTURED',
                'PROCEDURALLY_CLOSED',
            ],
        };
    }

    private function stageMap(int $workflowDefinitionId): array
    {
        $map = [];
        foreach ($this->workflows->stageDefinitions($workflowDefinitionId) as $stage) {
            $map[(string) $stage['stage_code']] = (string) $stage['stage_name'];
        }

        return $map;
    }

    private function requireLockedOrder(int $serviceOrderId): array
    {
        $order = $this->serviceOrders->lockForUpdate($serviceOrderId);
        if ($order === null) {
            throw new RuntimeException('Service order not found.');
        }

        if ((int) $order['is_locked'] === 1) {
            throw new RuntimeException('This service order is locked and cannot be modified.');
        }

        return $order;
    }

    private function runInTransaction(callable $callback): void
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $callback();
            $connection->commit();
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }
}
