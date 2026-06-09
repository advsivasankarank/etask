<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

final class UserRepository
{
    public function findByUsername(string $username): ?User
    {
        $sql = "SELECT u.id, u.client_contact_id, cc.client_id, u.username, u.password_hash, u.full_name, u.email, u.is_active, u.locked_until, u.must_change_password
                FROM users u
                LEFT JOIN client_contacts cc ON cc.id = u.client_contact_id
                WHERE u.username = :username
                LIMIT 1";

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['username' => $username]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record === false) {
            return null;
        }

        return new User(
            (int) $record['id'],
            isset($record['client_contact_id']) ? (int) $record['client_contact_id'] : null,
            isset($record['client_id']) ? (int) $record['client_id'] : null,
            $record['username'],
            $record['password_hash'],
            $record['full_name'],
            $record['email'],
            (bool) $record['is_active'],
            $record['locked_until'],
            (bool) $record['must_change_password'],
            $this->getRoleCodes((int) $record['id']),
            $this->effectivePermissionCodes((int) $record['id'])
        );
    }

    public function paginateSearch(string $search = '', bool $portalOnly = false, bool $includeInactive = true, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(DISTINCT u.id)
                FROM users u
                LEFT JOIN client_contacts cc ON cc.id = u.client_contact_id
                LEFT JOIN clients c ON c.id = cc.client_id
                WHERE 1 = 1";

        $dataSql = "SELECT u.id,
                       u.employee_code,
                       u.client_contact_id,
                       u.username,
                       u.full_name,
                       u.email,
                       u.mobile,
                       u.must_change_password,
                       u.last_login_at,
                       u.is_active,
                       cc.contact_name,
                       c.legal_name AS client_name,
                       GROUP_CONCAT(DISTINCT r.code ORDER BY r.code SEPARATOR ', ') AS role_codes,
                       GROUP_CONCAT(DISTINCT r.label ORDER BY r.label SEPARATOR ', ') AS role_labels
                FROM users u
                LEFT JOIN client_contacts cc ON cc.id = u.client_contact_id
                LEFT JOIN clients c ON c.id = cc.client_id
                LEFT JOIN user_role_map urm ON urm.user_id = u.id
                LEFT JOIN roles r ON r.id = urm.role_id
                WHERE 1 = 1";

        $params = [];

        if ($portalOnly) {
            $portalClause = " AND EXISTS (
                SELECT 1
                FROM user_role_map xurm
                INNER JOIN roles xr ON xr.id = xurm.role_id
                WHERE xurm.user_id = u.id AND xr.code = 'CLIENT'
            )";
            $countSql .= $portalClause;
            $dataSql .= $portalClause;
        }

        if (!$includeInactive) {
            $countSql .= " AND u.is_active = 1";
            $dataSql .= " AND u.is_active = 1";
        }

        if (trim($search) !== '') {
            $filterSql = " AND (
                u.username LIKE :search_username
                OR u.full_name LIKE :search_full_name
                OR u.email LIKE :search_email
                OR u.mobile LIKE :search_mobile
                OR c.legal_name LIKE :search_client_name
                OR cc.contact_name LIKE :search_contact_name
            )";
            $countSql .= $filterSql;
            $dataSql .= $filterSql;
            $searchTerm = '%' . trim($search) . '%';
            $params['search_username'] = $searchTerm;
            $params['search_full_name'] = $searchTerm;
            $params['search_email'] = $searchTerm;
            $params['search_mobile'] = $searchTerm;
            $params['search_client_name'] = $searchTerm;
            $params['search_contact_name'] = $searchTerm;
        }

        $countStatement = Database::connection()->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataSql .= " GROUP BY u.id ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

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

    public function findDetailedById(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT u.*,
                    cc.contact_name,
                    cc.designation,
                    c.id AS client_id,
                    c.legal_name AS client_name
             FROM users u
             LEFT JOIN client_contacts cc ON cc.id = u.client_contact_id
             LEFT JOIN clients c ON c.id = cc.client_id
             WHERE u.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $userId]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record === false) {
            return null;
        }

        $record['roles'] = $this->rolesForUser($userId);

        return $record;
    }

    public function activeRoles(bool $portalOnly = false): array
    {
        $sql = "SELECT id, code, label, scope
                FROM roles
                WHERE is_active = 1";

        if ($portalOnly) {
            $sql .= " AND code = 'CLIENT'";
        }

        $sql .= " ORDER BY scope ASC, label ASC";

        $statement = Database::connection()->query($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeClientContacts(): array
    {
        $statement = Database::connection()->query(
            "SELECT cc.id,
                    cc.contact_name,
                    cc.email,
                    cc.mobile,
                    c.id AS client_id,
                    c.legal_name AS client_name,
                    existing.id AS user_id
             FROM client_contacts cc
             INNER JOIN clients c ON c.id = cc.client_id
             LEFT JOIN users existing ON existing.client_contact_id = cc.id AND existing.is_active = 1
             WHERE c.is_active = 1
             ORDER BY c.legal_name ASC, cc.contact_name ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO users (
                employee_code, client_contact_id, username, password_hash, full_name, email, mobile,
                auth_type, must_change_password, last_password_changed_at, is_active, created_at, updated_at
            ) VALUES (
                :employee_code, :client_contact_id, :username, :password_hash, :full_name, :email, :mobile,
                'LOCAL', :must_change_password, NOW(), 1, NOW(), NOW()
            )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $userId, array $payload): void
    {
        $payload['id'] = $userId;
        $statement = Database::connection()->prepare(
            "UPDATE users
             SET employee_code = :employee_code,
                 client_contact_id = :client_contact_id,
                 username = :username,
                 full_name = :full_name,
                 email = :email,
                 mobile = :mobile,
                 must_change_password = :must_change_password,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);
    }

    public function updatePassword(int $userId, string $passwordHash, bool $mustChangePassword = true): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE users
             SET password_hash = :password_hash,
                 must_change_password = :must_change_password,
                 failed_login_attempts = 0,
                 locked_until = NULL,
                 last_password_changed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'must_change_password' => $mustChangePassword ? 1 : 0,
            'id' => $userId,
        ]);
    }

    public function setActiveState(int $userId, bool $isActive): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE users
             SET is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId,
        ]);
    }

    public function clearRoles(int $userId): void
    {
        $statement = Database::connection()->prepare("DELETE FROM user_role_map WHERE user_id = :user_id");
        $statement->execute(['user_id' => $userId]);
    }

    public function assignRole(int $userId, int $roleId, ?int $assignedBy): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO user_role_map (user_id, role_id, assigned_by, assigned_at)
             VALUES (:user_id, :role_id, :assigned_by, NOW())"
        );
        $statement->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by' => $assignedBy,
        ]);
    }

    public function usernameExists(string $username, ?int $ignoreUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
        $params = ['username' => $username];

        if ($ignoreUserId !== null) {
            $sql .= " AND id <> :id";
            $params['id'] = $ignoreUserId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $params = ['email' => $email];

        if ($ignoreUserId !== null) {
            $sql .= " AND id <> :id";
            $params['id'] = $ignoreUserId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function rolesForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT r.id, r.code, r.label, r.scope
             FROM user_role_map urm
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE urm.user_id = :user_id
             ORDER BY r.label ASC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function permissionCatalog(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, code, module_code, action_code, label, description
             FROM permissions
             WHERE is_active = 1
             ORDER BY module_code ASC, label ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function directGrantedPermissionCodes(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.code
             FROM user_permissions up
             INNER JOIN permissions p ON p.id = up.permission_id
             WHERE up.user_id = :user_id
               AND up.is_granted = 1
               AND p.is_active = 1
             ORDER BY p.code ASC"
        );
        $statement->execute(['user_id' => $userId]);

        return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'code');
    }

    public function rolePermissionCodes(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT DISTINCT p.code
             FROM user_role_map urm
             INNER JOIN role_permissions rp ON rp.role_id = urm.role_id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE urm.user_id = :user_id
               AND rp.is_granted = 1
               AND p.is_active = 1
             ORDER BY p.code ASC"
        );
        $statement->execute(['user_id' => $userId]);

        return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'code');
    }

    public function clearUserPermissions(int $userId): void
    {
        $statement = Database::connection()->prepare("DELETE FROM user_permissions WHERE user_id = :user_id");
        $statement->execute(['user_id' => $userId]);
    }

    public function grantUserPermission(int $userId, int $permissionId, ?int $assignedBy, ?string $notes = null): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO user_permissions (
                user_id, permission_id, is_granted, assigned_by, notes, assigned_at, created_at, updated_at
             ) VALUES (
                :user_id, :permission_id, 1, :assigned_by, :notes, NOW(), NOW(), NOW()
             )"
        );
        $statement->execute([
            'user_id' => $userId,
            'permission_id' => $permissionId,
            'assigned_by' => $assignedBy,
            'notes' => $notes,
        ]);
    }

    public function effectivePermissionCodes(int $userId): array
    {
        $grantedPermissions = [];

        $rolePermissionStatement = Database::connection()->prepare(
            "SELECT p.code, rp.is_granted
             FROM user_role_map urm
             INNER JOIN role_permissions rp ON rp.role_id = urm.role_id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE urm.user_id = :user_id
               AND p.is_active = 1"
        );
        $rolePermissionStatement->execute(['user_id' => $userId]);

        foreach ($rolePermissionStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ((int) ($row['is_granted'] ?? 0) === 1) {
                $grantedPermissions[(string) $row['code']] = true;
            }
        }

        $userPermissionStatement = Database::connection()->prepare(
            "SELECT p.code, up.is_granted
             FROM user_permissions up
             INNER JOIN permissions p ON p.id = up.permission_id
             WHERE up.user_id = :user_id
               AND p.is_active = 1"
        );
        $userPermissionStatement->execute(['user_id' => $userId]);

        foreach ($userPermissionStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = (string) $row['code'];

            if ((int) ($row['is_granted'] ?? 0) === 1) {
                $grantedPermissions[$code] = true;
                continue;
            }

            unset($grantedPermissions[$code]);
        }

        $codes = array_keys($grantedPermissions);
        sort($codes);

        return $codes;
    }

    public function recordActivity(?int $actorUserId, string $actionCode, int $entityId, string $description): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO activity_logs (user_id, module_code, action_code, entity_type, entity_id, description, created_at)
             VALUES (:user_id, 'USERS', :action_code, 'users', :entity_id, :description, NOW())"
        );
        $statement->execute([
            'user_id' => $actorUserId,
            'action_code' => $actionCode,
            'entity_id' => $entityId,
            'description' => $description,
        ]);
    }

    public function updateSuccessfulLogin(int $userId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE users
             SET last_login_at = NOW(), failed_login_attempts = 0, locked_until = NULL
             WHERE id = :id"
        );
        $statement->execute(['id' => $userId]);
    }

    public function incrementFailedLogin(string $username, int $maxAttempts = 5, int $lockMinutes = 15): void
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        $statement = $connection->prepare("SELECT id, failed_login_attempts FROM users WHERE username = :username LIMIT 1 FOR UPDATE");
        $statement->execute(['username' => $username]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record !== false) {
            $attempts = (int) $record['failed_login_attempts'] + 1;
            $lockUntil = $attempts >= $maxAttempts ? date('Y-m-d H:i:s', strtotime("+{$lockMinutes} minutes")) : null;

            $update = $connection->prepare(
                "UPDATE users
                 SET failed_login_attempts = :attempts, locked_until = :locked_until
                 WHERE id = :id"
            );
            $update->execute([
                'attempts' => $attempts,
                'locked_until' => $lockUntil,
                'id' => (int) $record['id'],
            ]);
        }

        $connection->commit();
    }

    public function findPortalPasswordResetCandidate(string $username, string $verification): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT u.id,
                    u.username,
                    u.full_name,
                    u.email,
                    u.mobile,
                    cc.mobile AS contact_mobile,
                    cc.email AS contact_email
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id AND r.code = 'CLIENT'
             LEFT JOIN client_contacts cc ON cc.id = u.client_contact_id
             WHERE u.username = :username
               AND u.is_active = 1
             LIMIT 1"
        );
        $statement->execute(['username' => $username]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record === false) {
            return null;
        }

        $verification = trim($verification);
        $normalizedVerification = strtolower($verification);
        $digitsOnly = preg_replace('/\D+/', '', $verification) ?? '';

        $emailMatches = in_array($normalizedVerification, array_filter([
            strtolower((string) ($record['email'] ?? '')),
            strtolower((string) ($record['contact_email'] ?? '')),
        ]), true);

        $mobileMatches = $digitsOnly !== '' && in_array($digitsOnly, array_filter([
            preg_replace('/\D+/', '', (string) ($record['mobile'] ?? '')) ?: '',
            preg_replace('/\D+/', '', (string) ($record['contact_mobile'] ?? '')) ?: '',
        ]), true);

        return ($emailMatches || $mobileMatches) ? $record : null;
    }

    public function createPasswordResetToken(int $userId, string $selector, string $tokenHash, string $audience, string $expiresAt): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO password_reset_tokens (
                user_id, selector, token_hash, audience, expires_at, created_at
             ) VALUES (
                :user_id, :selector, :token_hash, :audience, :expires_at, NOW()
             )"
        );
        $statement->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'audience' => $audience,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findActivePasswordResetToken(string $selector, string $audience): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT prt.*,
                    u.username,
                    u.full_name,
                    u.is_active
             FROM password_reset_tokens prt
             INNER JOIN users u ON u.id = prt.user_id
             WHERE prt.selector = :selector
               AND prt.audience = :audience
               AND prt.used_at IS NULL
               AND prt.expires_at >= NOW()
             ORDER BY prt.id DESC
             LIMIT 1"
        );
        $statement->execute([
            'selector' => $selector,
            'audience' => $audience,
        ]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function consumePasswordResetToken(int $tokenId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE password_reset_tokens
             SET used_at = NOW()
             WHERE id = :id"
        );
        $statement->execute(['id' => $tokenId]);
    }

    private function getRoleCodes(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT r.code
             FROM user_role_map urm
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE urm.user_id = :user_id"
        );
        $statement->execute(['user_id' => $userId]);

        return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'code');
    }
}
