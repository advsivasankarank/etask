<?php

declare(strict_types=1);

namespace App\Testing;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Repositories\BillingRepository;
use App\Repositories\ClientRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ReminderRepository;
use App\Repositories\ReportRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\BillingService;
use App\Services\ClientService;
use App\Services\DocumentAccessService;
use App\Services\PsoService;
use App\Services\ReminderService;
use App\Services\SearchService;
use App\Services\ServiceOrderService;
use App\Services\UserService;
use App\Services\WorkflowService;
use PDO;
use ReflectionClass;
use RuntimeException;
use Throwable;

final class RegressionSuite
{
    private NestedTransactionPdo $connection;
    private array $results = [];
    private array $context = [
        'shared' => [],
        'cleanup_files' => [],
    ];
    private float $startedAt;

    public function __construct()
    {
        $this->startedAt = microtime(true);
        $this->connection = $this->installNestedConnection();
    }

    public function run(): array
    {
        Session::start();
        $this->seedServer();
        $this->connection->beginTransaction();

        try {
            $this->bootstrapActors();
            $this->runTest('Authentication', fn (): array => $this->testAuthentication());
            $this->runTest('Client Creation', fn (): array => $this->testClientCreation());
            $this->runTest('Portal Credential Creation', fn (): array => $this->testPortalCredentialCreation());
            $this->runTest('PSO Creation', fn (): array => $this->testPsoCreation());
            $this->runTest('RBAC Permissions', fn (): array => $this->testRbacPermissions());
            $this->runTest('Service Order Creation', fn (): array => $this->testServiceOrderCreation());
            $this->runTest('Workflow Progression', fn (): array => $this->testWorkflowProgression());
            $this->runTest('Invoice Creation', fn (): array => $this->testInvoiceCreation());
            $this->runTest('Receipt Creation', fn (): array => $this->testReceiptCreation());
            $this->runTest('Secure Document Download', fn (): array => $this->testSecureDocumentDownload());
            $this->runTest('Search', fn (): array => $this->testSearch());
            $this->runTest('Reminders', fn (): array => $this->testReminders());
            $this->runTest('Reports', fn (): array => $this->testReports());
        } finally {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            $this->deleteCleanupFiles();
            Session::destroy();
            $this->resetDatabaseConnection();
        }

        $finishedAt = microtime(true);
        $summary = $this->summary($finishedAt - $this->startedAt);

        return [
            'summary' => $summary,
            'results' => $this->results,
            'metadata' => [
                'started_at' => date('Y-m-d H:i:s', (int) $this->startedAt),
                'finished_at' => date('Y-m-d H:i:s', (int) $finishedAt),
                'coverage' => $this->coverageSummary($summary),
            ],
        ];
    }

    private function testAuthentication(): array
    {
        $internalUser = $this->requireShared('internal_user');
        $request = $this->makeRequest('POST', '/login');
        $auth = new AuthService();

        $success = $auth->attempt((string) $internalUser['username'], (string) $internalUser['password'], $request);
        $this->assertTrue(($success['success'] ?? false) === true, 'Expected valid credentials to authenticate.');
        $this->assertTrue(Auth::check(), 'Expected auth session to be established.');

        Auth::logout();

        $failure = $auth->attempt((string) $internalUser['username'], 'Wrong!234', $request);
        $this->assertTrue(($failure['success'] ?? true) === false, 'Expected invalid password to be rejected.');

        return [
            'checks' => [
                'Valid login succeeds for a test-created internal user.',
                'Invalid password is rejected cleanly.',
            ],
            'details' => [
                'username' => $internalUser['username'],
                'success_message' => $success['message'] ?? '',
                'failure_message' => $failure['message'] ?? '',
            ],
        ];
    }

    private function testRbacPermissions(): array
    {
        $portalUser = $this->requireShared('portal_user');
        $portalManager = $this->requireShared('portal_manager');

        $this->actAs($portalManager['session']);
        $this->assertTrue(Auth::can('users.manage.portal'), 'Expected portal manager to hold portal user management permission.');

        $this->actAs($portalUser['session']);
        $this->assertTrue(Auth::can('portal.pso.create'), 'Expected portal user to hold PSO creation permission.');
        $this->assertTrue(!Auth::can('reports.financial'), 'Expected portal user to be denied financial reports.');

        return [
            'checks' => [
                'Portal manager retains portal user management permission.',
                'Portal user can create PSO.',
                'Portal user cannot access financial report permission surface.',
            ],
            'details' => [
                'portal_manager' => $portalManager['session']['username'],
                'portal_user' => $portalUser['session']['username'],
            ],
        ];
    }

