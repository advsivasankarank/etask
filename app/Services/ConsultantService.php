<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\ConsultantRepository;
use App\Repositories\ServiceOrderRepository;
use RuntimeException;
use Throwable;

final class ConsultantService
{
    public function __construct(
        private readonly ConsultantRepository $consultants = new ConsultantRepository(),
        private readonly ServiceOrderRepository $serviceOrders = new ServiceOrderRepository(),
        private readonly DocumentUploadService $documents = new DocumentUploadService()
    ) {
    }

    public function dashboard(int $serviceOrderId): array
    {
        $context = $this->consultants->serviceOrderContext($serviceOrderId);
        if ($context === null) {
            throw new RuntimeException('Service order not found.');
        }

        $assignments = $this->consultants->assignments($serviceOrderId);
        $assignmentDetails = [];
        foreach ($assignments as $assignment) {
            $assignmentDetails[] = [
                'assignment' => $assignment,
                'deliverables' => $this->consultants->deliverables((int) $assignment['id']),
                'bills' => $this->consultants->bills((int) $assignment['id']),
                'payments' => $this->consultants->payments((int) $assignment['id']),
            ];
        }

        return [
            'order' => $context,
            'consultants' => $this->consultants->consultants(),
            'reviewers' => $this->consultants->internalReviewers(),
            'assignments' => $assignmentDetails,
            'outstanding_amount' => $this->consultants->approvedOutstandingByServiceOrder($serviceOrderId),
        ];
    }

    public function assign(array $input, int $userId): int
    {
        $serviceOrderId = (int) ($input['service_order_id'] ?? 0);
        $consultantUserId = (int) ($input['consultant_user_id'] ?? 0);

        if ($serviceOrderId <= 0 || $consultantUserId <= 0) {
            throw new RuntimeException('Service order and consultant are required.');
        }

        return $this->runInTransaction(function () use ($serviceOrderId, $consultantUserId, $input, $userId): int {
            $assignmentId = $this->consultants->createAssignment([
                'service_order_id' => $serviceOrderId,
                'consultant_user_id' => $consultantUserId,
                'assigned_by' => $userId,
                'internal_reviewer_id' => (int) ($input['internal_reviewer_id'] ?? 0) ?: null,
                'remarks' => trim((string) ($input['remarks'] ?? '')) ?: null,
            ]);

            $this->syncConsultantPendingFlag($serviceOrderId);
            $this->serviceOrders->recordActivity(
                $userId,
                $serviceOrderId,
                'CONSULTANT_ASSIGN',
                'Consultant assigned to service order.',
                'CONSULTANTS'
            );

            return $assignmentId;
        });
    }

    public function uploadDeliverable(int $assignmentId, array $file, int $userId): int
    {
        if (!isset($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('A consultant deliverable file is required.');
        }

        return $this->runInTransaction(function () use ($assignmentId, $file, $userId): int {
            $assignment = $this->consultants->assignmentById($assignmentId);
            if ($assignment === null) {
                throw new RuntimeException('Consultant assignment not found.');
            }

            $clientId = $this->serviceOrders->clientIdForServiceOrder((int) $assignment['service_order_id']);
            if ($clientId === null) {
                throw new RuntimeException('Unable to resolve client for consultant deliverable.');
            }

            $documentIds = $this->documents->uploadLinkedDocuments(
                $clientId,
                'CONSULTANT',
                $assignmentId,
                'CONSULTANT_DELIVERABLE',
                $file,
                $userId,
                'consultants'
            );
            if ($documentIds === []) {
                throw new RuntimeException('Unable to store consultant deliverable.');
            }

            $deliverableId = $this->consultants->createDeliverable([
                'consultant_assignment_id' => $assignmentId,
                'document_id' => $documentIds[0],
            ]);
            $this->consultants->updateAssignmentStatus($assignmentId, 'WORK_SUBMITTED');
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $assignment['service_order_id'],
                'CONSULTANT_DELIVERABLE_UPLOAD',
                'Consultant deliverable uploaded.',
                'CONSULTANTS'
            );

            return $deliverableId;
        });
    }

    public function reviewDeliverable(int $deliverableId, int $assignmentId, int $userId, string $status, ?string $notes): void
    {
        $allowed = ['APPROVED', 'REJECTED'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Invalid deliverable review status.');
        }

        $assignment = $this->consultants->assignmentById($assignmentId);
        if ($assignment === null) {
            throw new RuntimeException('Consultant assignment not found.');
        }

        $this->consultants->updateDeliverableReview($deliverableId, $userId, $status, $notes);
        $this->consultants->updateAssignmentStatus($assignmentId, $status === 'APPROVED' ? 'APPROVED' : 'REJECTED', $notes);
        $this->serviceOrders->recordActivity(
            $userId,
            (int) $assignment['service_order_id'],
            'CONSULTANT_DELIVERABLE_REVIEW',
            'Consultant deliverable reviewed: ' . $status,
            'CONSULTANTS'
        );
    }

