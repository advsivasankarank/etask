<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PsoRepository
{
    public function listForPortal(?int $clientId, bool $internalView): array
    {
        $sql = "SELECT pso.id,
                       pso.pso_no,
                       pso.title,
                       pso.current_status,
                       pso.submitted_at,
                       c.legal_name AS client_name,
                       st.name AS service_type_name,
                       comp.display_name AS company_name,
                       so.so_no AS converted_so_no
                FROM pre_service_orders pso
                INNER JOIN clients c ON c.id = pso.client_id
                INNER JOIN service_types st ON st.id = pso.service_type_id
                INNER JOIN companies comp ON comp.id = pso.company_id
                LEFT JOIN service_orders so ON so.id = pso.converted_so_id";

        $params = [];
        if (!$internalView) {
            $sql .= " WHERE pso.client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        $sql .= " ORDER BY pso.id DESC LIMIT 100";

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO pre_service_orders (
                pso_no, client_id, company_id, financial_year_id, service_type_id, requested_for_period,
                title, description, requested_by_contact_id, current_status, submitted_at, created_at, updated_at
            ) VALUES (
                :pso_no, :client_id, :company_id, :financial_year_id, :service_type_id, :requested_for_period,
                :title, :description, :requested_by_contact_id, 'SUBMITTED', NOW(), NOW(), NOW()
            )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function attachDocument(int $psoId, int $documentId): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO pso_documents (pso_id, document_id, created_at)
             VALUES (:pso_id, :document_id, NOW())"
        );
        $statement->execute([
            'pso_id' => $psoId,
            'document_id' => $documentId,
        ]);
    }

    public function findById(int $psoId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT pso.*,
                    c.legal_name AS client_name,
                    c.client_code,
                    c.pan,
                    c.mobile,
                    st.name AS service_type_name,
                    st.code AS service_type_code,
                    comp.display_name AS company_name,
                    requester.contact_name AS requested_by_name,
                    so.so_no AS converted_so_no
             FROM pre_service_orders pso
             INNER JOIN clients c ON c.id = pso.client_id
             INNER JOIN service_types st ON st.id = pso.service_type_id
             INNER JOIN companies comp ON comp.id = pso.company_id
             INNER JOIN client_contacts requester ON requester.id = pso.requested_by_contact_id
             LEFT JOIN service_orders so ON so.id = pso.converted_so_id
             WHERE pso.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $psoId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function documents(int $psoId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT d.id, d.document_name, d.latest_file_name, d.uploaded_at
             FROM pso_documents pd
             INNER JOIN documents d ON d.id = pd.document_id
             WHERE pd.pso_id = :pso_id
             ORDER BY d.id ASC"
        );
        $statement->execute(['pso_id' => $psoId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reviews(int $psoId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT pr.review_action, pr.remarks, pr.acted_at, u.full_name AS acted_by_name
             FROM pso_reviews pr
             INNER JOIN users u ON u.id = pr.acted_by
             WHERE pr.pso_id = :pso_id
             ORDER BY pr.id ASC"
        );
        $statement->execute(['pso_id' => $psoId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addReview(int $psoId, string $action, ?string $remarks, int $actedBy): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO pso_reviews (pso_id, review_action, remarks, acted_by, acted_at)
             VALUES (:pso_id, :review_action, :remarks, :acted_by, NOW())"
        );
        $statement->execute([
            'pso_id' => $psoId,
            'review_action' => $action,
            'remarks' => $remarks,
            'acted_by' => $actedBy,
        ]);
    }

    public function lockForUpdate(int $psoId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM pre_service_orders
             WHERE id = :id
             LIMIT 1
             FOR UPDATE"
        );
        $statement->execute(['id' => $psoId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function markRecommendedApproval(int $psoId, int $userId, ?string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE pre_service_orders
             SET current_status = 'UNDER_REVIEW',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'reviewed_by' => $userId,
            'id' => $psoId,
        ]);

        $this->addReview($psoId, 'RECOMMENDED_APPROVAL', $remarks, $userId);
    }

    public function approveAndConvert(int $psoId, int $userId, int $serviceOrderId, ?string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE pre_service_orders
             SET current_status = 'CONVERTED_TO_SO',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 approved_at = NOW(),
                 converted_so_id = :converted_so_id,
                 notification_sent_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'reviewed_by' => $userId,
            'converted_so_id' => $serviceOrderId,
            'id' => $psoId,
        ]);

        $this->addReview($psoId, 'APPROVED', $remarks, $userId);
    }

    public function reject(int $psoId, int $userId, string $reason): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE pre_service_orders
             SET current_status = 'REJECTED',
                 admin_rejected_by = :admin_rejected_by,
                 admin_rejected_at = NOW(),
                 rejection_reason = :rejection_reason,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'admin_rejected_by' => $userId,
            'rejection_reason' => $reason,
            'id' => $psoId,
        ]);

        $this->addReview($psoId, 'REJECTED', $reason, $userId);
    }

    public function insertNotification(?int $clientContactId, string $subject, string $message, int $linkedId): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO notifications (
                user_id, client_contact_id, channel, subject, message, linked_module, linked_id, delivery_status, created_at
             ) VALUES (
                NULL, :client_contact_id, 'IN_APP', :subject, :message, 'PSO', :linked_id, 'PENDING', NOW()
             )"
        );
        $statement->execute([
            'client_contact_id' => $clientContactId,
            'subject' => $subject,
            'message' => $message,
            'linked_id' => $linkedId,
        ]);
    }
}
