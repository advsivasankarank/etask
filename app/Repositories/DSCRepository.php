<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DSCRepository
{
    public function summaryCounts(): array
    {
        return [
            'total' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1"),
            'in_office' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND custody_status = 'WITH_OFFICE'"),
            'with_staff' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND custody_status = 'WITH_STAFF'"),
            'with_client' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND custody_status = 'WITH_CLIENT'"),
            'expiring_soon' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND valid_to IS NOT NULL AND valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"),
            'expired' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND valid_to IS NOT NULL AND valid_to < CURDATE()"),
            'archived' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND archived_at IS NOT NULL"),
        ];
    }

    public function paginateRegister(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM dsc_register dr
            LEFT JOIN clients c ON c.id = dr.client_id
            LEFT JOIN users u ON u.id = dr.assigned_user_id
            WHERE dr.is_active = 1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (dr.holder_name LIKE :search OR dr.holder_pan LIKE :search OR dr.token_serial_no LIKE :search OR c.legal_name LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['custody_status'] ?? '')) !== '') {
            $whereSql .= " AND dr.custody_status = :custody_status";
            $params['custody_status'] = trim((string) $filters['custody_status']);
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND dr.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT dr.*, c.legal_name AS client_name, u.full_name AS assigned_user_name
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

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT dr.*, c.legal_name AS client_name, u.full_name AS assigned_user_name
             FROM dsc_register dr
             LEFT JOIN clients c ON c.id = dr.client_id
             LEFT JOIN users u ON u.id = dr.assigned_user_id
             WHERE dr.id = :id AND dr.is_active = 1
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO dsc_register (client_id, holder_name, holder_pan, holder_email, holder_mobile, token_serial_no, dsc_type, provider_name, valid_from, valid_to, custody_status, assigned_user_id, storage_location, password_status, remarks, created_by)
             VALUES (:client_id, :holder_name, :holder_pan, :holder_email, :holder_mobile, :token_serial_no, :dsc_type, :provider_name, :valid_from, :valid_to, :custody_status, :assigned_user_id, :storage_location, :password_status, :remarks, :created_by)"
        );
        $statement->execute([
            'client_id' => $data['client_id'] ?: null,
            'holder_name' => $data['holder_name'],
            'holder_pan' => $data['holder_pan'] ?: null,
            'holder_email' => $data['holder_email'] ?: null,
            'holder_mobile' => $data['holder_mobile'] ?: null,
            'token_serial_no' => $data['token_serial_no'] ?: null,
            'dsc_type' => $data['dsc_type'] ?: null,
            'provider_name' => $data['provider_name'] ?: null,
            'valid_from' => $data['valid_from'] ?: null,
            'valid_to' => $data['valid_to'] ?: null,
            'custody_status' => $data['custody_status'] ?? 'WITH_CLIENT',
            'assigned_user_id' => $data['assigned_user_id'] ?: null,
            'storage_location' => $data['storage_location'] ?: null,
            'password_status' => $data['password_status'] ?? 'NOT_STORED',
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_register SET client_id = :client_id, holder_name = :holder_name, holder_pan = :holder_pan, holder_email = :holder_email, holder_mobile = :holder_mobile, token_serial_no = :token_serial_no, dsc_type = :dsc_type, provider_name = :provider_name, valid_from = :valid_from, valid_to = :valid_to, custody_status = :custody_status, assigned_user_id = :assigned_user_id, storage_location = :storage_location, password_status = :password_status, remarks = :remarks WHERE id = :id"
        );
        $statement->execute([
            'client_id' => $data['client_id'] ?: null,
            'holder_name' => $data['holder_name'],
            'holder_pan' => $data['holder_pan'] ?: null,
            'holder_email' => $data['holder_email'] ?: null,
            'holder_mobile' => $data['holder_mobile'] ?: null,
            'token_serial_no' => $data['token_serial_no'] ?: null,
            'dsc_type' => $data['dsc_type'] ?: null,
            'provider_name' => $data['provider_name'] ?: null,
            'valid_from' => $data['valid_from'] ?: null,
            'valid_to' => $data['valid_to'] ?: null,
            'custody_status' => $data['custody_status'] ?? 'WITH_CLIENT',
            'assigned_user_id' => $data['assigned_user_id'] ?: null,
            'storage_location' => $data['storage_location'] ?: null,
            'password_status' => $data['password_status'] ?? 'NOT_STORED',
            'remarks' => $data['remarks'] ?: null,
            'id' => $id,
        ]);
    }

    public function archive(int $id): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_register SET is_active = 0, archived_at = NOW(), custody_status = 'ARCHIVED' WHERE id = :id"
        );
        $statement->execute(['id' => $id]);
    }

    public function movementsForDSC(int $dscId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT dm.*, from_u.full_name AS from_user_name, to_u.full_name AS to_user_name, created_u.full_name AS created_by_name
             FROM dsc_movements dm
             LEFT JOIN users from_u ON from_u.id = dm.from_user_id
             LEFT JOIN users to_u ON to_u.id = dm.to_user_id
             LEFT JOIN users created_u ON created_u.id = dm.created_by
             WHERE dm.dsc_id = :dsc_id
             ORDER BY dm.id DESC"
        );
        $statement->execute(['dsc_id' => $dscId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usageLogsForDSC(int $dscId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT dul.*, u.full_name AS used_by_name, c.legal_name AS client_name, so.so_no
             FROM dsc_usage_logs dul
             LEFT JOIN users u ON u.id = dul.used_by
             LEFT JOIN clients c ON c.id = dul.client_id
             LEFT JOIN service_orders so ON so.id = dul.service_order_id
             WHERE dul.dsc_id = :dsc_id
             ORDER BY dul.id DESC"
        );
        $statement->execute(['dsc_id' => $dscId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function renewalsForDSC(int $dscId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM dsc_renewals WHERE dsc_id = :dsc_id ORDER BY id DESC"
        );
        $statement->execute(['dsc_id' => $dscId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginateMovements(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM dsc_movements dm
            LEFT JOIN dsc_register dr ON dr.id = dm.dsc_id
            LEFT JOIN clients c ON c.id = dr.client_id
            LEFT JOIN users from_u ON from_u.id = dm.from_user_id
            LEFT JOIN users to_u ON to_u.id = dm.to_user_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND dm.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT dm.*, dr.holder_name, c.legal_name AS client_name, from_u.full_name AS from_user_name, to_u.full_name AS to_user_name
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
            "INSERT INTO dsc_movements (dsc_id, from_user_id, to_user_id, from_location, to_location, movement_type, movement_date, expected_return_date, purpose, remarks, created_by)
             VALUES (:dsc_id, :from_user_id, :to_user_id, :from_location, :to_location, :movement_type, NOW(), :expected_return_date, :purpose, :remarks, :created_by)"
        );
        $statement->execute([
            'dsc_id' => $data['dsc_id'],
            'from_user_id' => $data['from_user_id'] ?: null,
            'to_user_id' => $data['to_user_id'] ?: null,
            'from_location' => $data['from_location'] ?: null,
            'to_location' => $data['to_location'] ?: null,
            'movement_type' => $data['movement_type'],
            'expected_return_date' => $data['expected_return_date'] ?: null,
            'purpose' => $data['purpose'] ?: null,
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);

        $movementId = (int) Database::connection()->lastInsertId();

        // Update custody status based on movement type
        if ($data['movement_type'] === 'ASSIGNED' || $data['movement_type'] === 'TRANSFERRED') {
            $newStatus = !empty($data['to_user_id']) ? 'WITH_STAFF' : 'WITH_OFFICE';
            $this->updateCustodyStatus((int) $data['dsc_id'], $newStatus);
        } elseif ($data['movement_type'] === 'RETURNED') {
            $this->updateCustodyStatus((int) $data['dsc_id'], 'RETURNED');
        } elseif ($data['movement_type'] === 'ARCHIVED') {
            $this->updateCustodyStatus((int) $data['dsc_id'], 'ARCHIVED');
        }

        return $movementId;
    }

    public function returnMovement(int $movementId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_movements SET status = 'RETURNED', returned_at = NOW() WHERE id = :id"
        );
        $statement->execute(['id' => $movementId]);
    }

    public function archiveMovement(int $movementId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_movements SET status = 'ARCHIVED' WHERE id = :id"
        );
        $statement->execute(['id' => $movementId]);
    }

    public function paginateUsage(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM dsc_usage_logs dul
            LEFT JOIN dsc_register dr ON dr.id = dul.dsc_id
            LEFT JOIN clients c ON c.id = dul.client_id
            LEFT JOIN service_orders so ON so.id = dul.service_order_id
            LEFT JOIN users u ON u.id = dul.used_by
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT dul.*, dr.holder_name, c.legal_name AS client_name, so.so_no, u.full_name AS used_by_name
             {$baseSql}{$whereSql}
             ORDER BY dul.id DESC
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

    public function createUsage(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO dsc_usage_logs (dsc_id, client_id, service_order_id, used_by, usage_date, purpose, portal_or_department, filing_reference, acknowledgement_no, remarks, created_by)
             VALUES (:dsc_id, :client_id, :service_order_id, :used_by, NOW(), :purpose, :portal_or_department, :filing_reference, :acknowledgement_no, :remarks, :created_by)"
        );
        $statement->execute([
            'dsc_id' => $data['dsc_id'],
            'client_id' => $data['client_id'] ?: null,
            'service_order_id' => $data['service_order_id'] ?: null,
            'used_by' => $data['used_by'] ?: null,
            'purpose' => $data['purpose'],
            'portal_or_department' => $data['portal_or_department'] ?: null,
            'filing_reference' => $data['filing_reference'] ?: null,
            'acknowledgement_no' => $data['acknowledgement_no'] ?: null,
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function paginateRenewals(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM dsc_renewals drn
            LEFT JOIN dsc_register dr ON dr.id = drn.dsc_id
            LEFT JOIN clients c ON c.id = dr.client_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND drn.renewal_status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT drn.*, dr.holder_name, dr.valid_to, c.legal_name AS client_name
             {$baseSql}{$whereSql}
             ORDER BY drn.id DESC
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

    public function updateRenewal(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_renewals SET renewal_status = :renewal_status, remarks = :remarks, renewed_at = IF(:renewal_status = 'RENEWED', NOW(), renewed_at), new_valid_from = :new_valid_from, new_valid_to = :new_valid_to WHERE id = :id"
        );
        $statement->execute([
            'renewal_status' => $data['renewal_status'],
            'remarks' => $data['remarks'] ?: null,
            'new_valid_from' => $data['new_valid_from'] ?: null,
            'new_valid_to' => $data['new_valid_to'] ?: null,
            'id' => $id,
        ]);

        // If renewed, update main DSC validity
        if ($data['renewal_status'] === 'RENEWED' && !empty($data['new_valid_from']) && !empty($data['new_valid_to'])) {
            $renewal = $this->findRenewalById($id);
            if ($renewal !== null) {
                $statement = Database::connection()->prepare(
                    "UPDATE dsc_register SET valid_from = :valid_from, valid_to = :valid_to, custody_status = 'WITH_CLIENT' WHERE id = :id"
                );
                $statement->execute([
                    'valid_from' => $data['new_valid_from'],
                    'valid_to' => $data['new_valid_to'],
                    'id' => $renewal['dsc_id'],
                ]);
            }
        }
    }

    public function findRenewalById(int $id): ?array
    {
        $statement = Database::connection()->prepare("SELECT * FROM dsc_renewals WHERE id = :id LIMIT 1");
        $statement->execute(['id' => $id]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function createRenewal(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO dsc_renewals (dsc_id, renewal_due_date, renewal_status, remarks, created_by)
             VALUES (:dsc_id, :renewal_due_date, :renewal_status, :remarks, :created_by)"
        );
        $statement->execute([
            'dsc_id' => $data['dsc_id'],
            'renewal_due_date' => $data['renewal_due_date'] ?: null,
            'renewal_status' => $data['renewal_status'] ?? 'NOT_DUE',
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function allActive(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT dr.id, dr.holder_name, dr.holder_pan, c.legal_name AS client_name
             FROM dsc_register dr
             LEFT JOIN clients c ON c.id = dr.client_id
             WHERE dr.is_active = 1
             ORDER BY dr.holder_name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reportsData(array $filters): array
    {
        $baseSql = "FROM dsc_register dr
            LEFT JOIN clients c ON c.id = dr.client_id
            LEFT JOIN users u ON u.id = dr.assigned_user_id
            WHERE dr.is_active = 1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['custody_status'] ?? '')) !== '') {
            $whereSql .= " AND dr.custody_status = :custody_status";
            $params['custody_status'] = trim((string) $filters['custody_status']);
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND dr.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        $statement = Database::connection()->prepare(
            "SELECT dr.*, c.legal_name AS client_name, u.full_name AS assigned_user_name
             {$baseSql}{$whereSql}
             ORDER BY dr.valid_to ASC, dr.holder_name ASC"
        );
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateCustodyStatus(int $dscId, string $status): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE dsc_register SET custody_status = :status WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'id' => $dscId]);
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }
}