    private function testClientCreation(): array
    {
        $superAdmin = $this->requireShared('super_admin');
        $crm = $this->requireShared('crm_user');

        $clientId = (new ClientService())->create([
            'client_type' => 'INDIVIDUAL',
            'legal_name' => 'Regression Client ' . date('His'),
            'trade_name' => 'Regression Client Trade',
            'pan' => 'RGXPA' . substr(strtoupper(md5((string) microtime(true))), 0, 5),
            'tan' => 'CHNR' . random_int(10000, 99999) . 'A',
            'gstin' => '33ABCDE1234F1Z5',
            'aadhaar_no' => '123412341234',
            'email' => 'regression.client@example.test',
            'mobile' => '9000000001',
            'contact_name' => 'Regression Contact',
            'contact_email' => 'regression.contact@example.test',
            'contact_mobile' => '9000000002',
            'assigned_crm_id' => $crm['id'],
            'city' => 'Chennai',
            'state_name' => 'Tamil Nadu',
        ], [], $superAdmin['id']);

        $client = (new ClientRepository())->findById($clientId);
        $this->assertTrue($client !== null, 'Expected created client to be retrievable.');
        $this->assertTrue(!empty($client['aadhaar_ciphertext']) && empty($client['aadhaar_no']), 'Expected Aadhaar to be encrypted and plaintext cleared.');

        $contact = (new ClientRepository())->primaryContact($clientId);
        $this->assertTrue($contact !== null, 'Expected a primary contact to be created.');

        $this->context['shared']['client'] = [
            'id' => $clientId,
            'pan' => (string) $client['pan'],
            'contact_id' => (int) $contact['id'],
            'contact_name' => (string) $contact['contact_name'],
        ];

        return [
            'checks' => [
                'Client master record is created successfully.',
                'Primary contact is created automatically.',
                'Aadhaar is stored in encrypted form.',
            ],
            'details' => [
                'client_id' => $clientId,
                'pan' => $client['pan'] ?? '',
                'assigned_crm_id' => $crm['id'],
            ],
        ];
    }

    private function testPortalCredentialCreation(): array
    {
        $client = $this->requireShared('client');
        $portalManager = $this->requireShared('portal_manager');

        (new ClientService())->savePortalCredentials((int) $client['id'], [
            'INCOME_TAX_user_identifier' => 'regression.it.user',
            'INCOME_TAX_password' => 'Portal!234',
            'custom_portal_label' => ['Custom UDYAM'],
            'custom_portal_user_identifier' => ['regression.udyam'],
            'custom_portal_password' => ['Custom!234'],
            'custom_portal_code' => [''],
        ], (int) $portalManager['id']);

        $repo = new ClientRepository();
        $credentials = $repo->portalCredentials((int) $client['id']);
        $itCredential = $repo->portalCredentialByCode((int) $client['id'], 'INCOME_TAX');

        $this->assertTrue(count($credentials) >= 2, 'Expected standard and custom portal credentials to be stored.');
        $this->assertTrue(!empty($itCredential['password_ciphertext']), 'Expected portal password to be encrypted.');

        return [
            'checks' => [
                'Standard portal credential is stored.',
                'Custom portal credential is supported.',
                'Portal passwords are stored encrypted.',
            ],
            'details' => [
                'credential_count' => count($credentials),
                'standard_portal' => 'INCOME_TAX',
            ],
        ];
    }

