<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;
use RuntimeException;
use Throwable;

final class MigrationService
{
    public function ensureMigrationTable(): void
    {
        Database::connection()->exec(
            "CREATE TABLE IF NOT EXISTS schema_migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(190) NOT NULL UNIQUE,
                file_name VARCHAR(255) NOT NULL,
                checksum_sha256 CHAR(64) NOT NULL,
                applied_at DATETIME NOT NULL,
                execution_ms INT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function status(): array
    {
        $this->ensureMigrationTable();

        $applied = $this->appliedMigrationsIndexed();
        $rows = [];

        foreach ($this->migrationFiles() as $filePath) {
            $fileName = basename($filePath);
            $checksum = hash_file('sha256', $filePath) ?: '';
            $appliedRow = $applied[$fileName] ?? null;

            $rows[] = [
                'file_name' => $fileName,
                'checksum_sha256' => $checksum,
                'status' => $appliedRow === null ? 'PENDING' : 'APPLIED',
                'applied_at' => $appliedRow['applied_at'] ?? null,
                'execution_ms' => isset($appliedRow['execution_ms']) ? (int) $appliedRow['execution_ms'] : null,
                'checksum_matches' => $appliedRow === null
                    ? null
                    : hash_equals((string) $appliedRow['checksum_sha256'], $checksum),
            ];
        }

        return $rows;
    }

    public function pendingCount(): int
    {
        return count(array_filter($this->status(), static fn (array $row): bool => $row['status'] === 'PENDING'));
    }

    public function migrate(): array
    {
        $this->ensureMigrationTable();

        $applied = $this->appliedMigrationsIndexed();
        $results = [];

        foreach ($this->migrationFiles() as $filePath) {
            $fileName = basename($filePath);
            if (isset($applied[$fileName])) {
                continue;
            }

            $results[] = $this->applyMigration($filePath);
        }

        return $results;
    }

    public function baselinePending(): array
    {
        $this->ensureMigrationTable();

        $applied = $this->appliedMigrationsIndexed();
        $results = [];

        foreach ($this->migrationFiles() as $filePath) {
            $fileName = basename($filePath);
            if (isset($applied[$fileName])) {
                continue;
            }

            $statement = Database::connection()->prepare(
                "INSERT INTO schema_migrations (
                    migration_name, file_name, checksum_sha256, applied_at, execution_ms
                ) VALUES (
                    :migration_name, :file_name, :checksum_sha256, NOW(), 0
                )"
            );
            $statement->execute([
                'migration_name' => pathinfo($filePath, PATHINFO_FILENAME),
                'file_name' => $fileName,
                'checksum_sha256' => hash_file('sha256', $filePath) ?: '',
            ]);

            Logger::warning('database.migration_baselined', [
                'file_name' => $fileName,
            ]);

            $results[] = [
                'file_name' => $fileName,
                'execution_ms' => 0,
            ];
        }

        return $results;
    }

    private function applyMigration(string $filePath): array
    {
        $sql = trim((string) file_get_contents($filePath));
        if ($sql === '') {
            throw new RuntimeException('Migration file is empty: ' . basename($filePath));
        }

        $connection = Database::connection();
        $start = microtime(true);

        try {
            $connection->exec($sql);

            $executionMs = (int) round((microtime(true) - $start) * 1000);
            $statement = $connection->prepare(
                "INSERT INTO schema_migrations (
                    migration_name, file_name, checksum_sha256, applied_at, execution_ms
                ) VALUES (
                    :migration_name, :file_name, :checksum_sha256, NOW(), :execution_ms
                )"
            );
            $statement->execute([
                'migration_name' => pathinfo($filePath, PATHINFO_FILENAME),
                'file_name' => basename($filePath),
                'checksum_sha256' => hash_file('sha256', $filePath) ?: '',
                'execution_ms' => $executionMs,
            ]);

            Logger::info('database.migration_applied', [
                'file_name' => basename($filePath),
                'execution_ms' => $executionMs,
            ]);

            return [
                'file_name' => basename($filePath),
                'execution_ms' => $executionMs,
            ];
        } catch (Throwable $throwable) {
            Logger::error('database.migration_failed', [
                'file_name' => basename($filePath),
                'message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    private function migrationFiles(): array
    {
        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files, SORT_NATURAL);

        return $files;
    }

    private function appliedMigrationsIndexed(): array
    {
        $statement = Database::connection()->query(
            "SELECT file_name, checksum_sha256, applied_at, execution_ms
             FROM schema_migrations
             ORDER BY id ASC"
        );

        $indexed = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $indexed[(string) $row['file_name']] = $row;
        }

        return $indexed;
    }
}
