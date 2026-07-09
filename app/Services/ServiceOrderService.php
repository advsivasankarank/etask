<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\FinancialYearRepository;
use App\Repositories\PsoRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\WorkflowRepository;
use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class ServiceOrderService
{
    private ClientRepository $clients;
    private CompanyRepository $companies;
    private FinancialYearRepository $financialYears;
    private PsoRepository $psos;
    private ServiceTypeRepository $serviceTypes;
    private WorkflowRepository $workflows;
    private ServiceOrderRepository $serviceOrders;

    public function __construct()
    {
        $this->clients = new ClientRepository();
        $this->companies = new CompanyRepository();
        $this->financialYears = new FinancialYearRepository();
        $this->psos = new PsoRepository();
        $this->serviceTypes = new ServiceTypeRepository();
        $this->workflows = new WorkflowRepository();
        $this->serviceOrders = new ServiceOrderRepository();
    }

    public function create(array $input, int $createdBy): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $clientId = (int) ($input['client_id'] ?? 0);
            $serviceTypeId = (int) ($input['service_type_id'] ?? 0);

            $client = $this->clients->findById($clientId);
            if ($client === null) {
                throw new RuntimeException('Selected client was not found.');
            }

            $serviceType = $this->serviceTypes->findWithDefaultCompany($serviceTypeId);
            if ($serviceType === null) {
                throw new RuntimeException('Selected service type was not found.');
            }

            $companyId = (int) ($input['company_id'] ?? 0);
            if ($companyId <= 0) {
                $companyId = (int) ($serviceType['default_company_id'] ?? 0);
            }

            $company = $this->companies->findById($companyId);
            if ($company === null) {
                throw new RuntimeException('A mapped company is required for the selected service type.');
            }

            $financialYear = $this->financialYears->current(new DateTimeImmutable());
            if ($financialYear === null) {
                throw new RuntimeException('No financial year is configured for the current date.');
            }

            $workflow = $this->workflows->activeByServiceType($serviceTypeId);
            if ($workflow === null) {
                throw new RuntimeException('No active workflow is configured for the selected service type.');
            }

            $period = $this->resolvePeriodMetadata($input, $serviceType, $financialYear);
            $itrCase = $this->resolveItrCaseMetadata($input, $serviceType);

            $serviceOrderId = $this->createResolvedServiceOrder(
                clientId: $clientId,
                company: $company,
                financialYear: $financialYear,
                serviceType: $serviceType,
                workflow: $workflow,
                period: $period,
                itrCase: $itrCase,
                title: trim((string) ($input['title'] ?? '')),
                description: trim((string) ($input['description'] ?? '')) ?: null,
                priorityLevel: (string) ($input['priority_level'] ?? 'MEDIUM'),
                createdBy: $createdBy,
                preServiceOrderId: null,
                assignedCrmId: $this->nullableInt($input['assigned_crm_id'] ?? null),
                assignedAssistantCrmId: $this->nullableInt($input['assigned_assistant_crm_id'] ?? null),
                assignedBackendId: $this->nullableInt($input['assigned_backend_id'] ?? null),
                assignedDeoId: $this->nullableInt($input['assigned_deo_id'] ?? null)
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

    public function createFromApprovedPso(int $psoId, int $createdBy): int
    {
        $pso = $this->psos->findById($psoId);
        if ($pso === null) {
            throw new RuntimeException('Approved PSO could not be found for conversion.');
        }

        $company = $this->companies->findById((int) $pso['company_id']);
        $financialYear = $this->financialYears->findById((int) $pso['financial_year_id']);
        $serviceType = $this->serviceTypes->findWithDefaultCompany((int) $pso['service_type_id']);
        $workflow = $this->workflows->activeByServiceType((int) $pso['service_type_id']);

        if ($company === null || $financialYear === null || $serviceType === null || $workflow === null) {
            throw new RuntimeException('PSO conversion failed because required mapping data is missing.');
        }

        return $this->createResolvedServiceOrder(
            clientId: (int) $pso['client_id'],
            company: $company,
            financialYear: $financialYear,
            serviceType: $serviceType,
            workflow: $workflow,
            period: [
                'work_basis' => 'ANNUAL',
                'compliance_subtype' => null,
                'assessment_year' => null,
                'period_month' => null,
                'period_quarter' => null,
                'period_year' => null,
                'period_label' => (string) ($pso['requested_for_period'] ?? ''),
            ],
            itrCase: [
                'itr_case_nature' => (string) ($serviceType['code'] ?? '') === 'ITR' ? 'NON_BUSINESS' : null,
                'itr_tax_audit_applicable' => (string) ($serviceType['code'] ?? '') === 'ITR' ? 0 : null,
            ],
            title: (string) $pso['title'],
            description: (string) ($pso['description'] ?? ''),
            priorityLevel: 'MEDIUM',
            createdBy: $createdBy,
            preServiceOrderId: $psoId,
            assignedCrmId: null,
            assignedAssistantCrmId: null,
            assignedBackendId: null,
            assignedDeoId: null
        );
    }

    private function nextSequenceNumber(int $companyId, int $financialYearId): int
    {
        $connection = Database::connection();

        $insert = $connection->prepare(
            "INSERT IGNORE INTO numbering_sequences (company_id, financial_year_id, sequence_type, last_number, updated_at)
             VALUES (:company_id, :financial_year_id, 'SO', 0, NOW())"
        );
        $insert->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
        ]);

        $select = $connection->prepare(
            "SELECT id, last_number
             FROM numbering_sequences
             WHERE company_id = :company_id
               AND financial_year_id = :financial_year_id
               AND sequence_type = 'SO'
             LIMIT 1
             FOR UPDATE"
        );
        $select->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
        ]);

        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new RuntimeException('Unable to initialize the SO numbering sequence.');
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

        return $next;
    }

    private function nullableInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private function createResolvedServiceOrder(
        int $clientId,
        array $company,
        array $financialYear,
        array $serviceType,
        array $workflow,
        array $period,
        array $itrCase,
        string $title,
        ?string $description,
        string $priorityLevel,
        int $createdBy,
        ?int $preServiceOrderId,
        ?int $assignedCrmId,
        ?int $assignedAssistantCrmId,
        ?int $assignedBackendId,
        ?int $assignedDeoId
    ): int {
        $sequenceNumber = $this->nextSequenceNumber((int) $company['id'], (int) $financialYear['id']);
        $soNo = sprintf('SO/%s/%s/%04d', $company['code'], $financialYear['code'], $sequenceNumber);

        $slaDueAt = (new DateTimeImmutable())
            ->add(new DateInterval('P' . (int) $serviceType['default_sla_days'] . 'D'))
            ->format('Y-m-d H:i:s');

        $serviceOrderId = $this->serviceOrders->create([
            'so_no' => $soNo,
            'client_id' => $clientId,
            'company_id' => (int) $company['id'],
            'financial_year_id' => (int) $financialYear['id'],
            'pre_service_order_id' => $preServiceOrderId,
            'service_type_id' => (int) $serviceType['id'],
            'workflow_definition_id' => (int) $workflow['id'],
            'work_basis' => $period['work_basis'],
            'compliance_subtype' => $period['compliance_subtype'],
            'assessment_year' => $period['assessment_year'],
            'itr_case_nature' => $itrCase['itr_case_nature'],
            'itr_tax_audit_applicable' => $itrCase['itr_tax_audit_applicable'],
            'period_month' => $period['period_month'],
            'period_quarter' => $period['period_quarter'],
            'period_year' => $period['period_year'],
            'period_label' => $period['period_label'],
            'title' => $title,
            'description' => $description,
            'priority_level' => $priorityLevel,
            'assigned_crm_id' => $assignedCrmId,
            'assigned_assistant_crm_id' => $assignedAssistantCrmId,
            'assigned_backend_id' => $assignedBackendId,
            'assigned_deo_id' => $assignedDeoId,
            'current_stage_code' => 'DOCUMENT_PENDING',
            'sla_due_at' => $slaDueAt,
            'created_by' => $createdBy,
        ]);

        $eVerificationDueDate = ((string) $serviceType['code'] === 'ITR')
            ? (new DateTimeImmutable())->add(new DateInterval('P30D'))->format('Y-m-d')
            : null;

        $this->serviceOrders->insertStatusFlags([
            'service_order_id' => $serviceOrderId,
            'is_document_pending' => 1,
            'is_payment_pending' => (int) $serviceType['requires_payment_stage'],
            'is_paid' => 0,
            'is_filing_done' => 0,
            'is_acknowledgement_captured' => 0,
            'is_e_verification_required' => (int) $serviceType['requires_e_verification'],
            'is_e_verification_done' => 0,
            'e_verification_due_date' => $eVerificationDueDate,
            'is_overdue' => 0,
            'is_client_paid' => 0,
            'is_consultant_payment_pending' => 0,
        ]);

        $this->serviceOrders->insertClosureRows($serviceOrderId);
        $this->serviceOrders->insertStageHistory($serviceOrderId, $createdBy, 'DOCUMENT_PENDING', 'Document Pending');
        $this->serviceOrders->insertTransitionLog($serviceOrderId, $createdBy, 'DOCUMENT_PENDING');
        $this->serviceOrders->recordActivity(
            $createdBy,
            $serviceOrderId,
            'SO_CREATE',
            'Service order created with immutable number ' . $soNo
        );
        Logger::info('service_order.created', [
            'service_order_id' => $serviceOrderId,
            'so_no' => $soNo,
            'client_id' => $clientId,
            'company_id' => (int) $company['id'],
            'service_type_id' => (int) $serviceType['id'],
            'created_by' => $createdBy,
        ]);

        return $serviceOrderId;
    }

    private function resolvePeriodMetadata(array $input, array $serviceType, array $financialYear): array
    {
        $serviceCode = (string) ($serviceType['code'] ?? '');
        $workBasis = strtoupper(trim((string) ($input['work_basis'] ?? '')));
        $complianceSubtype = strtoupper(trim((string) ($input['compliance_subtype'] ?? '')));
        $assessmentYear = strtoupper(trim((string) ($input['assessment_year'] ?? '')));
        $periodMonth = (int) ($input['period_month'] ?? 0);
        $periodQuarter = strtoupper(trim((string) ($input['period_quarter'] ?? '')));
        $periodYear = (int) ($input['period_year'] ?? 0);

        if ($serviceCode === 'ITR') {
            if ($assessmentYear === '') {
                throw new RuntimeException('Assessment Year is required for ITR service orders.');
            }

            return [
                'work_basis' => 'ANNUAL',
                'compliance_subtype' => 'ITR',
                'assessment_year' => $assessmentYear,
                'period_month' => null,
                'period_quarter' => null,
                'period_year' => null,
                'period_label' => 'AY ' . $assessmentYear,
            ];
        }

        if ($serviceCode === 'GST') {
            if ($complianceSubtype === '') {
                throw new RuntimeException('GST return type is required.');
            }

            if (in_array($complianceSubtype, ['GSTR9', 'GSTR9C'], true)) {
                $label = (string) ($financialYear['label'] ?? '');

                return [
                    'work_basis' => 'ANNUAL',
                    'compliance_subtype' => $complianceSubtype,
                    'assessment_year' => null,
                    'period_month' => null,
                    'period_quarter' => null,
                    'period_year' => null,
                    'period_label' => trim($complianceSubtype . ' ' . $label),
                ];
            }
        }

        if (!in_array($workBasis, ['ANNUAL', 'MONTHLY', 'QUARTERLY'], true)) {
            throw new RuntimeException('Work basis must be Annual, Monthly, or Quarterly.');
        }

        if ($workBasis === 'MONTHLY') {
            if ($periodMonth < 1 || $periodMonth > 12 || $periodYear < 2000) {
                throw new RuntimeException('Month and year are required for monthly service orders.');
            }

            return [
                'work_basis' => 'MONTHLY',
                'compliance_subtype' => $complianceSubtype !== '' ? $complianceSubtype : null,
                'assessment_year' => null,
                'period_month' => $periodMonth,
                'period_quarter' => null,
                'period_year' => $periodYear,
                'period_label' => date('F', mktime(0, 0, 0, $periodMonth, 1)) . ' ' . $periodYear,
            ];
        }

        if ($workBasis === 'QUARTERLY') {
            if (!in_array($periodQuarter, ['Q1', 'Q2', 'Q3', 'Q4'], true) || $periodYear < 2000) {
                throw new RuntimeException('Quarter and year are required for quarterly service orders.');
            }

            return [
                'work_basis' => 'QUARTERLY',
                'compliance_subtype' => $complianceSubtype !== '' ? $complianceSubtype : null,
                'assessment_year' => null,
                'period_month' => null,
                'period_quarter' => $periodQuarter,
                'period_year' => $periodYear,
                'period_label' => $periodQuarter . ' ' . $periodYear,
            ];
        }

        return [
            'work_basis' => 'ANNUAL',
            'compliance_subtype' => $complianceSubtype !== '' ? $complianceSubtype : null,
            'assessment_year' => null,
            'period_month' => null,
            'period_quarter' => null,
            'period_year' => null,
            'period_label' => (string) ($financialYear['label'] ?? 'Annual'),
        ];
    }

    private function resolveItrCaseMetadata(array $input, array $serviceType): array
    {
        $serviceCode = (string) ($serviceType['code'] ?? '');
        if ($serviceCode !== 'ITR') {
            return [
                'itr_case_nature' => null,
                'itr_tax_audit_applicable' => null,
            ];
        }

        $caseNature = strtoupper(trim((string) ($input['itr_case_nature'] ?? '')));
        if (!in_array($caseNature, ['BUSINESS', 'NON_BUSINESS'], true)) {
            throw new RuntimeException('Case type is required for ITR service orders.');
        }

        $taxAuditApplicable = null;
        if ($caseNature === 'BUSINESS') {
            $rawValue = strtoupper(trim((string) ($input['itr_tax_audit_applicable'] ?? '')));
            if (!in_array($rawValue, ['YES', 'NO'], true)) {
                throw new RuntimeException('Tax Audit Applicable must be selected for business ITR cases.');
            }
            $taxAuditApplicable = $rawValue === 'YES' ? 1 : 0;
        } else {
            $taxAuditApplicable = 0;
        }

        return [
            'itr_case_nature' => $caseNature,
            'itr_tax_audit_applicable' => $taxAuditApplicable,
        ];
    }
}