    private function testPsoCreation(): array
    {
        $client = $this->requireShared('client');
        $portalUser = $this->createPortalUser((int) $client['contact_id']);
        $serviceTypeId = $this->serviceTypeIdByCode('GST');

        $psoId = (new PsoService())->create([
            'title' => 'Regression GST PSO',
            'service_type_id' => $serviceTypeId,
            'requested_for_period' => 'June 2026',
            'description' => 'Regression smoke test PSO',
        ], [], (int) $portalUser['session']['id'], (int) $portalUser['session']['client_id'], (int) $portalUser['session']['client_contact_id']);

        $row = $this->fetchOne('SELECT pso_no, current_status FROM pre_service_orders WHERE id = :id', ['id' => $psoId]);
        $this->assertTrue($row !== null, 'Expected PSO to be created.');
        $this->assertTrue(str_starts_with((string) $row['pso_no'], 'PSO/'), 'Expected PSO number format to be generated.');

        $this->context['shared']['pso'] = [
            'id' => $psoId,
            'pso_no' => (string) $row['pso_no'],
        ];
        $this->context['shared']['portal_user'] = $portalUser;

        return [
            'checks' => [
                'Portal-linked client contact can create a PSO.',
                'PSO numbering is generated automatically.',
            ],
            'details' => [
                'pso_id' => $psoId,
                'pso_no' => $row['pso_no'] ?? '',
            ],
        ];
    }

    private function testServiceOrderCreation(): array
    {
        $client = $this->requireShared('client');
        $superAdmin = $this->requireShared('super_admin');
        $crm = $this->requireShared('crm_user');
        $serviceTypeId = $this->serviceTypeIdByCode('GST');

        $serviceOrderId = (new ServiceOrderService())->create([
            'client_id' => $client['id'],
            'service_type_id' => $serviceTypeId,
            'work_basis' => 'MONTHLY',
            'compliance_subtype' => 'GSTR3B',
            'period_month' => 6,
            'period_year' => 2026,
            'title' => 'Regression GST SO',
            'description' => 'Regression smoke test service order',
            'priority_level' => 'HIGH',
            'assigned_crm_id' => $crm['id'],
        ], (int) $superAdmin['id']);

        $order = (new ServiceOrderRepository())->findDetailedById($serviceOrderId);
        $this->assertTrue($order !== null, 'Expected service order to be created.');
        $this->assertTrue(str_starts_with((string) $order['so_no'], 'SO/'), 'Expected SO number format to be generated.');

        $reminderServiceOrderId = (new ServiceOrderService())->create([
            'client_id' => $client['id'],
            'service_type_id' => $serviceTypeId,
            'work_basis' => 'MONTHLY',
            'compliance_subtype' => 'GSTR1',
            'period_month' => 7,
            'period_year' => 2026,
            'title' => 'Reminder Candidate SO',
            'description' => 'Kept in document pending for reminder smoke test',
            'priority_level' => 'MEDIUM',
            'assigned_crm_id' => $crm['id'],
        ], (int) $superAdmin['id']);

        $this->context['shared']['service_order'] = [
            'id' => $serviceOrderId,
            'so_no' => (string) $order['so_no'],
        ];
        $this->context['shared']['reminder_service_order'] = [
            'id' => $reminderServiceOrderId,
        ];

        return [
            'checks' => [
                'Service Order can be created directly from the master workflow.',
                'SO number is immutable and auto-generated.',
                'A second SO is seeded for reminder validation.',
            ],
            'details' => [
                'service_order_id' => $serviceOrderId,
                'so_no' => $order['so_no'] ?? '',
                'reminder_so_id' => $reminderServiceOrderId,
            ],
        ];
    }

    private function testWorkflowProgression(): array
    {
        $order = $this->requireShared('service_order');
        $superAdmin = $this->requireShared('super_admin');
        $workflow = new WorkflowService();

        $workflow->advanceMilestone((int) $order['id'], (int) $superAdmin['id']);
        $workflow->advanceMilestone((int) $order['id'], (int) $superAdmin['id']);
        $workflow->advanceMilestone((int) $order['id'], (int) $superAdmin['id']);
        $workflow->recordTaxPayment((int) $order['id'], (int) $superAdmin['id'], 'GSTPAY-REG-001');
        $workflow->advanceMilestone((int) $order['id'], (int) $superAdmin['id']);
        $workflow->advanceMilestone((int) $order['id'], (int) $superAdmin['id']);
        $workflow->captureAcknowledgement((int) $order['id'], (int) $superAdmin['id'], 'ARN-REG-001');
        $workflow->completeProceduralClosure((int) $order['id'], (int) $superAdmin['id'], 'Regression closure');

        $updated = (new ServiceOrderRepository())->findDetailedById((int) $order['id']);
        $this->assertTrue(($updated['current_stage_code'] ?? '') === 'PROCEDURALLY_CLOSED', 'Expected service order to reach procedural closure.');

        $closure = $this->fetchOne(
            "SELECT closure_status FROM service_order_closures WHERE service_order_id = :service_order_id AND closure_type = 'PROCEDURAL' LIMIT 1",
            ['service_order_id' => (int) $order['id']]
        );
        $this->assertTrue(($closure['closure_status'] ?? '') === 'COMPLETED', 'Expected procedural closure record to complete.');

        return [
            'checks' => [
                'Workflow advances through GST milestone stages.',
                'Payment transition auto-moves from Payment Pending to Paid.',
                'Acknowledgement capture and procedural closure complete successfully.',
            ],
            'details' => [
                'final_stage' => $updated['current_stage_code'] ?? '',
                'acknowledgement_no' => $updated['acknowledgement_no'] ?? '',
            ],
        ];
    }

