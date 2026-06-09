<?php

declare(strict_types=1);

namespace Modules\ClientPortal;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\BillingRepository;
use App\Repositories\ClientRepository;
use App\Repositories\PsoRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\BillingService;
use App\Services\PsoService;
use Throwable;

final class ClientPortalController
{
    public function __construct(
        private readonly PsoRepository $psos = new PsoRepository(),
        private readonly ServiceTypeRepository $serviceTypes = new ServiceTypeRepository(),
        private readonly PsoService $psoService = new PsoService(),
        private readonly ClientRepository $clients = new ClientRepository(),
        private readonly BillingRepository $billing = new BillingRepository(),
        private readonly BillingService $billingService = new BillingService()
    ) {
    }

    public function index(): void
    {
        $internalView = !Auth::can('portal.self_access');
        $content = View::render(base_path('modules/ClientPortal/views/index.php'), [
            'title' => 'Pre-Service Orders',
            'activeMenu' => 'client_portal',
            'psos' => $this->psos->listForPortal(Auth::clientId(), $internalView),
            'internalView' => $internalView,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function create(): void
    {
        $content = View::render(base_path('modules/ClientPortal/views/create.php'), [
            'title' => 'Create PSO',
            'activeMenu' => 'client_portal',
            'serviceTypes' => $this->serviceTypes->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function store(Request $request): void
    {
        $payload = [
            'title' => trim((string) $request->input('title', '')),
            'service_type_id' => (int) $request->input('service_type_id', 0),
            'requested_for_period' => trim((string) $request->input('requested_for_period', '')),
            'description' => trim((string) $request->input('description', '')),
        ];
        Session::flash('old', $payload);

        try {
            $psoId = $this->psoService->create(
                $payload,
                $request->file('documents') ?? [],
                (int) Auth::id(),
                Auth::clientId(),
                Auth::user()['client_contact_id'] ?? null
            );

            Session::flash('success', 'PSO submitted successfully for CRM review.');
            redirect('/client-portal/pso/show?id=' . $psoId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/client-portal/pso/create');
        }
    }

    public function show(Request $request): void
    {
        $psoId = (int) $request->input('id', 0);
        $pso = $this->psos->findById($psoId);

        if ($pso === null) {
            Response::abort(404, 'PSO not found.');
        }

        if (Auth::can('portal.self_access') && Auth::clientId() !== (int) $pso['client_id']) {
            Response::abort(403, 'You are not allowed to view this PSO.');
        }

        $content = View::render(base_path('modules/ClientPortal/views/show.php'), [
            'title' => 'PSO Details',
            'activeMenu' => 'client_portal',
            'pso' => $pso,
            'documents' => $this->psos->documents($psoId),
            'reviews' => $this->psos->reviews($psoId),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function recommendApproval(Request $request): void
    {
        $psoId = (int) $request->input('pso_id', 0);

        try {
            $this->psoService->recommendApproval($psoId, (int) Auth::id(), (string) $request->input('remarks', ''));
            Session::flash('success', 'PSO marked for approval review.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/client-portal/pso/show?id=' . $psoId);
    }

    public function approve(Request $request): void
    {
        $psoId = (int) $request->input('pso_id', 0);

        try {
            $serviceOrderId = $this->psoService->approve($psoId, (int) Auth::id(), (string) $request->input('remarks', ''));
            Session::flash('success', 'PSO approved and converted to service order ' . $serviceOrderId . '.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/client-portal/pso/show?id=' . $psoId);
    }

    public function reject(Request $request): void
    {
        $psoId = (int) $request->input('pso_id', 0);

        try {
            $this->psoService->reject($psoId, (int) Auth::id(), (string) $request->input('reason', ''));
            Session::flash('success', 'PSO rejected by Admin.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/client-portal/pso/show?id=' . $psoId);
    }

    public function account(): void
    {
        if (!Auth::isPortalUser()) {
            Response::abort(403, 'Portal access is required.');
        }

        $clientId = (int) (Auth::clientId() ?? 0);
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            Response::abort(404, 'Client profile not found.');
        }
        $contactId = (int) (Auth::user()['client_contact_id'] ?? 0);

        $content = View::render(base_path('modules/ClientPortal/views/account.php'), [
            'title' => 'Client Account',
            'activeMenu' => 'client_portal',
            'client' => $client,
            'contact' => $this->clients->primaryContact($clientId),
            'invoices' => $this->billing->portalInvoices($clientId),
            'payments' => $this->billing->portalPayments($clientId),
            'notifications' => $this->portalNotifications($contactId),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function payInvoice(Request $request): void
    {
        if (!Auth::isPortalUser()) {
            Response::abort(403, 'Portal access is required.');
        }

        $invoiceId = (int) $request->input('invoice_id', 0);
        $clientId = (int) (Auth::clientId() ?? 0);
        $invoice = $this->billing->portalInvoiceById($invoiceId, $clientId);

        if ($invoice === null) {
            Session::flash('error', 'Invoice not found for this client.');
            redirect('/client-portal/account');
        }

        try {
            $this->billingService->recordPayment([
                'service_order_id' => (int) $invoice['service_order_id'],
                'amount' => $request->input('amount', $invoice['outstanding_amount']),
                'payment_mode' => (string) $request->input('payment_mode', 'BANK_TRANSFER'),
                'transaction_type' => 'INVOICE_PAYMENT',
                'payment_date' => (string) $request->input('payment_date', date('Y-m-d')),
                'reference_no' => (string) $request->input('reference_no', ''),
                'notes' => (string) $request->input('notes', 'Client portal payment submission'),
                'status' => 'SUCCESS',
            ], (int) Auth::id());
            Session::flash('success', 'Payment recorded successfully and receipt generated.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/client-portal/account');
    }

    private function portalNotifications(int $clientContactId): array
    {
        if ($clientContactId <= 0) {
            return [];
        }

        $statement = \App\Core\Database::connection()->prepare(
            "SELECT id, subject, message, linked_module, linked_id, delivery_status, created_at
             FROM notifications
             WHERE client_contact_id = :client_contact_id
             ORDER BY id DESC
             LIMIT 25"
        );
        $statement->execute(['client_contact_id' => $clientContactId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
