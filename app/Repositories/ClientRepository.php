<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ClientRepository
{
    public function paginateSearch(string $search = '', bool $includeArchived = false, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*)
                     FROM clients c
                     WHERE 1 = 1";

        $dataSql = "SELECT c.id,
                       c.client_code,
                       c.legal_name,
                       c.trade_name,
                       c.pan,
                       c.tan,
                       c.gstin,
                       c.email,
                       c.mobile,
                       c.is_active,
                       c.archived_at,
                       crm.full_name AS assigned_crm_name
                FROM clients c
                LEFT JOIN users crm ON crm.id = c.assigned_crm_id
                WHERE 1 = 1";

        $params = [];

        if (!$includeArchived) {
            $countSql .= " AND c.is_active = 1";
            $dataSql .= " AND c.is_active = 1";
        }

        if (trim($search) !== '') {
            $filterSql = " AND (
                c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
                OR c.legal_name LIKE :search_legal_name
                OR c.mobile LIKE :search_mobile
                OR c.gstin LIKE :search_gstin
            )";
            $countSql .= $filterSql;
            $dataSql .= $filterSql;
            $searchTerm = '%' . trim($search) . '%';
            $params['search_pan'] = $searchTerm;
            $params['search_tan'] = $searchTerm;
            $params['search_legal_name'] = $searchTerm;
            $params['search_mobile'] = $searchTerm;
            $params['search_gstin'] = $searchTerm;
        }

        $countStatement = Database::connection()->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataSql .= " ORDER BY c.legal_name ASC LIMIT :limit OFFSET :offset";

        $statement = Database::connection()->prepare($dataSql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function allActive(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, client_code, legal_name, pan, tan, mobile, gstin, aadhaar_last4, assigned_crm_id
             FROM clients
             WHERE is_active = 1
             ORDER BY legal_name ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $clientId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT c.*,
                    crm.full_name AS assigned_crm_name
             FROM clients c
             LEFT JOIN users crm ON crm.id = c.assigned_crm_id
             WHERE c.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $clientId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function primaryContact(int $clientId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM client_contacts
             WHERE client_id = :client_id
             ORDER BY is_primary DESC, id ASC
             LIMIT 1"
        );
        $statement->execute(['client_id' => $clientId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function crmUsers(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT DISTINCT u.id, u.full_name
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code IN ('CRM', 'ASSISTANT_CRM', 'ADMIN', 'SUPER_ADMIN')
               AND u.is_active = 1
             ORDER BY u.full_name ASC"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO clients (
                client_code, client_type, legal_name, trade_name, pan, tan, gstin, aadhaar_no, aadhaar_ciphertext, aadhaar_iv, aadhaar_last4, email, mobile, alternate_mobile,
                landline, address_line1, address_line2, city, state_name, postal_code, default_company_id, assigned_crm_id,
                onboarded_at, is_active, created_at, updated_at
            ) VALUES (
                :client_code, :client_type, :legal_name, :trade_name, :pan, :tan, :gstin, :aadhaar_no, :aadhaar_ciphertext, :aadhaar_iv, :aadhaar_last4, :email, :mobile, :alternate_mobile,
                :landline, :address_line1, :address_line2, :city, :state_name, :postal_code, :default_company_id, :assigned_crm_id,
                NOW(), 1, NOW(), NOW()
            )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $clientId, array $payload): void
    {
        $payload['id'] = $clientId;
        $statement = Database::connection()->prepare(
            "UPDATE clients
             SET client_type = :client_type,
                 legal_name = :legal_name,
                 trade_name = :trade_name,
                 pan = :pan,
                 tan = :tan,
                 gstin = :gstin,
                 aadhaar_no = :aadhaar_no,
                 aadhaar_ciphertext = :aadhaar_ciphertext,
                 aadhaar_iv = :aadhaar_iv,
                 aadhaar_last4 = :aadhaar_last4,
                 email = :email,
                 mobile = :mobile,
                 alternate_mobile = :alternate_mobile,
                 landline = :landline,
                 address_line1 = :address_line1,
                 address_line2 = :address_line2,
                 city = :city,
                 state_name = :state_name,
                 postal_code = :postal_code,
                 default_company_id = :default_company_id,
                 assigned_crm_id = :assigned_crm_id,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);
    }

    public function createContact(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO client_contacts (
                client_id, contact_name, designation, email, mobile, is_primary, can_login, created_at, updated_at
             ) VALUES (
                :client_id, :contact_name, :designation, :email, :mobile, 1, :can_login, NOW(), NOW()
             )"
        );
        $statement->execute([
            'client_id' => $payload['client_id'],
            'contact_name' => $payload['contact_name'],
            'designation' => $payload['designation'],
            'email' => $payload['email'],
            'mobile' => $payload['mobile'],
            'can_login' => !empty($payload['can_login']) ? 1 : 0,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function findContactWithClientById(int $contactId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT cc.id,
                    cc.client_id,
                    cc.contact_name,
                    cc.email,
                    cc.mobile,
                    cc.can_login,
                    c.legal_name,
                    c.pan,
                    c.tan,
                    c.aadhaar_ciphertext,
                    c.aadhaar_iv
             FROM client_contacts cc
             INNER JOIN clients c ON c.id = cc.client_id
             WHERE cc.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $contactId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function updatePrimaryContact(int $contactId, array $payload): void
    {
        $payload['id'] = $contactId;
        $statement = Database::connection()->prepare(
            "UPDATE client_contacts
             SET contact_name = :contact_name,
                 designation = :designation,
                 email = :email,
                 mobile = :mobile,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);
    }

    public function archive(int $clientId, string $reason): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE clients
             SET is_active = 0,
                 archived_at = NOW(),
                 archive_reason = :archive_reason,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'archive_reason' => $reason,
            'id' => $clientId,
        ]);
    }

    public function panExists(string $pan, ?int $ignoreClientId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM clients WHERE pan = :pan";
        $params = ['pan' => $pan];

        if ($ignoreClientId !== null) {
            $sql .= " AND id <> :id";
            $params['id'] = $ignoreClientId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function serviceOrders(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.id,
                    so.so_no,
                    so.title,
                    so.current_stage_code,
                    so.created_at,
                    st.name AS service_type_name
             FROM service_orders so
             INNER JOIN service_types st ON st.id = so.service_type_id
             WHERE so.client_id = :client_id
             ORDER BY so.id DESC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function identityDocuments(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, document_category, document_name, latest_file_name, uploaded_at
             FROM documents
             WHERE client_id = :client_id
               AND linked_module = 'CLIENT'
               AND linked_id = :linked_id
               AND document_category IN ('CLIENT_PAN_CARD_IMAGE', 'CLIENT_AADHAAR_CARD_IMAGE')
               AND is_active = 1
             ORDER BY uploaded_at DESC, id DESC"
        );
        $statement->execute([
            'client_id' => $clientId,
            'linked_id' => $clientId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function portalCredentials(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id,
                    portal_code,
                    portal_label,
                    user_identifier,
                    updated_at,
                    CASE WHEN password_ciphertext IS NOT NULL AND password_ciphertext <> '' THEN 1 ELSE 0 END AS has_password
             FROM client_portal_credentials
             WHERE client_id = :client_id
               AND is_active = 1
             ORDER BY portal_code ASC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function portalCredentialByCode(int $clientId, string $portalCode): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM client_portal_credentials
             WHERE client_id = :client_id
               AND portal_code = :portal_code
             LIMIT 1"
        );
        $statement->execute([
            'client_id' => $clientId,
            'portal_code' => $portalCode,
        ]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function createPortalCredential(array $payload): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO client_portal_credentials (
                client_id, portal_code, portal_label, user_identifier, password_ciphertext, password_iv, portal_url, remarks,
                last_verified_at, is_active, created_by, updated_by, created_at, updated_at
            ) VALUES (
                :client_id, :portal_code, :portal_label, :user_identifier, :password_ciphertext, :password_iv, :portal_url, :remarks,
                :last_verified_at, 1, :created_by, :updated_by, NOW(), NOW()
            )"
        );
        $statement->execute($payload);
    }

    public function updatePortalCredential(int $credentialId, array $payload): void
    {
        $payload['id'] = $credentialId;
        $statement = Database::connection()->prepare(
            "UPDATE client_portal_credentials
             SET portal_label = :portal_label,
                 user_identifier = :user_identifier,
                 password_ciphertext = :password_ciphertext,
                 password_iv = :password_iv,
                 portal_url = :portal_url,
                 remarks = :remarks,
                 last_verified_at = :last_verified_at,
                 is_active = 1,
                 updated_by = :updated_by,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);
    }

    public function countLegacyPlaintextAadhaar(): int
    {
        $statement = Database::connection()->query(
            "SELECT COUNT(*)
             FROM clients
             WHERE aadhaar_no IS NOT NULL
               AND aadhaar_no <> ''
               AND (aadhaar_ciphertext IS NULL OR aadhaar_ciphertext = '')"
        );

        return (int) $statement->fetchColumn();
    }

    public function legacyPlaintextAadhaarRows(int $limit = 100): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, client_code, legal_name, aadhaar_no, aadhaar_last4
             FROM clients
             WHERE aadhaar_no IS NOT NULL
               AND aadhaar_no <> ''
               AND (aadhaar_ciphertext IS NULL OR aadhaar_ciphertext = '')
             ORDER BY id ASC
             LIMIT :row_limit"
        );
        $statement->bindValue(':row_limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function backfillEncryptedAadhaar(int $clientId, string $ciphertext, string $iv, ?string $last4): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE clients
             SET aadhaar_ciphertext = :aadhaar_ciphertext,
                 aadhaar_iv = :aadhaar_iv,
                 aadhaar_last4 = COALESCE(:aadhaar_last4, aadhaar_last4),
                 updated_at = NOW()
             WHERE id = :id
               AND aadhaar_no IS NOT NULL
               AND aadhaar_no <> ''
               AND (aadhaar_ciphertext IS NULL OR aadhaar_ciphertext = '')"
        );
        $statement->execute([
            'aadhaar_ciphertext' => $ciphertext,
            'aadhaar_iv' => $iv,
            'aadhaar_last4' => $last4,
            'id' => $clientId,
        ]);
    }

    public function hasEncryptedAadhaarColumns(): bool
    {
        $statement = Database::connection()->query(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'clients'
               AND COLUMN_NAME IN ('aadhaar_ciphertext', 'aadhaar_iv')"
        );

        return (int) $statement->fetchColumn() === 2;
    }
}