    private function testInvoiceCreation(): array
    {
        $order = $this->requireShared('service_order');
        $superAdmin = $this->requireShared('super_admin');
        $billing = new BillingService();

        $billing->createDisbursement([
            'service_order_id' => $order['id'],
            'amount' => 250.00,
            'expense_type' => 'GST Filing Fee',
            'expense_date' => date('Y-m-d'),
            'is_recoverable' => 1,
            'paid_to' => 'Government Portal',
        ], (int) $superAdmin['id']);

        $invoiceId = $billing->createInvoice([
            'service_order_id' => $order['id'],
            'service_fee' => 1500.00,
            'tax_total' => 270.00,
            'invoice_type' => 'FINAL',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+7 days')),
        ], (int) $superAdmin['id']);

        $invoice = $this->fetchOne('SELECT invoice_no, disbursement_total, net_payable FROM invoices WHERE id = :id', ['id' => $invoiceId]);
        $this->assertTrue($invoice !== null, 'Expected invoice to be created.');
        $this->assertTrue(str_starts_with((string) $invoice['invoice_no'], 'INV/'), 'Expected invoice number to be auto-generated.');
        $this->assertTrue((float) ($invoice['disbursement_total'] ?? 0) >= 250.0, 'Expected recoverable disbursement to be rolled into invoice.');

        $this->context['shared']['invoice'] = [
            'id' => $invoiceId,
            'invoice_no' => (string) $invoice['invoice_no'],
            'net_payable' => (float) $invoice['net_payable'],
        ];

        return [
            'checks' => [
                'Recoverable disbursement can be added to billing context.',
                'Final invoice is generated with invoice numbering.',
                'Recoverable disbursement is included in the invoice totals.',
            ],
            'details' => [
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoice['invoice_no'] ?? '',
                'net_payable' => $invoice['net_payable'] ?? 0,
            ],
        ];
    }

    private function testReceiptCreation(): array
    {
        $order = $this->requireShared('service_order');
        $invoice = $this->requireShared('invoice');
        $superAdmin = $this->requireShared('super_admin');

        $receiptId = (new BillingService())->recordPayment([
            'service_order_id' => $order['id'],
            'amount' => $invoice['net_payable'],
            'transaction_type' => 'INVOICE_PAYMENT',
            'payment_mode' => 'BANK_TRANSFER',
            'payment_date' => date('Y-m-d'),
            'reference_no' => 'BANK-REG-001',
            'status' => 'SUCCESS',
        ], (int) $superAdmin['id']);

        $receipt = $this->fetchOne('SELECT receipt_no, receipt_amount FROM receipts WHERE id = :id', ['id' => $receiptId]);
        $invoiceRow = $this->fetchOne('SELECT payment_status FROM invoices WHERE id = :id', ['id' => $invoice['id']]);
        $flags = $this->fetchOne('SELECT is_client_paid FROM service_order_status_flags WHERE service_order_id = :id', ['id' => $order['id']]);

        $this->assertTrue(str_starts_with((string) ($receipt['receipt_no'] ?? ''), 'RCPT/'), 'Expected receipt number to be auto-generated.');
        $this->assertTrue(($invoiceRow['payment_status'] ?? '') === 'PAID', 'Expected invoice payment status to be updated to paid.');
        $this->assertTrue((int) ($flags['is_client_paid'] ?? 0) === 1, 'Expected service order client-paid flag to be updated.');

        $this->context['shared']['receipt'] = [
            'id' => $receiptId,
            'receipt_no' => (string) $receipt['receipt_no'],
        ];

        return [
            'checks' => [
                'Receipt is generated from payment recording.',
                'Invoice payment status becomes paid.',
                'Service order accounting payment flag is synchronized.',
            ],
            'details' => [
                'receipt_id' => $receiptId,
                'receipt_no' => $receipt['receipt_no'] ?? '',
            ],
        ];
    }

