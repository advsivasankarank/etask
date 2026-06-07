<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ConsultantRepository
{
    public function serviceOrdersForConsultants(): array
    {
        $statement = Database::connection()->query(
            "SELECT so.id,
                    so.so_no,
                    so.title,
                    c.legal_name AS client_name,
                    st.name AS service_type_name,
                    comp.display_name AS company_name
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             INNER JOIN companies comp ON comp.id = so.company_id
             ORDER BY so.id DESC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function serviceOrderContext(int $serviceOrderId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.id,
                    so.so_no,
                    so.title,
                    so.final_closed_at,
                    c.legal_name AS client_name,
                    st.name AS service_type_name,
                    comp.display_name AS company_name,
                    ssf.is_consultant_payment_pending
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             INNER JOIN companies comp ON comp.id = so.company_id
             LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
             WHERE so.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $serviceOrderId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function consultants(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT u.id, u.full_name, u.email
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code = 'CONSULTANT'
               AND u.is_active = 1
             ORDER BY u.full_name ASC"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function internalReviewers(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT DISTINCT u.id, u.full_name
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code IN ('ADMIN', 'CRM', 'BACKEND_STAFF', 'SUPER_ADMIN')
               AND u.is_active = 1
             ORDER BY u.full_name ASC"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignments(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT ca.id,
                    ca.consultant_user_id,
                    ca.internal_reviewer_id,
                    ca.status,
                    ca.remarks,
                    ca.assigned_at,
                    consultant.full_name AS consultant_name,
                    reviewer.full_name AS reviewer_name
             FROM consultant_assignments ca
             INNER JOIN users consultant ON consultant.id = ca.consultant_user_id
             LEFT JOIN users reviewer ON reviewer.id = ca.internal_reviewer_id
             WHERE ca.service_order_id = :service_order_id
             ORDER BY ca.id DESC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignmentById(int $assignmentId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT ca.*, consultant.full_name AS consultant_name
             FROM consultant_assignments ca
             INNER JOIN users consultant ON consultant.id = ca.consultant_user_id
             WHERE ca.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $assignmentId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function createAssignment(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_assignments (
                service_order_id, consultant_user_id, assigned_by, assigned_at, internal_reviewer_id, status, remarks
             ) VALUES (
                :service_order_id, :consultant_user_id, :assigned_by, NOW(), :internal_reviewer_id, 'ASSIGNED', :remarks
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function updateAssignmentStatus(int $assignmentId, string $status, ?string $remarks = null): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_assignments
             SET status = :status,
                 remarks = COALESCE(:remarks, remarks)
             WHERE id = :id"
        );
        $statement->execute([
            'status' => $status,
            'remarks' => $remarks,
            'id' => $assignmentId,
        ]);
    }

    public function createDeliverable(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_deliverables (
                consultant_assignment_id, document_id, reviewed_by, reviewed_at, review_status, review_notes, created_at
             ) VALUES (
                :consultant_assignment_id, :document_id, NULL, NULL, 'PENDING', NULL, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function updateDeliverableReview(int $deliverableId, int $reviewedBy, string $status, ?string $notes): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_deliverables
             SET reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 review_status = :review_status,
                 review_notes = :review_notes
             WHERE id = :id"
        );
        $statement->execute([
            'reviewed_by' => $reviewedBy,
            'review_status' => $status,
            'review_notes' => $notes,
            'id' => $deliverableId,
        ]);
    }

    public function deliverables(int $assignmentId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT cd.id,
                    d.id AS document_id,
                    cd.review_status,
                    cd.review_notes,
                    cd.created_at,
                    d.document_name,
                    d.latest_file_name,
                    reviewer.full_name AS reviewed_by_name
             FROM consultant_deliverables cd
             INNER JOIN documents d ON d.id = cd.document_id
             LEFT JOIN users reviewer ON reviewer.id = cd.reviewed_by
             WHERE cd.consultant_assignment_id = :consultant_assignment_id
             ORDER BY cd.id DESC"
        );
        $statement->execute(['consultant_assignment_id' => $assignmentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBill(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_bills (
                consultant_assignment_id, bill_no, bill_date, amount, tax_amount, total_amount, document_id,
                review_status, reviewed_by, reviewed_at, review_notes, created_at
             ) VALUES (
                :consultant_assignment_id, :bill_no, :bill_date, :amount, :tax_amount, :total_amount, :document_id,
                'PENDING', NULL, NULL, NULL, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function bills(int $assignmentId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT cb.id,
                    d.id AS document_id,
                    cb.bill_no,
                    cb.bill_date,
                    cb.amount,
                    cb.tax_amount,
                    cb.total_amount,
                    cb.review_status,
                    cb.review_notes,
                    reviewer.full_name AS reviewed_by_name,
                    d.latest_file_name
             FROM consultant_bills cb
             LEFT JOIN users reviewer ON reviewer.id = cb.reviewed_by
             LEFT JOIN documents d ON d.id = cb.document_id
             WHERE cb.consultant_assignment_id = :consultant_assignment_id
             ORDER BY cb.id DESC"
        );
        $statement->execute(['consultant_assignment_id' => $assignmentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function billById(int $billId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT cb.*,
                    ca.service_order_id
             FROM consultant_bills cb
             INNER JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
             WHERE cb.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $billId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function updateBillReview(int $billId, int $reviewedBy, string $status, ?string $notes): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_bills
             SET reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 review_status = :review_status,
                 review_notes = :review_notes
             WHERE id = :id"
        );
        $statement->execute([
            'reviewed_by' => $reviewedBy,
            'review_status' => $status,
            'review_notes' => $notes,
            'id' => $billId,
        ]);
    }

    public function createPayment(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_payments (
                consultant_bill_id, payment_date, amount, payment_mode, reference_no, paid_by, proof_document_id, remarks, created_at
             ) VALUES (
                :consultant_bill_id, :payment_date, :amount, :payment_mode, :reference_no, :paid_by, :proof_document_id, :remarks, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function payments(int $assignmentId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT cp.id,
                    cp.payment_date,
                    cp.amount,
                    cp.payment_mode,
                    cp.reference_no,
                    cp.remarks,
                    cb.bill_no
             FROM consultant_payments cp
             INNER JOIN consultant_bills cb ON cb.id = cp.consultant_bill_id
             WHERE cb.consultant_assignment_id = :consultant_assignment_id
             ORDER BY cp.id DESC"
        );
        $statement->execute(['consultant_assignment_id' => $assignmentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approvedOutstandingByServiceOrder(int $serviceOrderId): float
    {
        $statement = Database::connection()->prepare(
            "SELECT COALESCE(SUM(cb.total_amount), 0) - COALESCE(SUM(cp.amount), 0) AS outstanding_amount
             FROM consultant_assignments ca
             LEFT JOIN consultant_bills cb ON cb.consultant_assignment_id = ca.id AND cb.review_status = 'APPROVED'
             LEFT JOIN consultant_payments cp ON cp.consultant_bill_id = cb.id
             WHERE ca.service_order_id = :service_order_id"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return (float) ($statement->fetchColumn() ?: 0.0);
    }
}
