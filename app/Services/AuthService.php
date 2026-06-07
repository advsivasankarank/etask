<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Models\User;
use App\Repositories\UserRepository;
use DateTimeImmutable;
use PDOException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository()
    ) {
    }

    public function attempt(string $username, string $password, Request $request): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            $this->recordFailedLoginAttempt($username, $request, 'Username and password are required.');
            return ['success' => false, 'message' => 'Username and password are required.'];
        }

        $user = $this->users->findByUsername($username);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            $this->users->incrementFailedLogin($username);
            $this->recordFailedLoginAttempt($username, $request, 'Invalid login credentials.');
            return ['success' => false, 'message' => 'Invalid login credentials.'];
        }

        if (!$user->isActive) {
            $this->recordFailedLoginAttempt($username, $request, 'Inactive account login attempt.');
            return ['success' => false, 'message' => 'Your account is inactive. Please contact the administrator.'];
        }

        if ($this->isLocked($user)) {
            $this->recordFailedLoginAttempt($username, $request, 'Locked account login attempt.');
            return ['success' => false, 'message' => 'Your account is temporarily locked. Please try again later.'];
        }

        $this->users->updateSuccessfulLogin($user->id);
        Auth::login($user->toSessionArray());
        $this->recordLogin($user->id, $request);

        return ['success' => true, 'message' => 'Login successful.'];
    }

    public function recordLogin(int $userId, Request $request): void
    {
        try {
            $statement = Database::connection()->prepare(
                "INSERT INTO activity_logs (user_id, module_code, action_code, entity_type, entity_id, description, ip_address, user_agent, created_at)
                 VALUES (:user_id, 'AUTH', 'LOGIN', 'users', :entity_id, 'User logged in', :ip_address, :user_agent, NOW())"
            );
            $statement->execute([
                'user_id' => $userId,
                'entity_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (PDOException) {
        }

        Logger::info('auth.login', [
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function recordLogout(int $userId): void
    {
        try {
            $statement = Database::connection()->prepare(
                "INSERT INTO activity_logs (user_id, module_code, action_code, entity_type, entity_id, description, created_at)
                 VALUES (:user_id, 'AUTH', 'LOGOUT', 'users', :entity_id, 'User logged out', NOW())"
            );
            $statement->execute([
                'user_id' => $userId,
                'entity_id' => $userId,
            ]);
        } catch (PDOException) {
        }

        Logger::info('auth.logout', [
            'user_id' => $userId,
        ]);
    }

    public function recordFailedLoginAttempt(string $username, Request $request, string $description): void
    {
        try {
            $statement = Database::connection()->prepare(
                "INSERT INTO activity_logs (user_id, module_code, action_code, entity_type, entity_id, description, ip_address, user_agent, created_at)
                 VALUES (NULL, 'AUTH', 'LOGIN_FAILED', 'users', NULL, :description, :ip_address, :user_agent, NOW())"
            );
            $statement->execute([
                'description' => $description . ' Username: ' . trim($username),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (PDOException) {
        }

        Logger::warning('auth.login_failed', [
            'username' => trim($username),
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function isLocked(User $user): bool
    {
        if ($user->lockedUntil === null) {
            return false;
        }

        return new DateTimeImmutable($user->lockedUntil) > new DateTimeImmutable();
    }
}