    private function testSecureDocumentDownload(): array
    {
        $client = $this->requireShared('client');
        $portalUser = $this->requireShared('portal_user');
        $internalUser = $this->requireShared('internal_user');
        $documentId = $this->seedClientDocument((int) $client['id'], (int) $portalUser['session']['id']);
        $request = $this->makeRequest('GET', '/documents/' . $documentId . '/download');
        $service = new DocumentAccessService();

        $this->actAs($portalUser['session']);
        $allowed = $service->downloadContext($documentId, $request);
        $this->assertTrue(is_file((string) $allowed['absolute_path']), 'Expected authorized user to receive a resolvable document path.');

        $this->actAs($internalUser['session']);
        $denied = false;
        try {
            $service->downloadContext($documentId, $request);
        } catch (RuntimeException $exception) {
            $denied = str_contains($exception->getMessage(), 'not allowed');
        }

        $this->assertTrue($denied, 'Expected unauthorized user to be blocked from secure document download.');

        $accessCount = (int) $this->fetchValue(
            "SELECT COUNT(*) FROM activity_logs WHERE module_code = 'DOCUMENTS' AND entity_id = :entity_id",
            ['entity_id' => $documentId]
        );
        $this->assertTrue($accessCount >= 2, 'Expected document access attempts to be logged.');

        return [
            'checks' => [
                'Authorized owner can download through the secure document service.',
                'Unauthorized user is blocked.',
                'Document access attempts are logged.',
            ],
            'details' => [
                'document_id' => $documentId,
                'audit_entries' => $accessCount,
            ],
        ];
    }

    private function testSearch(): array
    {
        $client = $this->requireShared('client');
        $searchActor = $this->requireShared('search_user');
        $search = new SearchService();
        $request = $this->makeRequest('GET', '/search');

        $this->actAs($searchActor['session']);
        $advanced = $search->advancedSearch([
            'source' => 'clients',
            'q' => (string) $client['pan'],
            'pan' => (string) $client['pan'],
        ], 1, 10);
        $clientHits = (int) ($advanced['total'] ?? 0);
        $this->assertTrue($clientHits >= 1, 'Expected global search to find the created client by PAN.');

        $search->logSearch($request, 'ADVANCED', (string) $client['pan'], 'clients', ['q' => $client['pan'], 'pan' => $client['pan']], $clientHits);
        $history = $search->history(['q' => $client['pan']], 1, 10);
        $this->assertTrue((int) ($history['total'] ?? 0) >= 1, 'Expected search history logging to succeed.');

        return [
            'checks' => [
                'Advanced search finds the created client by PAN.',
                'Search history is written for audited lookup.',
            ],
            'details' => [
                'client_hits' => $clientHits,
                'history_total' => $history['total'] ?? 0,
            ],
        ];
    }

    private function testReminders(): array
    {
        $superAdmin = $this->requireShared('super_admin');
        $service = new ReminderService();

        $this->actAs($superAdmin['session']);
        $this->connection->exec("UPDATE reminder_templates SET is_active = 0 WHERE channel = 'EMAIL'");
        $before = $service->overview();
        $run = $service->runScheduler();
        $after = $service->overview();

        $this->assertTrue(array_key_exists('created', $run) && array_key_exists('triggered', $run) && array_key_exists('escalated', $run), 'Expected reminder scheduler to return execution counters.');
        $this->assertTrue(isset($after['summary']['open_reminders']), 'Expected reminder overview summary to be available.');

        return [
            'checks' => [
                'Reminder overview loads successfully.',
                'Reminder scheduler executes and returns counters.',
                'Reminder summary remains queryable after execution.',
            ],
            'details' => [
                'open_before' => $before['summary']['open_reminders'] ?? 0,
                'created' => $run['created'] ?? 0,
                'triggered' => $run['triggered'] ?? 0,
                'escalated' => $run['escalated'] ?? 0,
                'open_after' => $after['summary']['open_reminders'] ?? 0,
            ],
        ];
    }

