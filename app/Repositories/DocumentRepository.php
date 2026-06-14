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
}
