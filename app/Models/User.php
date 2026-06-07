<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public readonly string $actorType;

    public function __construct(
        public readonly int $id,
        public readonly ?int $clientContactId,
        public readonly ?int $clientId,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly string $fullName,
        public readonly string $email,
        public readonly bool $isActive,
        public readonly ?string $lockedUntil,
        public readonly bool $mustChangePassword,
        public readonly array $roles,
        public readonly array $permissions = []
    ) {
        $this->actorType = $this->resolveActorType();
    }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'client_contact_id' => $this->clientContactId,
            'client_id' => $this->clientId,
            'username' => $this->username,
            'full_name' => $this->fullName,
            'email' => $this->email,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'actor_type' => $this->actorType,
            'must_change_password' => $this->mustChangePassword,
        ];
    }

    private function resolveActorType(): string
    {
        if ($this->clientContactId !== null || $this->clientId !== null) {
            return 'PORTAL';
        }

        if (in_array('CONSULTANT', $this->roles, true)) {
            return 'CONSULTANT';
        }

        return 'INTERNAL';
    }
}