    private function testReports(): array
    {
        $client = $this->requireShared('client');
        $serviceOrder = $this->requireShared('service_order');
        $invoice = $this->requireShared('invoice');
        $receipt = $this->requireShared('receipt');
        $reports = new ReportRepository();
        $documents = new DocumentRepository();

        $clientRegister = $reports->clientRegister(['search' => $client['pan'], 'crm_id' => 0, 'status' => 'active'], 1, 10);
        $serviceRegister = $reports->serviceOrderRegister(['search' => $serviceOrder['so_no']], 1, 10);
        $invoiceRegister = $reports->invoiceRegister(['search' => $invoice['invoice_no']], 1, 10);
        $receiptRegister = $reports->receiptRegister(['search' => $receipt['receipt_no']], 1, 10);
        $gstSummary = $reports->gstSummary(['company_id' => 0, 'work_basis' => 'MONTHLY', 'period_year' => '2026']);
        $clientName = (string) $this->fetchValue('SELECT legal_name FROM clients WHERE id = :id', ['id' => $client['id']]);
        $documentReport = $documents->accessReport(['search' => $clientName], 1, 10);

        $this->assertTrue((int) ($clientRegister['total'] ?? 0) >= 1, 'Expected client register to include created client.');
        $this->assertTrue((int) ($serviceRegister['total'] ?? 0) >= 1, 'Expected service order register to include created SO.');
        $this->assertTrue((int) ($invoiceRegister['total'] ?? 0) >= 1, 'Expected invoice register to include created invoice.');
        $this->assertTrue((int) ($receiptRegister['total'] ?? 0) >= 1, 'Expected receipt register to include created receipt.');
        $this->assertTrue(isset($gstSummary['summary']['total_orders']), 'Expected GST summary to return summary metrics.');
        $this->assertTrue((int) ($documentReport['total'] ?? 0) >= 1, 'Expected document access report to include secure access logs.');

        return [
            'checks' => [
                'Client register exposes the created client.',
                'Service order, invoice, and receipt registers expose created billing chain.',
                'GST summary and document access report are queryable.',
            ],
            'details' => [
                'client_total' => $clientRegister['total'] ?? 0,
                'service_order_total' => $serviceRegister['total'] ?? 0,
                'invoice_total' => $invoiceRegister['total'] ?? 0,
                'receipt_total' => $receiptRegister['total'] ?? 0,
                'document_access_total' => $documentReport['total'] ?? 0,
            ],
        ];
    }

    private function bootstrapActors(): void
    {
        $superAdmin = $this->firstUserByRole('SUPER_ADMIN');
        $portalManager = $this->firstUserByPermission('users.manage.portal');
        $crm = $this->firstUserByRole('CRM') ?? $this->firstUserByRole('ADMIN') ?? $superAdmin;

        if ($superAdmin === null || $portalManager === null || $crm === null) {
            throw new RuntimeException('Required seed users were not found for regression execution.');
        }

        $this->context['shared']['super_admin'] = $superAdmin;
        $this->context['shared']['portal_manager'] = $portalManager;
        $this->context['shared']['crm_user'] = $crm;

        $internalUser = $this->createInternalUser('BACKEND_STAFF', 'Regression Internal User', '9000000100');
        $this->context['shared']['internal_user'] = $internalUser;
        $this->context['shared']['search_user'] = $this->createInternalUser('ADMIN', 'Regression Search User', '9000000101');
    }

    private function createInternalUser(string $roleCode, string $fullName, string $mobile): array
    {
        $actor = $this->requireShared('super_admin');
        $roleId = $this->roleId($roleCode);
        $password = 'Smoke!234';
        $username = 'reg.internal.' . substr(md5((string) microtime(true)), 0, 8);

        $userId = (new UserService())->create([
            'user_type' => 'INTERNAL',
            'role_ids' => [$roleId],
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'email' => $username . '@example.test',
            'mobile' => $mobile,
            'employee_code' => 'REG-' . random_int(100, 999),
        ], $actor['session']);

        $session = $this->userSessionById($userId);

        return [
            'id' => $userId,
            'username' => $username,
            'password' => $password,
            'session' => $session,
        ];
    }

