<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ClientRepository;
use RuntimeException;
use Throwable;

final class AadhaarBackfillService
{
    private ClientRepository $clients;
    private EncryptionService $encryption;

    public function __construct()
    {
        $this->clients = new ClientRepository();
        $this->encryption = new EncryptionService();
    }

    public function preview(int $limit = 20): array
    {
        $limit = max(1, $limit);
        $this->assertMigrationReady();

        return [
            'legacy_count' => $this->clients->countLegacyPlaintextAadhaar(),
            'sample' => $this->sanitizeRows($this->clients->legacyPlaintextAadhaarRows($limit)),
        ];
    }

    public function execute(int $limit = 100): array
    {
        $limit = max(1, $limit);
        $this->assertMigrationReady();
        $rows = $this->clients->legacyPlaintextAadhaarRows($limit);
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $processed = 0;

            foreach ($rows as $row) {
                $aadhaar = preg_replace('/\D+/', '', (string) ($row['aadhaar_no'] ?? ''));
                if ($aadhaar === '' || strlen($aadhaar) !== 12) {
                    continue;
                }

                $encrypted = $this->encryption->encrypt($aadhaar);
                if ($encrypted === null) {
                    continue;
                }

                $this->clients->backfillEncryptedAadhaar(
                    (int) $row['id'],
                    (string) $encrypted['ciphertext'],
                    (string) $encrypted['iv'],
                    substr($aadhaar, -4)
                );
                $processed++;
            }

            $connection->commit();

            Logger::info('security.aadhaar_backfill_completed', [
                'processed' => $processed,
                'requested_limit' => $limit,
                'remaining_legacy_count' => $this->clients->countLegacyPlaintextAadhaar(),
            ]);

            return [
                'processed' => $processed,
                'remaining' => $this->clients->countLegacyPlaintextAadhaar(),
            ];
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            Logger::error('security.aadhaar_backfill_failed', [
                'requested_limit' => $limit,
                'message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    private function sanitizeRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            $aadhaar = preg_replace('/\D+/', '', (string) ($row['aadhaar_no'] ?? ''));

            return [
                'id' => (int) $row['id'],
                'client_code' => (string) ($row['client_code'] ?? ''),
                'legal_name' => (string) ($row['legal_name'] ?? ''),
                'aadhaar_masked' => $aadhaar !== '' ? 'XXXXXXXX' . substr($aadhaar, -4) : '-',
                'aadhaar_last4' => (string) ($row['aadhaar_last4'] ?? ''),
            ];
        }, $rows);
    }

    private function assertMigrationReady(): void
    {
        if ($this->clients->hasEncryptedAadhaarColumns()) {
            return;
        }

        throw new RuntimeException('Migration step-14-security-hardening.sql must be imported before Aadhaar backfill can run.');
    }
}