    public function createBill(array $input, array $file, int $userId): int
    {
        $assignmentId = (int) ($input['consultant_assignment_id'] ?? 0);
        $billNo = trim((string) ($input['bill_no'] ?? ''));
        $totalAmount = round((float) ($input['total_amount'] ?? 0), 2);

        if ($assignmentId <= 0 || $billNo === '' || $totalAmount <= 0) {
            throw new RuntimeException('Assignment, bill number, and total amount are required.');
        }

        return $this->runInTransaction(function () use ($assignmentId, $billNo, $totalAmount, $input, $file, $userId): int {
            $assignment = $this->consultants->assignmentById($assignmentId);
            if ($assignment === null) {
                throw new RuntimeException('Consultant assignment not found.');
            }

            $clientId = $this->serviceOrders->clientIdForServiceOrder((int) $assignment['service_order_id']);
            if ($clientId === null) {
                throw new RuntimeException('Unable to resolve client for consultant bill.');
            }

            $documentId = null;
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $documentIds = $this->documents->uploadLinkedDocuments(
                    $clientId,
                    'CONSULTANT',
                    $assignmentId,
                    'CONSULTANT_BILL',
                    $file,
                    $userId,
                    'consultants'
                );
                $documentId = $documentIds[0] ?? null;
            }

            $billId = $this->consultants->createBill([
                'consultant_assignment_id' => $assignmentId,
                'bill_no' => $billNo,
                'bill_date' => (string) ($input['bill_date'] ?? date('Y-m-d')),
                'amount' => round((float) ($input['amount'] ?? $totalAmount), 2),
                'tax_amount' => round((float) ($input['tax_amount'] ?? 0), 2),
                'total_amount' => $totalAmount,
                'document_id' => $documentId,
            ]);

            $this->syncConsultantPendingFlag((int) $assignment['service_order_id']);
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $assignment['service_order_id'],
                'CONSULTANT_BILL_CREATE',
                'Consultant bill submitted: ' . $billNo,
                'CONSULTANTS'
            );

            return $billId;
        });
    }

    public function reviewBill(int $billId, int $userId, string $status, ?string $notes): void
    {
        $allowed = ['APPROVED', 'REJECTED'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Invalid bill review status.');
        }

        $bill = $this->consultants->billById($billId);
        if ($bill === null) {
            throw new RuntimeException('Consultant bill not found.');
        }

        $this->consultants->updateBillReview($billId, $userId, $status, $notes);
        $this->syncConsultantPendingFlag((int) $bill['service_order_id']);
        $this->serviceOrders->recordActivity(
            $userId,
            (int) $bill['service_order_id'],
            'CONSULTANT_BILL_REVIEW',
            'Consultant bill reviewed: ' . $status,
            'CONSULTANTS'
        );
    }

    public function recordPayment(array $input, int $userId): int
    {
        $billId = (int) ($input['consultant_bill_id'] ?? 0);
        $amount = round((float) ($input['amount'] ?? 0), 2);

        if ($billId <= 0 || $amount <= 0) {
            throw new RuntimeException('Consultant bill and payment amount are required.');
        }

        return $this->runInTransaction(function () use ($billId, $amount, $input, $userId): int {
            $bill = $this->consultants->billById($billId);
            if ($bill === null) {
                throw new RuntimeException('Consultant bill not found.');
            }

            if ((string) $bill['review_status'] !== 'APPROVED') {
                throw new RuntimeException('Only approved consultant bills can be paid.');
            }

            $paymentId = $this->consultants->createPayment([
                'consultant_bill_id' => $billId,
                'payment_date' => (string) ($input['payment_date'] ?? date('Y-m-d')),
                'amount' => $amount,
                'payment_mode' => (string) ($input['payment_mode'] ?? 'BANK_TRANSFER'),
                'reference_no' => trim((string) ($input['reference_no'] ?? '')) ?: null,
                'paid_by' => $userId,
                'proof_document_id' => null,
                'remarks' => trim((string) ($input['remarks'] ?? '')) ?: null,
            ]);

            $this->syncConsultantPendingFlag((int) $bill['service_order_id']);
            $this->serviceOrders->recordActivity(
                $userId,
                (int) $bill['service_order_id'],
                'CONSULTANT_PAYMENT_RECORD',
                'Consultant payment recorded.',
                'CONSULTANTS'
            );

            return $paymentId;
        });
    }

    private function syncConsultantPendingFlag(int $serviceOrderId): void
    {
        $outstanding = $this->consultants->approvedOutstandingByServiceOrder($serviceOrderId);
        $assignments = $this->consultants->assignments($serviceOrderId);

        $hasUnapprovedBill = false;
        foreach ($assignments as $assignment) {
            foreach ($this->consultants->bills((int) $assignment['id']) as $bill) {
                if ($bill['review_status'] === 'PENDING') {
                    $hasUnapprovedBill = true;
                    break 2;
                }
            }
        }

        $pending = $outstanding > 0 || $hasUnapprovedBill;
        $this->serviceOrders->updateStatusFlags($serviceOrderId, [
            'is_consultant_payment_pending' => $pending ? 1 : 0,
        ]);
    }

    private function runInTransaction(callable $callback): mixed
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
