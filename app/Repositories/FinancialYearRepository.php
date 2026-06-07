<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use DateTimeInterface;
use PDO;

final class FinancialYearRepository
{
    public function current(DateTimeInterface $date): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, code, label, start_date, end_date
             FROM financial_years
             WHERE :current_date BETWEEN start_date AND end_date
             LIMIT 1"
        );
        $statement->execute(['current_date' => $date->format('Y-m-d')]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, code, label, start_date, end_date
             FROM financial_years
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }
}
