<?php

declare(strict_types=1);

namespace Modules\Billing;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Auth;
use App\Repositories\BillingRepository;
use App\Services\BillingService;
use Throwable;

final class BillingController
{
    public function __construct(
        private readonly BillingRepository $billing = new BillingRepository(),
        private readonly BillingService $billingService = new BillingService()
    ) {
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $pagination = $this->billing->paginateServiceOrdersForBilling($page, 12, $search);

        $content = View::render(base_path('modules/Billing/views/index.php'), [
            'title' => 'Billing',
            'activeMenu' => 'billing',
            'orders' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function show(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);
        $dashboard = $this->billingService->billingDashboard($serviceOrderId);

        $content = View::render(base_path('modules/Billing/views/show.php'), [
            'title' => 'Billing Workspace',
            'activeMenu' => 'billing',
            'billing' => $dashboard,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function addDisbursement(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->billingService->createDisbursement($request->all(), (int) Auth::id());
            Session::flash('success', 'Disbursement added successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/billing/show?service_order_id=' . $serviceOrderId);
    }

    public function createInvoice(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->billingService->createInvoice($request->all(), (int) Auth::id());
            Session::flash('success', 'Invoice created successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/billing/show?service_order_id=' . $serviceOrderId);
    }

    public function recordPayment(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $receiptId = $this->billingService->recordPayment($request->all(), (int) Auth::id());
            Session::flash('success', 'Payment recorded and receipt generated (ID ' . $receiptId . ').');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/billing/show?service_order_id=' . $serviceOrderId);
    }
}
