<?php

declare(strict_types=1);

namespace Modules\Accounts;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AccountsRepository;
use Throwable;

final class AccountsController
{
    private AccountsRepository $accounts;

    public function __construct()
    {
        $this->accounts = new AccountsRepository();
    }

    public function index(): void
    {
        $content = View::render(base_path('modules/Accounts/views/index.php'), [
            'title' => 'Accounts Dashboard',
            'activeMenu' => 'accounts',
            'summary' => $this->accounts->summaryCounts(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function invoices(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $request->input('search', ''),
            'payment_status' => $request->input('payment_status', ''),
            'client_id' => $request->input('client_id', ''),
        ];

        $content = View::render(base_path('modules/Accounts/views/invoices.php'), [
            'title' => 'Invoice Register',
            'activeMenu' => 'accounts',
            'invoices' => $this->accounts->paginateInvoices($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function receipts(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $request->input('search', ''),
            'client_id' => $request->input('client_id', ''),
        ];

        $content = View::render(base_path('modules/Accounts/views/receipts.php'), [
            'title' => 'Receipt Register',
            'activeMenu' => 'accounts',
            'receipts' => $this->accounts->paginateReceipts($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function payments(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $request->input('search', ''),
            'transaction_type' => $request->input('transaction_type', ''),
        ];

        $content = View::render(base_path('modules/Accounts/views/payments.php'), [
            'title' => 'Payment Register',
            'activeMenu' => 'accounts',
            'payments' => $this->accounts->paginatePayments($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function outstanding(): void
    {
        $content = View::render(base_path('modules/Accounts/views/outstanding.php'), [
            'title' => 'Outstanding Register',
            'activeMenu' => 'accounts',
            'invoices' => $this->accounts->outstandingInvoices(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function ageing(): void
    {
        $ageingData = $this->accounts->ageingData();
        $buckets = ['Not Due' => [], '0-30' => [], '31-60' => [], '61-90' => [], '90+' => []];
        foreach ($ageingData as $row) {
            $bucket = $row['ageing_bucket'] ?? 'Not Due';
            $buckets[$bucket][] = $row;
        }

        $content = View::render(base_path('modules/Accounts/views/ageing.php'), [
            'title' => 'Collection Ageing',
            'activeMenu' => 'accounts',
            'buckets' => $buckets,
            'total' => count($ageingData),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function followups(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/Accounts/views/followups.php'), [
            'title' => 'Collection Follow-up',
            'activeMenu' => 'accounts',
            'followups' => $this->accounts->paginateFollowups($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createFollowup(Request $request): void
    {
        $payload = [
            'client_id' => (int) $request->input('client_id', 0) ?: null,
            'invoice_id' => (int) $request->input('invoice_id', 0) ?: null,
            'service_order_id' => (int) $request->input('service_order_id', 0) ?: null,
            'followup_date' => trim((string) $request->input('followup_date', '')),
            'followup_mode' => trim((string) $request->input('followup_mode', '')),
            'followup_note' => trim((string) $request->input('followup_note', '')),
            'next_followup_date' => trim((string) $request->input('next_followup_date', '')),
            'status' => strtoupper((string) $request->input('status', 'OPEN')),
            'created_by' => (int) Auth::id(),
        ];

        if (empty($payload['followup_note'])) {
            Session::flash('error', 'Follow-up note is required.');
            redirect('/accounts/followups');
        }

        try {
            $this->accounts->createFollowup($payload);
            Session::flash('success', 'Follow-up recorded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/accounts/followups');
    }

    public function consultantPayables(): void
    {
        $content = View::render(base_path('modules/Accounts/views/consultant_payables.php'), [
            'title' => 'Consultant Payables',
            'activeMenu' => 'accounts',
            'payables' => $this->accounts->consultantPayables(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function unbilledWork(): void
    {
        $content = View::render(base_path('modules/Accounts/views/unbilled_work.php'), [
            'title' => 'Unbilled Completed Work',
            'activeMenu' => 'accounts',
            'workOrders' => $this->accounts->unbilledCompletedWork(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function reports(): void
    {
        $content = View::render(base_path('modules/Accounts/views/reports.php'), [
            'title' => 'Accounts Reports',
            'activeMenu' => 'accounts',
            'summary' => $this->accounts->summaryCounts(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }
}