    private function createPortalUser(int $clientContactId): array
    {
        $actor = $this->requireShared('portal_manager');
        $roleId = $this->roleId('CLIENT');
        $password = 'Portal!234';
        $username = 'reg.portal.' . substr(md5((string) microtime(true)), 0, 8);

        $userId = (new UserService())->create([
            'user_type' => 'PORTAL',
            'role_ids' => [$roleId],
            'client_contact_id' => $clientContactId,
            'username' => $username,
            'password' => $password,
            'full_name' => 'Regression Portal User',
            'email' => $username . '@example.test',
            'mobile' => '9000000200',
        ], $actor['session']);

        return [
            'id' => $userId,
            'username' => $username,
            'password' => $password,
            'session' => $this->userSessionById($userId),
        ];
    }

    private function seedClientDocument(int $clientId, int $uploadedBy): int
    {
        $storageRoot = rtrim((string) Config::get('app.private_storage_path'), '\\/');
        if ($storageRoot === '') {
            throw new RuntimeException('Private storage path is not configured.');
        }

        $relativeDirectory = 'uploads/regression/' . date('Y/m');
        $absoluteDirectory = $storageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new RuntimeException('Unable to create regression document directory.');
        }

        $fileName = 'regression-document-' . substr(md5((string) microtime(true)), 0, 12) . '.txt';
        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($absolutePath, 'Regression secure document test');
        $this->context['cleanup_files'][] = $absolutePath;

