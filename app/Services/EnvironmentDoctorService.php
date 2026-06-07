<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

final class EnvironmentDoctorService
{
    public function __construct(
        private readonly MigrationService $migrations = new MigrationService()
    ) {
    }

    public function report(): array
    {
        $checks = [
            $this->checkAppKey(),
            $this->checkDebugMode(),
            $this->checkBaseUrl(),
            $this->checkUploadLimit(),
            $this->checkStorageDirectory('storage/logs'),
            $this->checkStorageDirectory('storage/temp'),
            $this->checkStorageDirectory('storage/uploads'),
            $this->checkAbsoluteDirectory((string) config('app.private_storage_path', ''), 'PRIVATE_STORAGE_PATH'),
            $this->checkDatabase(),
            $this->checkMigrations(),
        ];

        $summary = [
            'ok' => 0,
            'warning' => 0,
            'error' => 0,
        ];

        foreach ($checks as $check) {
            $summary[strtolower($check['status'])]++;
        }

        return [
            'summary' => $summary,
            'checks' => $checks,
        ];
    }

    private function checkAppKey(): array
    {
        $configured = trim((string) config('app.encryption_key', '')) !== '';

        return [
            'name' => 'APP_KEY',
            'status' => $configured ? 'OK' : 'ERROR',
            'message' => $configured ? 'Application encryption key is configured.' : 'APP_KEY is missing.',
        ];
    }

    private function checkDebugMode(): array
    {
        $debug = (bool) config('app.debug', false);

        return [
            'name' => 'APP_DEBUG',
            'status' => $debug ? 'WARNING' : 'OK',
            'message' => $debug ? 'Debug mode is enabled. Disable this in production.' : 'Debug mode is disabled.',
        ];
    }

    private function checkBaseUrl(): array
    {
        $baseUrl = trim((string) config('app.url', ''));

        return [
            'name' => 'BASE_URL',
            'status' => $baseUrl === '' ? 'ERROR' : 'OK',
            'message' => $baseUrl === '' ? 'Application URL is not configured.' : 'Application URL is configured as ' . $baseUrl,
        ];
    }

    private function checkUploadLimit(): array
    {
        $bytes = (int) config('app.upload_max_bytes', 0);

        return [
            'name' => 'UPLOAD_MAX_BYTES',
            'status' => $bytes > 0 ? 'OK' : 'ERROR',
            'message' => $bytes > 0 ? 'Upload limit is set to ' . $bytes . ' bytes.' : 'Upload limit is invalid.',
        ];
    }

    private function checkStorageDirectory(string $relativePath): array
    {
        $absolutePath = base_path($relativePath);
        $exists = is_dir($absolutePath);
        $writable = $exists && is_writable($absolutePath);

        return [
            'name' => $relativePath,
            'status' => $exists && $writable ? 'OK' : 'ERROR',
            'message' => $exists && $writable
                ? $relativePath . ' exists and is writable.'
                : $relativePath . ' must exist and be writable.',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            Database::connection()->query('SELECT 1');

            return [
                'name' => 'DATABASE',
                'status' => 'OK',
                'message' => 'Database connection is healthy.',
            ];
        } catch (Throwable $throwable) {
            return [
                'name' => 'DATABASE',
                'status' => 'ERROR',
                'message' => 'Database connection failed: ' . $throwable->getMessage(),
            ];
        }
    }

    private function checkMigrations(): array
    {
        try {
            $pendingCount = $this->migrations->pendingCount();

            return [
                'name' => 'MIGRATIONS',
                'status' => $pendingCount === 0 ? 'OK' : 'WARNING',
                'message' => $pendingCount === 0
                    ? 'All tracked migrations are applied.'
                    : $pendingCount . ' migration(s) are still pending.',
            ];
        } catch (Throwable $throwable) {
            return [
                'name' => 'MIGRATIONS',
                'status' => 'ERROR',
                'message' => 'Migration status check failed: ' . $throwable->getMessage(),
            ];
        }
    }

    private function checkAbsoluteDirectory(string $absolutePath, string $name): array
    {
        $absolutePath = trim($absolutePath);
        $exists = $absolutePath !== '' && is_dir($absolutePath);
        $writable = $exists && is_writable($absolutePath);

        return [
            'name' => $name,
            'status' => $exists && $writable ? 'OK' : 'WARNING',
            'message' => $exists && $writable
                ? $name . ' exists and is writable at ' . $absolutePath
                : $name . ' will be created on first upload or must be writable at ' . $absolutePath,
        ];
    }
}
