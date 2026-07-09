<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Repositories\ClientRepository;
use App\Repositories\FinancialYearRepository;
use App\Repositories\PsoRepository;
use App\Repositories\ServiceTypeRepository;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class PsoService
{
    private PsoRepository $psos;
    private ServiceTypeRepository $serviceTypes;
    private ClientRepository $clients;
    private FinancialYearRepository $financialYears;
    private DocumentUploadService $documents;
    private ServiceOrderService $serviceOrders;

    public function __construct()
    {
        $this->psos = new PsoRepository();
        $this->serviceTypes = new ServiceTypeRepository();
        $this->clients = new ClientRepository();
        $this->financialYears = new FinancialYearRepository();
        $this->documents = new DocumentUploadService();
        $this->serviceOrders = new ServiceOrderService();
    }

    public function create(array $input, array $files, int $userId, ?int $clientId, ?int $clientContactId): int
    {
        if ($clientId === null || $clientContactId === null) {
            throw new RuntimeException('Client portal access requires a linked client contact.');
        }

        $title = trim((string) ($input['title'] ?? ''));
        $serviceTypeId = (int) ($input['service_type_id'] ?? 0);

        if ($title === '' || $serviceTypeId <= 0) {
            throw new RuntimeException('Title and service type are required.');
        }

        $serviceType = $this->serviceTypes->findWithDefaultCompany($serviceTypeId);
        if ($serviceType === null) {
            throw new RuntimeException('Invalid service type selected.');
        }

        $financialYear = $this->financialYears->current(new DateTimeImmutable());
        if ($financialYear === null) {
            throw new RuntimeException('No active financial year found for PSO creation.');
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $companyId = (int) ($serviceType['default_company_id'] ?? 0);
            if ($companyId <= 0) {
                throw new RuntimeException('No default company mapping exists for the selected service.');
            }

            $psoNo = $this->nextPsoNumber($companyId, (int) $financialYear['id']);
            $psoId = $this->psos->create([
                'pso_no' => $psoNo,
                'client_id' => $clientId,
                'company_id' => $companyId,
                'financial_year_id' => (int) $financialYear['id'],
                'service_type_id' => $serviceTypeId,
                'requested_for_period' => trim((string) ($input['requested_for_period'] ?? '')) ?: null,
                'title' => $title,
                'description' => trim((string) ($input['description'] ?? '')) ?: null,
                'requested_by_contact_id' => $clientContactId,
            ]);

            $documentIds = $this->documents->uploadForPso($clientId, $psoId, $files, $userId);
            foreach ($documentIds as $documentId) {
                $this->psos->attachDocument($psoId, $documentId);
            }

            $this->psos->addReview($psoId, 'SUBMITTED', 'PSO submitted by client portal user.', $userId);
            $this->psos->insertNotification($clientContactId, 'PSO Submitted', 'Your pre-service order has been submitted for CRM review.', $psoId);

            $connection->commit();
            return $psoId;
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    public function recommendApproval(int $psoId, int $userId, string $remarks): void
    {
        $this->psos->markRecommendedApproval($psoId, $userId, trim($remarks) !== '' ? $remarks : null);
    }

    public function approve(int $psoId, int $userId, string $remarks): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $pso = $this->psos->lockForUpdate($psoId);
            if ($pso === null) {
                throw new RuntimeException('PSO not found.');
            }

            if (in_array($pso['current_status'], ['REJECTED', 'CONVERTED_TO_SO'], true)) {
                throw new RuntimeException('This PSO cannot be approved from its current status.');
            }

            $serviceOrderId = $this->serviceOrders->createFromApprovedPso($psoId, $userId);
            $this->psos->approveAndConvert($psoId, $userId, $serviceOrderId, trim($remarks) !== '' ? $remarks : null);
            $this->psos->insertNotification(
                isset($pso['requested_by_contact_id']) ? (int) $pso['requested_by_contact_id'] : null,
                'PSO Approved',
                'Your PSO has been approved and converted into a service order.',
                $psoId
            );

            $connection->commit();
            return $serviceOrderId;
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    public function reject(int $psoId, int $userId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Rejection reason is required.');
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $pso = $this->psos->lockForUpdate($psoId);
            if ($pso === null) {
                throw new RuntimeException('PSO not found.');
            }

            $this->psos->reject($psoId, $userId, $reason);
            $this->psos->insertNotification(
                isset($pso['requested_by_contact_id']) ? (int) $pso['requested_by_contact_id'] : null,
                'PSO Rejected',
                'Your PSO has been rejected by Admin. Reason: ' . $reason,
                $psoId
            );

            $connection->commit();
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    private function nextPsoNumber(int $companyId, int $financialYearId): string
    {
        $connection = Database::connection();
        $insert = $connection->prepare(
            "INSERT IGNORE INTO numbering_sequences (company_id, financial_year_id, sequence_type, last_number, updated_at)
             VALUES (:company_id, :financial_year_id, 'PSO', 0, NOW())"
        );
        $insert->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
        ]);

        $select = $connection->prepare(
            "SELECT ns.id, ns.last_number, c.code AS company_code, fy.code AS fy_code
             FROM numbering_sequences ns
             INNER JOIN companies c ON c.id = ns.company_id
             INNER JOIN financial_years fy ON fy.id = ns.financial_year_id
             WHERE ns.company_id = :company_id
               AND ns.financial_year_id = :financial_year_id
               AND ns.sequence_type = 'PSO'
             LIMIT 1
             FOR UPDATE"
        );
        $select->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
        ]);

        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new RuntimeException('Unable to generate PSO number.');
        }

        $next = (int) $row['last_number'] + 1;
        $update = $connection->prepare(
            "UPDATE numbering_sequences
             SET last_number = :last_number, updated_at = NOW()
             WHERE id = :id"
        );
        $update->execute([
            'last_number' => $next,
            'id' => (int) $row['id'],
        ]);

        return sprintf('PSO/%s/%s/%04d', $row['company_code'], $row['fy_code'], $next);
    }
}
