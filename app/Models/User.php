<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public string $actorType;

    public function __construct(
        public int $id,
        public ?int $clientContactId,
        public ?int $clientId,
        public string $username,
        public string $passwordHash,
        public string $fullName,
        public string $email,
        public bool $isActive,
        public ?string $lockedUntil,
        public bool $mustChangePassword,
        public array $roles,
        public array $permissions = []
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
