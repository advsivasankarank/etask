<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class PasswordPolicy
{
    public static function assert(string $password): void
    {
        if (strlen($password) < 8) {
            throw new RuntimeException('Password must be at least 8 characters long.');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new RuntimeException('Password must include at least one uppercase letter.');
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new RuntimeException('Password must include at least one lowercase letter.');
        }

        if (!preg_match('/\d/', $password)) {
            throw new RuntimeException('Password must include at least one number.');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new RuntimeException('Password must include at least one special character.');
        }
    }
}
