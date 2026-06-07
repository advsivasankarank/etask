<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CompanyRepository
{
    public function allActive(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, code, display_name
             FROM companies
             WHERE is_active = 1
             ORDER BY display_name ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $companyId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, code, display_name
             FROM companies
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $companyId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }
}
