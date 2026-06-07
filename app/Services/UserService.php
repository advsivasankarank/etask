<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\UserRepository;
use RuntimeException;
use Throwable;

final class UserService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository()
    ) {
    }

    public function create(array $input, array $actor): int
    {
        $userType = strtoupper(trim((string) ($input['user_type'] ?? 'INTERNAL')));
        $roleIds = array_map('intval', (array) ($input['role_ids'] ?? []));
        $username = strtolower(trim((string) ($input['username'] ?? '')));
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

            $userId = $this->users->create([
                'employee_code' => $userType === 'INTERNAL' ? (trim((string) ($input['employee_code'] ?? '')) ?: null) : null,
                'client_contact_id' => $clientContactId,
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'must_change_password' => 1,
            ]);

            foreach ($validatedRoleIds as $roleId) {
                $this->users->assignRole($userId, $roleId, $actor['id'] ?? null);
            }

            $this->users->recordActivity($actor['id'] ?? null, 'CREATE', $userId, 'User created: ' . $username);

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
        $username = strtolower(trim((string) ($input['username'] ?? '')));
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

            $this->users->update($userId, [
                'employee_code' => $userType === 'INTERNAL' ? (trim((string) ($input['employee_code'] ?? '')) ?: null) : null,
                'client_contact_id' => $clientContactId,
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'must_change_password' => !empty($input['must_change_password']) ? 1 : 0,
            ]);

            $this->users->clearRoles($userId);
            foreach ($validatedRoleIds as $roleId) {
                $this->users->assignRole($userId, $roleId, $actor['id'] ?? null);
            }

            $this->users->recordActivity($actor['id'] ?? null, 'UPDATE', $userId, 'User updated: ' . $username);
        });
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