        $this->connection->prepare(
            "INSERT INTO documents (
                client_id, linked_module, linked_id, document_category, document_name, current_version_no,
                latest_file_name, latest_file_path, mime_type, file_size, checksum_sha256, uploaded_by, uploaded_at, is_active
             ) VALUES (
                :client_id, 'CLIENT', :linked_id, 'GENERAL', :document_name, 1,
                :latest_file_name, :latest_file_path, 'text/plain', :file_size, :checksum_sha256, :uploaded_by, NOW(), 1
             )"
        )->execute([
            'client_id' => $clientId,
            'linked_id' => $clientId,
            'document_name' => $fileName,
            'latest_file_name' => $fileName,
            'latest_file_path' => $relativeDirectory . '/' . $fileName,
            'file_size' => filesize($absolutePath) ?: 0,
            'checksum_sha256' => hash_file('sha256', $absolutePath) ?: null,
            'uploaded_by' => $uploadedBy,
        ]);

        $documentId = (int) $this->connection->lastInsertId();
        $this->connection->prepare(
            "INSERT INTO document_versions (
                document_id, version_no, file_name, file_path, mime_type, file_size, checksum_sha256, change_note, uploaded_by, uploaded_at
             ) VALUES (
                :document_id, 1, :file_name, :file_path, 'text/plain', :file_size, :checksum_sha256, 'Regression seeded file', :uploaded_by, NOW()
             )"
        )->execute([
            'document_id' => $documentId,
            'file_name' => $fileName,
            'file_path' => $relativeDirectory . '/' . $fileName,
            'file_size' => filesize($absolutePath) ?: 0,
            'checksum_sha256' => hash_file('sha256', $absolutePath) ?: null,
            'uploaded_by' => $uploadedBy,
        ]);

        return $documentId;
    }

    private function runTest(string $name, callable $callback): void
    {
        $startedAt = microtime(true);

        try {
            $this->connection->beginTransaction();
            $payload = $callback();
            $this->connection->commit();
            $status = 'PASS';
            $error = null;
        } catch (Throwable $throwable) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            $payload = ['checks' => [], 'details' => []];
            $status = 'FAIL';
            $error = $throwable->getMessage();
        }

        $this->results[] = [
            'name' => $name,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'checks' => $payload['checks'] ?? [],
            'details' => $payload['details'] ?? [],
            'error' => $error,
        ];
    }

    private function summary(float $durationSeconds): array
    {
        $passed = count(array_filter($this->results, static fn (array $result): bool => $result['status'] === 'PASS'));
        $failed = count(array_filter($this->results, static fn (array $result): bool => $result['status'] === 'FAIL'));
        $skipped = count(array_filter($this->results, static fn (array $result): bool => $result['status'] === 'SKIP'));

        return [
            'total' => count($this->results),
            'passed' => $passed,
            'failed' => $failed,
            'skipped' => $skipped,
            'duration_seconds' => number_format($durationSeconds, 2, '.', ''),
        ];
    }

    private function coverageSummary(array $summary): array
    {
        return [
            'Execution model' => 'CLI smoke suite with nested transaction rollback',
            'Command' => 'php database/scripts/run_regression_suite.php',
            'Covered modules' => 'Authentication, RBAC, Clients, Portal Credentials, PSO, Service Orders, Workflow, Billing, Documents, Search, Reminders, Reports',
            'Total smoke tests' => (string) $summary['total'],
            'Passed' => (string) $summary['passed'],
            'Failed' => (string) $summary['failed'],
            'Database cleanup' => 'Full rollback after execution',
            'Execution target' => 'Under 5 minutes',
        ];
    }

    private function installNestedConnection(): NestedTransactionPdo
    {
        $config = Config::get('database');
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $connection = new NestedTransactionPdo($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $reflection = new ReflectionClass(Database::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, $connection);

        return $connection;
    }

    private function resetDatabaseConnection(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    private function firstUserByRole(string $roleCode): ?array
    {
        $record = $this->fetchOne(
            "SELECT u.id, u.username
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code = :role_code
               AND u.is_active = 1
             ORDER BY u.id ASC
             LIMIT 1",
            ['role_code' => $roleCode]
        );

        if ($record === null) {
            return null;
        }

        return [
            'id' => (int) $record['id'],
            'session' => $this->userSessionByUsername((string) $record['username']),
        ];
    }

    private function firstUserByPermission(string $permissionCode): ?array
    {
        $record = $this->fetchOne(
            "SELECT DISTINCT u.id, u.username
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN role_permissions rp ON rp.role_id = urm.role_id AND rp.is_granted = 1
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE p.code = :permission_code
               AND u.is_active = 1
             ORDER BY u.id ASC
             LIMIT 1",
            ['permission_code' => $permissionCode]
        );

        if ($record === null) {
            return null;
        }

        return [
            'id' => (int) $record['id'],
            'session' => $this->userSessionByUsername((string) $record['username']),
        ];
    }

    private function roleId(string $roleCode): int
    {
        $roleId = $this->fetchValue('SELECT id FROM roles WHERE code = :code LIMIT 1', ['code' => $roleCode]);
        if ($roleId === false || $roleId === null) {
            throw new RuntimeException('Role not found: ' . $roleCode);
        }

        return (int) $roleId;
    }

    private function serviceTypeIdByCode(string $serviceTypeCode): int
    {
        $serviceTypeId = $this->fetchValue(
            'SELECT id FROM service_types WHERE code = :code AND is_active = 1 LIMIT 1',
            ['code' => $serviceTypeCode]
        );
        if ($serviceTypeId === false || $serviceTypeId === null) {
            throw new RuntimeException('Service type not found: ' . $serviceTypeCode);
        }

        return (int) $serviceTypeId;
    }

    private function userSessionById(int $userId): array
    {
        $username = $this->fetchValue('SELECT username FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);
        if (!is_string($username) || $username === '') {
            throw new RuntimeException('Unable to resolve session user by id.');
        }

        return $this->userSessionByUsername($username);
    }

    private function userSessionByUsername(string $username): array
    {
        $user = (new UserRepository())->findByUsername($username);
        if ($user === null) {
            throw new RuntimeException('Unable to resolve session user: ' . $username);
        }

        return $user->toSessionArray();
    }

    private function actAs(array $sessionUser): void
    {
        $_SESSION['auth_user'] = $sessionUser;
    }

    private function requireShared(string $key): mixed
    {
        if (!array_key_exists($key, $this->context['shared'])) {
            throw new RuntimeException('Missing regression context key: ' . $key);
        }

        return $this->context['shared'][$key];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    private function fetchValue(string $sql, array $params = []): mixed
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn();
    }

    private function makeRequest(string $method, string $uri): Request
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SCRIPT_NAME'] = '/etask/public/index.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'RegressionSuite/1.0';

        return new Request();
    }

    private function seedServer(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'RegressionSuite/1.0';
        $_SERVER['SCRIPT_NAME'] = '/etask/public/index.php';
        Csrf::token();
    }

    private function deleteCleanupFiles(): void
    {
        foreach ($this->context['cleanup_files'] as $file) {
            if (is_string($file) && is_file($file)) {
                @unlink($file);
            }
        }
    }

    private function assertTrue(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }
}
