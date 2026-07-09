<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DocumentRepository
{
    public function portalCenterDocuments(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT d.id,
                    d.client_id,
                    d.linked_module,
                    d.linked_id,
                    d.document_category,
                    d.document_name,
                    d.current_version_no,
                    d.latest_file_name,
                    d.mime_type,
                    d.file_size,
                    d.uploaded_at,
                    d.uploaded_by,
                    u.full_name AS uploaded_by_name,
                    so.so_no,
                    so.title AS service_order_title,
                    st.name AS service_type_name,
                    pso.pso_no,
                    pso.title AS pso_title
             FROM documents d
             LEFT JOIN users u ON u.id = d.uploaded_by
             LEFT JOIN service_orders so
                ON d.linked_module = 'SO'
               AND so.id = d.linked_id
             LEFT JOIN service_types st ON st.id = so.service_type_id
             LEFT JOIN pre_service_orders pso
                ON d.linked_module = 'PSO'
               AND pso.id = d.linked_id
             WHERE d.client_id = :client_id
               AND d.is_active = 1
             ORDER BY d.uploaded_at DESC, d.id DESC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function forLinkedRecord(string $linkedModule, int $linkedId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT d.id,
                    d.client_id,
                    d.linked_module,
                    d.linked_id,
                    d.document_category,
                    d.document_name,
                    d.current_version_no,
                    d.latest_file_name,
                    d.mime_type,
                    d.file_size,
                    d.uploaded_at,
                    d.uploaded_by,
                    u.full_name AS uploaded_by_name
             FROM documents d
             LEFT JOIN users u ON u.id = d.uploaded_by
             WHERE d.linked_module = :linked_module
               AND d.linked_id = :linked_id
               AND d.is_active = 1
             ORDER BY d.uploaded_at DESC, d.id DESC"
        );
        $statement->execute([
            'linked_module' => strtoupper(trim($linkedModule)),
            'linked_id' => $linkedId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $documentId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT d.id,
                    d.client_id,
                    d.linked_module,
                    d.linked_id,
                    d.document_category,
                    d.document_name,
                    d.latest_file_name,
                    d.latest_file_path,
                    d.mime_type,
                    d.file_size,
                    d.checksum_sha256,
                    d.uploaded_by,
                    d.uploaded_at,
                    d.is_active,
                    c.legal_name AS client_name,
                    ca.consultant_user_id,
                    ca.service_order_id AS consultant_service_order_id,
                    so.client_id AS service_order_client_id,
                    pso.client_id AS pso_client_id
             FROM documents d
             INNER JOIN clients c ON c.id = d.client_id
             LEFT JOIN consultant_assignments ca
                ON d.linked_module = 'CONSULTANT'
               AND ca.id = d.linked_id
             LEFT JOIN service_orders so
                ON d.linked_module = 'SO'
               AND so.id = d.linked_id
             LEFT JOIN pre_service_orders pso
                ON d.linked_module = 'PSO'
               AND pso.id = d.linked_id
             WHERE d.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $documentId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function recordAccess(?int $userId, int $documentId, string $actionCode, string $description, ?string $ipAddress, ?string $userAgent): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO activity_logs (
                user_id, module_code, action_code, entity_type, entity_id, description, ip_address, user_agent, created_at
             ) VALUES (
                :user_id, 'DOCUMENTS', :action_code, 'documents', :entity_id, :description, :ip_address, :user_agent, NOW()
             )"
        );
        $statement->execute([
            'user_id' => $userId,
            'action_code' => $actionCode,
            'entity_id' => $documentId,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function accessReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id
            LEFT JOIN documents d ON d.id = al.entity_id AND al.entity_type = 'documents'
            LEFT JOIN clients c ON c.id = d.client_id
            WHERE al.module_code = 'DOCUMENTS'";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (
                d.document_name LIKE :search_document_name
                OR c.legal_name LIKE :search_client_name
                OR u.full_name LIKE :search_user_name
                OR al.ip_address LIKE :search_ip
            )";
            $params['search_document_name'] = $term;
            $params['search_client_name'] = $term;
            $params['search_user_name'] = $term;
            $params['search_ip'] = $term;
        }

        if (trim((string) ($filters['action_code'] ?? '')) !== '') {
            $whereSql .= " AND al.action_code = :action_code";
            $params['action_code'] = trim((string) $filters['action_code']);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        if ($dateFrom !== '') {
            $whereSql .= " AND DATE(al.created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo !== '') {
            $whereSql .= " AND DATE(al.created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT al.id,
                    al.action_code,
                    al.description,
                    al.ip_address,
                    al.created_at,
                    u.full_name AS user_name,
                    d.id AS document_id,
                    d.document_name,
                    d.document_category,
                    d.linked_module,
                    c.legal_name AS client_name
             {$baseSql}{$whereSql}
             ORDER BY al.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function versions(int $documentId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id,
                    version_no,
                    file_name,
                    file_path,
                    mime_type,
                    file_size,
                    checksum_sha256,
                    change_note,
                    uploaded_by,
                    uploaded_at
             FROM document_versions
             WHERE document_id = :document_id
             ORDER BY version_no DESC, id DESC"
        );
        $statement->execute(['document_id' => $documentId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function touchCurrentVersion(
        int $documentId,
        int $versionNo,
        string $fileName,
        string $filePath,
        string $mimeType,
        int $fileSize,
        ?string $checksum,
        int $uploadedBy
    ): void {
        $statement = Database::connection()->prepare(
            "UPDATE documents
             SET current_version_no = :current_version_no,
                 latest_file_name = :latest_file_name,
                 latest_file_path = :latest_file_path,
                 mime_type = :mime_type,
                 file_size = :file_size,
                 checksum_sha256 = :checksum_sha256,
                 uploaded_by = :uploaded_by,
                 uploaded_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'current_version_no' => $versionNo,
            'latest_file_name' => $fileName,
            'latest_file_path' => $filePath,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'checksum_sha256' => $checksum,
            'uploaded_by' => $uploadedBy,
            'id' => $documentId,
        ]);
    }

    public function addVersion(
        int $documentId,
        int $versionNo,
        string $fileName,
        string $filePath,
        string $mimeType,
        int $fileSize,
        ?string $checksum,
        ?string $changeNote,
        int $uploadedBy
    ): void {
        $statement = Database::connection()->prepare(
            "INSERT INTO document_versions (
                document_id, version_no, file_name, file_path, mime_type, file_size, checksum_sha256, change_note, uploaded_by, uploaded_at
             ) VALUES (
                :document_id, :version_no, :file_name, :file_path, :mime_type, :file_size, :checksum_sha256, :change_note, :uploaded_by, NOW()
             )"
        );
        $statement->execute([
            'document_id' => $documentId,
            'version_no' => $versionNo,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'checksum_sha256' => $checksum,
            'change_note' => $changeNote,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function registerSummary(): array
    {
        return [
            'total' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1"),
            'pending_verification' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1 AND verification_status = 'PENDING'"),
            'verified' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1 AND verification_status = 'VERIFIED'"),
            'requested' => $this->scalar("SELECT COUNT(*) FROM document_requests WHERE status IN ('REQUESTED','RECEIVED')"),
            'in_movement' => $this->scalar("SELECT COUNT(*) FROM document_movements WHERE status = 'OPEN'"),
            'archived' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1 AND archived_at IS NOT NULL"),
        ];
    }

    public function paginateRegister(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM documents d
            LEFT JOIN clients c ON c.id = d.client_id
            LEFT JOIN service_orders so ON d.linked_module = 'SO' AND so.id = d.linked_id
            WHERE d.is_active = 1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (d.document_name LIKE :search OR d.document_category LIKE :search OR c.legal_name LIKE :search OR so.so_no LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND d.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        if (trim((string) ($filters['verification_status'] ?? '')) !== '') {
            $whereSql .= " AND d.verification_status = :verification_status";
            $params['verification_status'] = trim((string) $filters['verification_status']);
        }

        if (trim((string) ($filters['document_category'] ?? '')) !== '') {
            $whereSql .= " AND d.document_category = :document_category";
            $params['document_category'] = trim((string) $filters['document_category']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT d.id, d.document_name, d.document_category, d.linked_module, d.linked_id,
                    d.current_version_no, d.verification_status, d.uploaded_at, d.archived_at,
                    d.returned_at, c.legal_name AS client_name, so.so_no
             {$baseSql}{$whereSql}
             ORDER BY d.uploaded_at DESC, d.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function verifyDocument(int $documentId, int $verifiedBy, string $status, ?string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE documents SET verification_status = :status, verified_by = :verified_by, verified_at = NOW() WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'verified_by' => $verifiedBy, 'id' => $documentId]);
    }

    public function paginateRequests(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM document_requests dr
            LEFT JOIN clients c ON c.id = dr.client_id
            LEFT JOIN service_orders so ON so.id = dr.service_order_id
            LEFT JOIN users req_by ON req_by.id = dr.requested_by
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND dr.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND dr.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT dr.*, c.legal_name AS client_name, so.so_no, req_by.full_name AS requested_by_name
             {$baseSql}{$whereSql}
             ORDER BY dr.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function createRequest(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO document_requests (client_id, service_order_id, requested_by, assigned_to, document_title, document_category, description, due_date, status, remarks)
             VALUES (:client_id, :service_order_id, :requested_by, :assigned_to, :document_title, :document_category, :description, :due_date, 'REQUESTED', :remarks)"
        );
        $statement->execute([
            'client_id' => $data['client_id'],
            'service_order_id' => $data['service_order_id'] ?: null,
            'requested_by' => $data['requested_by'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'document_title' => $data['document_title'],
            'document_category' => $data['document_category'] ?: null,
            'description' => $data['description'] ?: null,
            'due_date' => $data['due_date'] ?: null,
            'remarks' => $data['remarks'] ?: null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function updateRequestStatus(int $requestId, string $status, ?int $receivedDocumentId, ?string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE document_requests SET status = :status, received_document_id = :received_document_id, remarks = :remarks, received_at = IF(:status = 'RECEIVED', NOW(), received_at) WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'received_document_id' => $receivedDocumentId, 'remarks' => $remarks, 'id' => $requestId]);
    }

    public function paginateMovements(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM document_movements dm
            LEFT JOIN documents d ON d.id = dm.document_id
            LEFT JOIN clients c ON c.id = dm.client_id
            LEFT JOIN users from_u ON from_u.id = dm.from_user_id
            LEFT JOIN users to_u ON to_u.id = dm.to_user_id
            LEFT JOIN users created_by_u ON created_by_u.id = dm.created_by
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND dm.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        if (trim((string) ($filters['movement_type'] ?? '')) !== '') {
            $whereSql .= " AND dm.movement_type = :movement_type";
            $params['movement_type'] = trim((string) $filters['movement_type']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT dm.*, d.document_name, c.legal_name AS client_name,
                    from_u.full_name AS from_user_name, to_u.full_name AS to_user_name, created_by_u.full_name AS created_by_name
             {$baseSql}{$whereSql}
             ORDER BY dm.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function createMovement(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO document_movements (document_id, client_id, service_order_id, from_user_id, to_user_id, from_location, to_location, movement_type, purpose, movement_date, expected_return_date, remarks, created_by)
             VALUES (:document_id, :client_id, :service_order_id, :from_user_id, :to_user_id, :from_location, :to_location, :movement_type, :purpose, NOW(), :expected_return_date, :remarks, :created_by)"
        );
        $statement->execute([
            'document_id' => $data['document_id'],
            'client_id' => $data['client_id'] ?: null,
            'service_order_id' => $data['service_order_id'] ?: null,
            'from_user_id' => $data['from_user_id'] ?: null,
            'to_user_id' => $data['to_user_id'] ?: null,
            'from_location' => $data['from_location'] ?: null,
            'to_location' => $data['to_location'] ?: null,
            'movement_type' => $data['movement_type'],
            'purpose' => $data['purpose'] ?: null,
            'expected_return_date' => $data['expected_return_date'] ?: null,
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function returnMovement(int $movementId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE document_movements SET status = 'RETURNED', returned_at = NOW() WHERE id = :id"
        );
        $statement->execute(['id' => $movementId]);
    }

    public function archiveMovement(int $movementId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE document_movements SET status = 'ARCHIVED' WHERE id = :id"
        );
        $statement->execute(['id' => $movementId]);
    }

    public function movementsForDocument(int $documentId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT dm.*, from_u.full_name AS from_user_name, to_u.full_name AS to_user_name
             FROM document_movements dm
             LEFT JOIN users from_u ON from_u.id = dm.from_user_id
             LEFT JOIN users to_u ON to_u.id = dm.to_user_id
             WHERE dm.document_id = :document_id
             ORDER BY dm.id DESC"
        );
        $statement->execute(['document_id' => $documentId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allActive(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT d.id, d.document_name, d.document_category, c.legal_name AS client_name
             FROM documents d
             LEFT JOIN clients c ON c.id = d.client_id
             WHERE d.is_active = 1
             ORDER BY d.document_name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }
}
