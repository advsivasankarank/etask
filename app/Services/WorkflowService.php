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
            'stages' => $this->stageDefinitions($order),
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

            if (in_array($currentStage, ['PAYMENT_PENDING', 'TAX_PAYMENT_PENDING'], true)) {
                throw new RuntimeException('Use tax payment entry to move beyond Tax Payment Pending.');
            }

            if (in_array($currentStage, ['FILING_DONE', 'ITR_FILING_DONE', 'FORM_3CB_FILED'], true)) {
                throw new RuntimeException('Acknowledgement must be captured before the workflow can move forward.');
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

            if (in_array($nextStage['code'], ['FILING_DONE', 'ITR_FILING_DONE'], true)) {
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
            $currentStage = (string) $order['current_stage_code'];

            if (!in_array($currentStage, ['PAYMENT_PENDING', 'TAX_PAYMENT_PENDING'], true)) {
                throw new RuntimeException('Tax payment can only be recorded while the workflow is in Tax Payment Pending.');
            }

            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'payment_reference_no' => $referenceNo,
                'payment_recorded_at' => date('Y-m-d H:i:s'),
            ]);

            $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                'is_payment_pending' => 0,
                'is_paid' => 1,
            ]);

            $nextStage = $this->nextStage($order);
            if ($nextStage === null) {
                throw new RuntimeException('Unable to determine the next milestone after tax payment.');
            }

            $this->transitionToStage($order, $userId, $nextStage['code'], 'AUTO_PAYMENT', 'Tax payment captured: ' . $referenceNo);
        });
    }

    public function captureAcknowledgement(int $serviceOrderId, int $userId, string $referenceNo): void
    {
        $referenceNo = trim($referenceNo);
        if ($referenceNo === '') {
            throw new RuntimeException('Acknowledgement reference is required.');
        }

        $this->runInTransaction(function () use ($serviceOrderId, $userId, $referenceNo): void {
            $order = $this->requireLockedOrder($serviceOrderId);
            $currentStage = (string) $order['current_stage_code'];
            $now = date('Y-m-d H:i:s');

            if ($currentStage === 'FORM_3CB_FILED') {
                $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                    'form_3cb_acknowledgement_no' => $referenceNo,
                    'form_3cb_acknowledgement_captured_at' => $now,
                ]);

                $this->transitionToStage($order, $userId, 'FORM_3CB_ACKNOWLEDGEMENT_CAPTURED', 'AUTO_ACK_UPLOAD', 'Form 3CB acknowledgement captured: ' . $referenceNo);

                $updatedOrder = $this->requireLockedOrder($serviceOrderId);
                $nextStage = $this->nextStage($updatedOrder);
                if ($nextStage !== null && $nextStage['code'] !== 'PROCEDURALLY_CLOSED') {
                    $this->transitionToStage($updatedOrder, $userId, $nextStage['code'], 'SYSTEM', 'Form 3CB acknowledgement captured; moved to next milestone');
                }
                return;
            }

            if (!in_array($currentStage, ['FILING_DONE', 'ITR_FILING_DONE'], true)) {
                throw new RuntimeException('Acknowledgement can only be captured after ITR Filing Done.');
            }

            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'filing_reference_no' => $referenceNo,
                'acknowledgement_no' => $referenceNo,
                'acknowledgement_captured_at' => $now,
            ]);

            $this->serviceOrders->updateStatusFlags((int) $order['id'], [
                'is_acknowledgement_captured' => 1,
            ]);

            $ackStage = $this->nextStage($order);
            if ($ackStage === null) {
                throw new RuntimeException('Unable to determine acknowledgement milestone.');
            }

            $this->transitionToStage($order, $userId, $ackStage['code'], 'AUTO_ACK_UPLOAD', 'Acknowledgement captured: ' . $referenceNo);

            if ((string) $order['service_type_code'] === 'ITR') {
                $updatedOrder = $this->requireLockedOrder($serviceOrderId);
                $nextStage = $this->nextStage($updatedOrder);
                if ($nextStage !== null) {
                    $this->transitionToStage($updatedOrder, $userId, $nextStage['code'], 'SYSTEM', 'ITR acknowledgement captured; e-verification window started');
                    if ($nextStage['code'] === 'E_VERIFICATION_PENDING') {
                        $this->createEVerificationReminders($updatedOrder, $userId, new DateTimeImmutable($now));
                    }
                }
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
            $this->serviceOrders->recordActivity($userId, (int) $order['id'], 'ACCOUNTING_CLOSE', 'Accounting closure completed', 'WORKFLOW');
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
            $this->serviceOrders->recordActivity($userId, (int) $order['id'], 'FINAL_CLOSE', 'Final closure completed and order locked', 'WORKFLOW');
        });
    }

    public function reopenMilestone(int $serviceOrderId, string $stageCode, int $userId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Reopen reason is required.');
        }

        $this->runInTransaction(function () use ($serviceOrderId, $stageCode, $userId, $reason): void {
            $order = $this->serviceOrders->lockForUpdate($serviceOrderId);
            if ($order === null) {
                throw new RuntimeException('Service order not found.');
            }

            $sequence = $this->stageSequence($order);
            $currentStage = (string) $order['current_stage_code'];
            $requestedStage = strtoupper(trim($stageCode));

            if (!in_array($requestedStage, $sequence, true)) {
                throw new RuntimeException('Selected milestone cannot be reopened for this service order.');
            }

            $currentIndex = array_search($currentStage, $sequence, true);
            $requestedIndex = array_search($requestedStage, $sequence, true);

            if ($currentIndex === false || $requestedIndex === false) {
                throw new RuntimeException('Unable to resolve milestone sequence.');
            }

            if ($requestedIndex >= $currentIndex && $currentStage !== 'PROCEDURALLY_CLOSED') {
                throw new RuntimeException('Only a previously completed milestone can be reopened.');
            }

            $this->serviceOrders->closeCurrentStageHistory((int) $order['id']);
            $this->serviceOrders->updateWorkflowMetadata((int) $order['id'], [
                'is_locked' => 0,
                'lock_reason' => null,
                'final_closed_at' => null,
                'final_closed_by' => null,
                'accounting_closed_at' => null,
                'procedural_closed_at' => null,
            ]);

            $this->serviceOrders->updateCurrentStage((int) $order['id'], $requestedStage);
            $this->serviceOrders->appendStageHistory(
                (int) $order['id'],
                $userId,
                $requestedStage,
                $this->stageMap($order)[$requestedStage] ?? str_replace('_', ' ', $requestedStage),
                'Milestone reopened: ' . $reason
            );
            $this->serviceOrders->appendTransitionLog(
                (int) $order['id'],
                $currentStage,
                $requestedStage,
                'REOPEN',
                $reason,
                $userId
            );
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $order['id'],
                'WORKFLOW_REOPEN',
                'Milestone reopened from ' . $currentStage . ' to ' . $requestedStage . '. Reason: ' . $reason,
                'WORKFLOW'
            );

            $this->syncStateAfterReopen((int) $order['id'], $requestedStage, $requestedIndex, $sequence);
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
        $currentStage = (string) $order['current_stage_code'];
        $serviceType = (string) $order['service_type_code'];
        $advanceBlocked = array_merge(
            ['E_VERIFICATION_PENDING', 'PROCEDURALLY_CLOSED'],
            $this->paymentPendingStages(),
            $this->ackCaptureRequiredStages(),
            $this->ackNowStages($order)
        );

        $actions = [
            'can_advance' => !in_array($currentStage, $advanceBlocked, true),
            'can_record_payment' => in_array($currentStage, $this->paymentPendingStages(), true),
            'can_capture_ack' => in_array($currentStage, $this->ackCaptureRequiredStages(), true),
            'can_mark_everification_done' => $serviceType === 'ITR' && $currentStage === 'E_VERIFICATION_PENDING',
            'can_procedural_close' => false,
            'can_accounting_close' => (int) ($order['is_client_paid'] ?? 0) === 1,
            'can_final_close' => false,
        ];

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
            throw new RuntimeException('ITR acknowledgement is mandatory before procedural closure.');
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
        $stageMap = $this->stageMap($order);
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
        $sequence = $this->stageSequence($order);
        $currentStage = (string) $order['current_stage_code'];
        $currentIndex = array_search($currentStage, $sequence, true);

        if ($currentIndex === false || !isset($sequence[$currentIndex + 1])) {
            return null;
        }

        $stageCode = $sequence[$currentIndex + 1];
        $stageMap = $this->stageMap($order);

        return [
            'code' => $stageCode,
            'name' => $stageMap[$stageCode] ?? str_replace('_', ' ', $stageCode),
        ];
    }

    private function stageDefinitions(array $order): array
    {
        $definitions = [];
        $sortOrder = 1;

        foreach ($this->stageSequence($order) as $stageCode) {
            $definitions[] = [
                'stage_code' => $stageCode,
                'stage_name' => $this->stageMap($order)[$stageCode] ?? str_replace('_', ' ', $stageCode),
                'sort_order' => $sortOrder++,
            ];
        }

        return $definitions;
    }

    private function stageSequence(array $order): array
    {
        $serviceTypeCode = (string) ($order['service_type_code'] ?? '');

        if ($serviceTypeCode === 'ITR') {
            $caseNature = (string) ($order['itr_case_nature'] ?? '');
            $taxAuditApplicable = (int) ($order['itr_tax_audit_applicable'] ?? 0) === 1;

            if ($caseNature === 'BUSINESS' && $taxAuditApplicable) {
                return [
                    'DOCUMENT_PENDING',
                    'BALANCE_SHEET_PREPARATION',
                    'BALANCE_SHEET_CHECKING',
                    'FORM_3CB_PREPARED',
                    'FORM_3CB_CHECKED',
                    'FORM_3CB_FILED',
                    'FORM_3CB_ACKNOWLEDGEMENT_CAPTURED',
                    'IT_COMPUTATION_PREPARATION',
                    'REVIEW',
                    'TAX_PAYMENT_PENDING',
                    'TAX_PAID',
                    'ITR_FILING_PENDING',
                    'ITR_FILING_DONE',
                    'ITR_ACKNOWLEDGEMENT_CAPTURED',
                    'E_VERIFICATION_PENDING',
                    'E_VERIFICATION_DONE',
                    'PROCEDURALLY_CLOSED',
                ];
            }

            if ($caseNature === 'BUSINESS') {
                return [
                    'DOCUMENT_PENDING',
                    'BALANCE_SHEET_PREPARATION',
                    'BALANCE_SHEET_CHECKING',
                    'IT_COMPUTATION_PREPARATION',
                    'REVIEW',
                    'TAX_PAYMENT_PENDING',
                    'TAX_PAID',
                    'ITR_FILING_PENDING',
                    'ITR_FILING_DONE',
                    'ITR_ACKNOWLEDGEMENT_CAPTURED',
                    'E_VERIFICATION_PENDING',
                    'E_VERIFICATION_DONE',
                    'PROCEDURALLY_CLOSED',
                ];
            }

            if ($caseNature === 'NON_BUSINESS') {
                return [
                    'DOCUMENT_PENDING',
                    'IT_COMPUTATION_PREPARATION',
                    'REVIEW',
                    'TAX_PAYMENT_PENDING',
                    'TAX_PAID',
                    'ITR_FILING_PENDING',
                    'ITR_FILING_DONE',
                    'ITR_ACKNOWLEDGEMENT_CAPTURED',
                    'E_VERIFICATION_PENDING',
                    'E_VERIFICATION_DONE',
                    'PROCEDURALLY_CLOSED',
                ];
            }

            return [
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
            ];
        }

        return match ($serviceTypeCode) {
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

    private function stageMap(array $order): array
    {
        $serviceTypeCode = (string) ($order['service_type_code'] ?? '');
        if ($serviceTypeCode === 'ITR') {
            return [
                'DOCUMENT_PENDING' => 'Document Pending',
                'PREPARATION' => 'IT Computation Preparation',
                'BALANCE_SHEET_PREPARATION' => 'Balance Sheet Preparation',
                'BALANCE_SHEET_CHECKING' => 'Balance Sheet Checking',
                'FORM_3CB_PREPARED' => 'Form 3CB Prepared',
                'FORM_3CB_CHECKED' => 'Form 3CB Checked',
                'FORM_3CB_FILED' => 'Form 3CB Filed',
                'FORM_3CB_ACKNOWLEDGEMENT_CAPTURED' => '3CB Acknowledgement Captured',
                'IT_COMPUTATION_PREPARATION' => 'IT Computation Preparation',
                'REVIEW' => 'Review',
                'PAYMENT_PENDING' => 'Tax Payment Pending',
                'PAID' => 'Tax Paid',
                'TAX_PAYMENT_PENDING' => 'Tax Payment Pending',
                'TAX_PAID' => 'Tax Paid',
                'FILING_PENDING' => 'ITR Filing Pending',
                'FILING_DONE' => 'ITR Filing Done',
                'ACKNOWLEDGEMENT_CAPTURED' => 'ITR Acknowledgement Captured',
                'ITR_FILING_PENDING' => 'ITR Filing Pending',
                'ITR_FILING_DONE' => 'ITR Filing Done',
                'ITR_ACKNOWLEDGEMENT_CAPTURED' => 'ITR Acknowledgement Captured',
                'E_VERIFICATION_PENDING' => 'E-Verification Pending',
                'E_VERIFICATION_DONE' => 'E-Verification Done',
                'PROCEDURALLY_CLOSED' => 'Procedurally Closed',
            ];
        }

        $map = [];
        foreach ($this->workflows->stageDefinitions((int) $order['workflow_definition_id']) as $stage) {
            $map[(string) $stage['stage_code']] = (string) $stage['stage_name'];
        }

        return $map;
    }

    private function paymentPendingStages(): array
    {
        return ['PAYMENT_PENDING', 'TAX_PAYMENT_PENDING'];
    }

    private function ackCaptureRequiredStages(): array
    {
        return ['FILING_DONE', 'ITR_FILING_DONE', 'FORM_3CB_FILED'];
    }

    private function ackNowStages(array $order): array
    {
        $serviceTypeCode = (string) ($order['service_type_code'] ?? '');

        if ($serviceTypeCode === 'ITR') {
            return ['ACKNOWLEDGEMENT_CAPTURED', 'ITR_ACKNOWLEDGEMENT_CAPTURED', 'FORM_3CB_ACKNOWLEDGEMENT_CAPTURED'];
        }

        return ['ACKNOWLEDGEMENT_CAPTURED'];
    }

    private function syncStateAfterReopen(int $serviceOrderId, string $stageCode, int $stageIndex, array $sequence): void
    {
        $statusUpdates = [
            'is_document_pending' => $stageCode === 'DOCUMENT_PENDING' ? 1 : 0,
            'is_payment_pending' => in_array($stageCode, $this->paymentPendingStages(), true) ? 1 : 0,
            'is_paid' => $this->isAtOrPast($sequence, $stageIndex, ['PAID', 'TAX_PAID']) ? 1 : 0,
            'is_filing_done' => $this->isAtOrPast($sequence, $stageIndex, ['FILING_DONE', 'ITR_FILING_DONE']) ? 1 : 0,
            'is_acknowledgement_captured' => $this->isAtOrPast($sequence, $stageIndex, ['ACKNOWLEDGEMENT_CAPTURED', 'ITR_ACKNOWLEDGEMENT_CAPTURED']) ? 1 : 0,
            'is_e_verification_done' => $this->isAtOrPast($sequence, $stageIndex, ['E_VERIFICATION_DONE']) ? 1 : 0,
            'is_overdue' => 0,
        ];
        $this->serviceOrders->updateStatusFlags($serviceOrderId, $statusUpdates);

        $this->serviceOrders->updateClosure($serviceOrderId, 'PROCEDURAL', 'PENDING', null, 'Milestone reopened', null);
        $this->serviceOrders->updateClosure($serviceOrderId, 'ACCOUNTING', 'PENDING', null, 'Milestone reopened', null);
        $this->serviceOrders->updateClosure($serviceOrderId, 'FINAL', 'PENDING', null, 'Milestone reopened', null);
    }

    private function isAtOrPast(array $sequence, int $currentIndex, array $candidateStages): bool
    {
        foreach ($candidateStages as $candidate) {
            $candidateIndex = array_search($candidate, $sequence, true);
            if ($candidateIndex !== false && $currentIndex >= $candidateIndex) {
                return true;
            }
        }

        return false;
    }

    private function indexOf(array $sequence, string $stageCode): int
    {
        $index = array_search($stageCode, $sequence, true);
        return $index === false ? PHP_INT_MAX : (int) $index;
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
