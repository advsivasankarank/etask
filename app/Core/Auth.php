<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;

final class Auth
{
    private const ACTOR_TYPE_PORTAL = 'PORTAL';
    private const ACTOR_TYPE_CONSULTANT = 'CONSULTANT';
    private const ACTOR_TYPE_INTERNAL = 'INTERNAL';

    public static function user(): ?array
    {
        return Session::get('auth_user');
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function clientId(): ?int
    {
        return self::user()['client_id'] ?? null;
    }

    public static function actorType(): string
    {
        $user = self::user();
        if ($user === null) {
            return self::ACTOR_TYPE_INTERNAL;
        }

        $actorType = strtoupper((string) ($user['actor_type'] ?? ''));
        return in_array($actorType, [self::ACTOR_TYPE_PORTAL, self::ACTOR_TYPE_CONSULTANT, self::ACTOR_TYPE_INTERNAL], true)
            ? $actorType
            : self::ACTOR_TYPE_INTERNAL;
    }

    public static function isPortalUser(): bool
    {
        return self::actorType() === self::ACTOR_TYPE_PORTAL;
    }

    public static function isConsultantUser(): bool
    {
        return self::actorType() === self::ACTOR_TYPE_CONSULTANT;
    }

    public static function hasRole(string ...$roles): bool
    {
        foreach ($roles as $role) {
            $normalizedRole = strtoupper(trim($role));
            if ($normalizedRole === 'CLIENT' && self::isPortalUser()) {
                return true;
            }

            if ($normalizedRole === 'CONSULTANT' && self::isConsultantUser()) {
                return true;
            }
        }

        return false;
    }

    public static function permissions(): array
    {
        $user = self::user();

        if ($user === null) {
            return [];
        }

        return $user['permissions'] ?? [];
    }

    public static function can(string $permission): bool
    {
        return $permission !== '' && in_array($permission, self::permissions(), true);
    }

    public static function canAny(string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function canAll(string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }

        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('auth_user', $user);
    }

    public static function logout(): void
    {
        $userId = self::id();
        Session::forget('auth_user');
        Session::regenerate();

        if ($userId !== null) {
            (new AuthService())->recordLogout($userId);
        }
    }
}
