<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\ClientRepository;
use App\Repositories\UserRepository;
use RuntimeException;
use Throwable;

final class UserService
{
    private UserRepository $users;
    private ClientRepository $clients;
    private EncryptionService $encryption;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->clients = new ClientRepository();
        $this->encryption = new EncryptionService();
    }

    public function create(array $input, array $actor): int
    {
        $userType = strtoupper(trim((string) ($input['user_type'] ?? 'INTERNAL')));
        $roleIds = array_map('intval', (array) ($input['role_ids'] ?? []));
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if ($username === '' || $fullName === '' || $email === '' || $password === '') {
            throw new RuntimeException('Username, full name, email, and password are required.');
        }

        PasswordPolicy::assert($password);

        if ($this->users->usernameExists($username)) {
            throw new RuntimeException('This username is already in use.');
        }

        if ($this->users->emailExists($email)) {
            throw new RuntimeException('This email address is already in use.');
        }

        return $this->runInTransaction(function () use ($input, $actor, $userType, $roleIds, $username, $fullName, $email, $password): int {
            $roleCatalog = $this->indexedRoles($userType === 'PORTAL');
            $validatedRoleIds = $this->validateRoleSelection($roleIds, $roleCatalog, $userType, $actor);
            $clientContactId = $this->resolveClientContactId($input, $userType);
            $normalizedUsername = $this->normalizeUsername($username, $userType, $clientContactId);

            if ($this->users->usernameExists($normalizedUsername)) {
                throw new RuntimeException('This username is already in use.');
            }

            $userId = $this->users->create([
                'employee_code' => $userType === 'INTERNAL' ? (trim((string) ($input['employee_code'] ?? '')) ?: null) : null,
                'client_contact_id' => $clientContactId,
                'username' => $normalizedUsername,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'must_change_password' => 1,
            ]);

            foreach ($validatedRoleIds as $roleId) {
                $this->users->assignRole($userId, $roleId, $actor['id'] ?? null);
            }

            $this->users->recordActivity($actor['id'] ?? null, 'CREATE', $userId, 'User created: ' . $normalizedUsername);

            return $userId;
        });
    }

    public function update(int $userId, array $input, array $actor): void
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $userType = $this->inferUserType($user);
        $this->assertActorCanManage($actor, $userType);

        $roleIds = array_map('intval', (array) ($input['role_ids'] ?? []));
        $username = trim((string) ($input['username'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));

        if ($username === '' || $fullName === '' || $email === '') {
            throw new RuntimeException('Username, full name, and email are required.');
        }

        if ($this->users->usernameExists($username, $userId)) {
            throw new RuntimeException('This username is already in use.');
        }

        if ($this->users->emailExists($email, $userId)) {
            throw new RuntimeException('This email address is already in use.');
        }

        $this->runInTransaction(function () use ($userId, $userType, $input, $actor, $roleIds, $username, $fullName, $email): void {
            $roleCatalog = $this->indexedRoles($userType === 'PORTAL');
            $validatedRoleIds = $this->validateRoleSelection($roleIds, $roleCatalog, $userType, $actor);
            $clientContactId = $this->resolveClientContactId($input, $userType);
            $normalizedUsername = $this->normalizeUsername($username, $userType, $clientContactId);

            if ($this->users->usernameExists($normalizedUsername, $userId)) {
                throw new RuntimeException('This username is already in use.');
            }

            $this->users->update($userId, [
                'employee_code' => $userType === 'INTERNAL' ? (trim((string) ($input['employee_code'] ?? '')) ?: null) : null,
                'client_contact_id' => $clientContactId,
                'username' => $normalizedUsername,
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'must_change_password' => !empty($input['must_change_password']) ? 1 : 0,
            ]);

            $this->users->clearRoles($userId);
            foreach ($validatedRoleIds as $roleId) {
                $this->users->assignRole($userId, $roleId, $actor['id'] ?? null);
            }

            $this->users->recordActivity($actor['id'] ?? null, 'UPDATE', $userId, 'User updated: ' . $normalizedUsername);
        });
    }

    public function createPortalUserForClientContact(
        int $clientContactId,
        string $usernameBasis,
        string $password,
        string $fullName,
        string $email,
        ?string $mobile = null,
        ?int $actorId = null
    ): array {
        $fullName = trim($fullName);
        $email = strtolower(trim($email));

        if ($fullName === '' || $email === '' || trim($password) === '') {
            throw new RuntimeException('Portal full name, email, and password are required.');
        }

        PasswordPolicy::assert($password);
        $normalizedUsername = $this->portalUsernameFromBasis($clientContactId, $usernameBasis);

        if ($this->users->usernameExists($normalizedUsername)) {
            throw new RuntimeException('A portal user already exists for the selected PAN, TAN, or Aadhaar.');
        }

        if ($this->users->emailExists($email)) {
            throw new RuntimeException('This email address is already in use.');
        }

        $clientRole = $this->clientPortalRoleId();
        $userId = $this->users->create([
            'employee_code' => null,
            'client_contact_id' => $clientContactId,
            'username' => $normalizedUsername,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'email' => $email,
            'mobile' => trim((string) $mobile) ?: null,
            'must_change_password' => 1,
        ]);
        $this->users->assignRole($userId, $clientRole, $actorId);
        $this->users->recordActivity($actorId, 'CREATE', $userId, 'Portal user created: ' . $normalizedUsername);

        return [
            'user_id' => $userId,
            'username' => $normalizedUsername,
        ];
    }

    public function archive(int $userId, array $actor): void
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $this->assertActorCanManage($actor, $this->inferUserType($user));

        $this->users->setActiveState($userId, false);
        $this->users->recordActivity($actor['id'] ?? null, 'ARCHIVE', $userId, 'User archived: ' . $user['username']);
    }

    public function activate(int $userId, array $actor): void
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $this->assertActorCanManage($actor, $this->inferUserType($user));

        $this->users->setActiveState($userId, true);
        $this->users->recordActivity($actor['id'] ?? null, 'ACTIVATE', $userId, 'User activated: ' . $user['username']);
    }

    public function resetPassword(int $userId, string $newPassword, array $actor): void
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $this->assertActorCanManage($actor, $this->inferUserType($user));

        $newPassword = trim($newPassword);
        PasswordPolicy::assert($newPassword);

        $this->users->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        $this->users->recordActivity($actor['id'] ?? null, 'RESET_PASSWORD', $userId, 'Password reset for user: ' . $user['username']);
    }

    public function changeOwnPassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        if (!password_verify($currentPassword, (string) ($user['password_hash'] ?? ''))) {
            throw new RuntimeException('Current password is incorrect.');
        }

        $newPassword = trim($newPassword);
        PasswordPolicy::assert($newPassword);

        if (password_verify($newPassword, (string) ($user['password_hash'] ?? ''))) {
            throw new RuntimeException('New password must be different from the current password.');
        }

        $this->users->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT), false);
        $this->users->recordActivity($userId, 'CHANGE_PASSWORD', $userId, 'User completed first-login password change.');
    }

    public function rightsCatalogForUser(int $userId): array
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $roleCodes = $this->users->rolePermissionCodes($userId);
        $directCodes = $this->users->directGrantedPermissionCodes($userId);
        $effectiveCodes = $this->users->effectivePermissionCodes($userId);

        $grouped = [];
        foreach ($this->users->permissionCatalog() as $permission) {
            $moduleCode = (string) ($permission['module_code'] ?? 'GENERAL');
            $code = (string) ($permission['code'] ?? '');
            $grouped[$moduleCode][] = [
                'id' => (int) $permission['id'],
                'code' => $code,
                'label' => (string) ($permission['label'] ?? $code),
                'description' => (string) ($permission['description'] ?? ''),
                'inherited' => in_array($code, $roleCodes, true),
                'direct' => in_array($code, $directCodes, true),
                'effective' => in_array($code, $effectiveCodes, true),
            ];
        }

        ksort($grouped);

        return [
            'user' => $user,
            'groups' => $grouped,
            'role_codes' => $roleCodes,
            'direct_codes' => $directCodes,
            'effective_codes' => $effectiveCodes,
        ];
    }

    public function updateGrantedRights(int $userId, array $permissionCodes, array $actor): void
    {
        if (!in_array('users.manage.rights', $actor['permissions'] ?? [], true)) {
            throw new RuntimeException('Only Super Admin can manage user rights.');
        }

        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $catalog = [];
        foreach ($this->users->permissionCatalog() as $permission) {
            $catalog[(string) $permission['code']] = (int) $permission['id'];
        }

        $normalizedCodes = [];
        foreach ($permissionCodes as $permissionCode) {
            $code = trim((string) $permissionCode);
            if ($code === '' || !isset($catalog[$code])) {
                continue;
            }

            $normalizedCodes[$code] = true;
        }

        $normalizedCodes = array_keys($normalizedCodes);
        sort($normalizedCodes);

        $this->runInTransaction(function () use ($userId, $actor, $normalizedCodes, $catalog): void {
            $this->users->clearUserPermissions($userId);
            foreach ($normalizedCodes as $code) {
                $this->users->grantUserPermission($userId, $catalog[$code], $actor['id'] ?? null, 'Assigned from rights control panel');
            }
        });

        $this->users->recordActivity(
            $actor['id'] ?? null,
            'RIGHTS_UPDATE',
            $userId,
            'User rights updated. Direct grants: ' . ($normalizedCodes === [] ? 'none' : implode(', ', $normalizedCodes))
        );
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

    private function indexedRoles(bool $portalOnly): array
    {
        $indexed = [];
        foreach ($this->users->activeRoles($portalOnly) as $role) {
            $indexed[(int) $role['id']] = $role;
        }

        return $indexed;
    }

    private function validateRoleSelection(array $roleIds, array $roleCatalog, string $userType, array $actor): array
    {
        $roleIds = array_values(array_unique(array_filter($roleIds)));

        if ($roleIds === []) {
            throw new RuntimeException('At least one role must be selected.');
        }

        foreach ($roleIds as $roleId) {
            if (!isset($roleCatalog[$roleId])) {
                throw new RuntimeException('Invalid role selection.');
            }
        }

        if ($userType === 'PORTAL') {
            foreach ($roleIds as $roleId) {
                if (($roleCatalog[$roleId]['code'] ?? '') !== 'CLIENT') {
                    throw new RuntimeException('Client portal users can only be assigned the Client role.');
                }
            }
        }

        $this->assertActorCanManage($actor, $userType);

        return $roleIds;
    }

    private function resolveClientContactId(array $input, string $userType): ?int
    {
        $clientContactId = (int) ($input['client_contact_id'] ?? 0);

        if ($userType === 'PORTAL') {
            if ($clientContactId <= 0) {
                throw new RuntimeException('Client contact is required for a portal user.');
            }

            return $clientContactId;
        }

        return null;
    }

    private function normalizeUsername(string $username, string $userType, ?int $clientContactId): string
    {
        if ($userType !== 'PORTAL') {
            return strtolower($username);
        }

        if (($clientContactId ?? 0) <= 0) {
            throw new RuntimeException('Client contact is required for a portal user.');
        }

        return $this->validatePortalUsernameForContact($clientContactId, $username);
    }

    private function validatePortalUsernameForContact(int $clientContactId, string $username): string
    {
        $normalized = $this->normalizePortalIdentifier($username);
        $contact = $this->clients->findContactWithClientById($clientContactId);

        if ($contact === null) {
            throw new RuntimeException('Client contact is invalid for portal username assignment.');
        }

        $validOptions = $this->portalIdentifierOptions($contact);
        if (!in_array($normalized, $validOptions, true)) {
            throw new RuntimeException('Portal username must exactly match the client PAN, TAN, or Aadhaar.');
        }

        return $normalized;
    }

    private function portalUsernameFromBasis(int $clientContactId, string $usernameBasis): string
    {
        $contact = $this->clients->findContactWithClientById($clientContactId);
        if ($contact === null) {
            throw new RuntimeException('Client contact is invalid for portal username assignment.');
        }

        return match (strtoupper(trim($usernameBasis))) {
            'PAN' => $this->validatedPan((string) ($contact['pan'] ?? '')),
            'TAN' => $this->validatedTan((string) ($contact['tan'] ?? '')),
            'AADHAAR' => $this->validatedAadhaar(
                (string) ($this->encryption->decrypt(
                    (string) ($contact['aadhaar_ciphertext'] ?? ''),
                    (string) ($contact['aadhaar_iv'] ?? '')
                ) ?? '')
            ),
            default => throw new RuntimeException('Choose PAN, TAN, or Aadhaar for portal username assignment.'),
        };
    }

    private function portalIdentifierOptions(array $contact): array
    {
        $options = [];
        $pan = trim((string) ($contact['pan'] ?? ''));
        $tan = trim((string) ($contact['tan'] ?? ''));
        $aadhaar = (string) ($this->encryption->decrypt(
            (string) ($contact['aadhaar_ciphertext'] ?? ''),
            (string) ($contact['aadhaar_iv'] ?? '')
        ) ?? '');

        if ($pan !== '') {
            $options[] = $this->validatedPan($pan);
        }

        if ($tan !== '') {
            $options[] = $this->validatedTan($tan);
        }

        if ($aadhaar !== '') {
            $options[] = $this->validatedAadhaar($aadhaar);
        }

        return $options;
    }

    private function normalizePortalIdentifier(string $username): string
    {
        $trimmed = preg_replace('/\s+/', '', trim($username)) ?? '';
        if ($trimmed === '') {
            throw new RuntimeException('Portal username is required.');
        }

        $digitsOnly = preg_replace('/\D+/', '', $trimmed) ?? '';
        if (strlen($digitsOnly) === 12 && $digitsOnly === $trimmed) {
            return $this->validatedAadhaar($digitsOnly);
        }

        $upper = strtoupper($trimmed);
        if (preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $upper) === 1) {
            return $this->validatedPan($upper);
        }

        if (preg_match('/^[A-Z]{4}[0-9]{5}[A-Z]$/', $upper) === 1) {
            return $this->validatedTan($upper);
        }

        throw new RuntimeException('Portal username must be a valid PAN, TAN, or 12-digit Aadhaar number.');
    }

    private function validatedPan(string $pan): string
    {
        $normalized = strtoupper(trim($pan));
        if (preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $normalized) !== 1) {
            throw new RuntimeException('PAN must follow the standard format.');
        }

        return $normalized;
    }

    private function validatedTan(string $tan): string
    {
        $normalized = strtoupper(trim($tan));
        if (preg_match('/^[A-Z]{4}[0-9]{5}[A-Z]$/', $normalized) !== 1) {
            throw new RuntimeException('TAN must follow the standard format.');
        }

        return $normalized;
    }

    private function validatedAadhaar(string $aadhaar): string
    {
        $normalized = preg_replace('/\D+/', '', $aadhaar) ?? '';
        if (preg_match('/^[0-9]{12}$/', $normalized) !== 1) {
            throw new RuntimeException('Aadhaar must be exactly 12 digits.');
        }

        return $normalized;
    }

    private function clientPortalRoleId(): int
    {
        foreach ($this->users->activeRoles(true) as $role) {
            if (($role['code'] ?? '') === 'CLIENT') {
                return (int) $role['id'];
            }
        }

        throw new RuntimeException('Client portal role is not configured.');
    }

    private function assertActorCanManage(array $actor, string $userType): void
    {
        $permissions = $actor['permissions'] ?? [];

        if ($userType === 'PORTAL') {
            if (in_array('users.manage.portal', $permissions, true)) {
                return;
            }

            throw new RuntimeException('You are not allowed to manage client portal users.');
        }

        if (in_array('users.manage.internal', $permissions, true)) {
            return;
        }

        throw new RuntimeException('Only Admin or Super Admin can manage internal users.');
    }

    private function inferUserType(array $user): string
    {
        return !empty($user['client_contact_id']) ? 'PORTAL' : 'INTERNAL';
    }
}
