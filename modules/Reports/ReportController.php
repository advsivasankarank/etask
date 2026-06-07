<?php

declare(strict_types=1);

namespace Modules\Reports;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\DocumentRepository;
use App\Repositories\ReportRepository;

final class ReportController
{
    public function __construct(
        private readonly ReportRepository $reports = new ReportRepository(),
        private readonly DocumentRepository $documents = new DocumentRepository()
    ) {
    }

    public function index(): void
    {
        Response::html(View::render(base_path('modules/Reports/views/index.php'), [
            'title' => 'Reports',
            'activeMenu' => 'reports',
            'cards' => $this->reports->overviewCards(),
        ]));
    }

    public function clients(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'crm_id' => (int) $request->input('crm_id', 0),
            'status' => (string) $request->input('status', 'active'),
        ];

        Response::html(View::render(base_path('modules/Reports/views/clients.php'), [
            'title' => 'Client Register',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->clientRegister($filters, $page),
        ]));
    }

    public function serviceOrders(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'company_id' => (int) $request->input('company_id', 0),
            'service_type_id' => (int) $request->input('service_type_id', 0),
            'financial_year_id' => (int) $request->input('financial_year_id', 0),
            'stage_code' => trim((string) $request->input('stage_code', '')),
            'work_basis' => trim((string) $request->input('work_basis', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/service_orders.php'), [
            'title' => 'Service Order Register',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->serviceOrderRegister($filters, $page),
        ]));
    }

    public function invoices(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'company_id' => (int) $request->input('company_id', 0),
            'payment_status' => trim((string) $request->input('payment_status', '')),
            'invoice_type' => trim((string) $request->input('invoice_type', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/invoices.php'), [
            'title' => 'Invoice Register',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->invoiceRegister($filters, $page),
        ]));
    }

    public function receipts(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'company_id' => (int) $request->input('company_id', 0),
            'payment_mode' => trim((string) $request->input('payment_mode', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/receipts.php'), [
            'title' => 'Receipt Register',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->receiptRegister($filters, $page),
        ]));
    }

    public function outstanding(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'company_id' => (int) $request->input('company_id', 0),
            'payment_status' => trim((string) $request->input('payment_status', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'overdue_only' => (string) $request->input('overdue_only', ''),
        ];

        Response::html(View::render(base_path('modules/Reports/views/outstanding.php'), [
            'title' => 'Outstanding Report',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->outstandingReport($filters, $page),
        ]));
    }

    public function gstSummary(Request $request): void
    {
        $filters = [
            'company_id' => (int) $request->input('company_id', 0),
            'work_basis' => trim((string) $request->input('work_basis', '')),
            'period_year' => trim((string) $request->input('period_year', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/gst_summary.php'), [
            'title' => 'GST Summary',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->gstSummary($filters),
        ]));
    }

    public function revenue(Request $request): void
    {
        $filters = [
            'company_id' => (int) $request->input('company_id', 0),
            'service_type_id' => (int) $request->input('service_type_id', 0),
            'financial_year_id' => (int) $request->input('financial_year_id', 0),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/revenue.php'), [
            'title' => 'Revenue Report',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'options' => $this->reports->filterOptions(),
            'report' => $this->reports->revenueReport($filters),
        ]));
    }

    public function documentAccess(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'action_code' => trim((string) $request->input('action_code', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        Response::html(View::render(base_path('modules/Reports/views/document_access.php'), [
            'title' => 'Document Access Report',
            'activeMenu' => 'reports',
            'filters' => $filters,
            'report' => $this->documents->accessReport($filters, $page),
        ]));
    }
}
