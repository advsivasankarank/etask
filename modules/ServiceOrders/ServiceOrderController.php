<?php

declare(strict_types=1);

namespace Modules\ServiceOrders;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClientRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\UserRepository;
use App\Services\ServiceOrderService;
use App\Services\WorkflowService;
use RuntimeException;
use Throwable;

final class ServiceOrderController
{
    public function __construct(
        private readonly ServiceOrderRepository $serviceOrders = new ServiceOrderRepository(),
        private readonly ClientRepository $clients = new ClientRepository(),
        private readonly ServiceTypeRepository $serviceTypes = new ServiceTypeRepository(),
        private readonly CompanyRepository $companies = new CompanyRepository(),
        private readonly ServiceOrderService $serviceOrderService = new ServiceOrderService(),
        private readonly WorkflowService $workflows = new WorkflowService()
    ) {
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $clientId = Auth::isPortalUser() ? Auth::clientId() : null;
        $page = max(1, (int) $request->input('page', 1));
        $pagination = $this->serviceOrders->paginateForIndex($search, $clientId, $page, 12);

        $content = View::render(base_path('modules/ServiceOrders/views/index.php'), [
            'title' => 'Service Orders',
            'activeMenu' => 'service_orders',
            'orders' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function create(Request $request): void
    {
        $old = Session::pullFlash('old', []);
        $clientId = (int) $request->input('client_id', 0);
        if ($clientId > 0 && !isset($old['client_id'])) {
            $old['client_id'] = $clientId;
        }

        $content = View::render(base_path('modules/ServiceOrders/views/create.php'), [
            'title' => 'Create Service Order',
            'activeMenu' => 'service_orders',
            'clients' => $this->clients->allActive(),
            'serviceTypes' => $this->serviceTypes->allActive(),
            'companies' => $this->companies->allActive(),
            'old' => $old,
            'error' => Session::pullFlash('error'),
            'priorityOptions' => ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'],
            'workBasisOptions' => ['ANNUAL', 'MONTHLY', 'QUARTERLY'],
            'quarterOptions' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'monthOptions' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ],
            'gstSubtypeOptions' => ['GSTR1', 'GSTR3B', 'GSTR9', 'GSTR9C', 'OTHER'],
        ]);

        Response::html($content);
    }

    public function store(Request $request): void
    {
        $payload = [
            'client_id' => (int) $request->input('client_id', 0),
            'service_type_id' => (int) $request->input('service_type_id', 0),
            'company_id' => (int) $request->input('company_id', 0),
            'title' => trim((string) $request->input('title', '')),
            'description' => trim((string) $request->input('description', '')),
            'priority_level' => (string) $request->input('priority_level', 'MEDIUM'),
            'work_basis' => (string) $request->input('work_basis', ''),
            'compliance_subtype' => (string) $request->input('compliance_subtype', ''),
            'assessment_year' => trim((string) $request->input('assessment_year', '')),
            'period_month' => (int) $request->input('period_month', 0),
            'period_quarter' => (string) $request->input('period_quarter', ''),
            'period_year' => (int) $request->input('period_year', 0),
            'assigned_crm_id' => null,
            'assigned_assistant_crm_id' => null,
            'assigned_backend_id' => null,
            'assigned_deo_id' => null,
        ];

        Session::flash('old', $payload);

        if ($payload['client_id'] <= 0 || $payload['service_type_id'] <= 0 || $payload['title'] === '') {
            Session::flash('error', 'Client, service type, and title are required.');
            redirect('/service-orders/create');
        }

        try {
            $serviceOrderId = $this->serviceOrderService->create($payload, (int) Auth::id());
            Session::flash('success', 'Service order created successfully with immutable SO number.');
            redirect('/service-orders/show?id=' . $serviceOrderId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/service-orders/create');
        }
    }

    public function show(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $context = $this->workflows->getWorkflowContext($id);
        $order = $context['order'];

        if (Auth::isPortalUser() && Auth::clientId() !== (int) ($order['client_id'] ?? 0)) {
            Response::abort(403, 'You are not allowed to view this service order.');
        }

        $content = View::render(base_path('modules/ServiceOrders/views/show.php'), [
            'title' => 'Service Order Details',
            'activeMenu' => 'service_orders',
            'order' => $order,
            'workflowStages' => $context['stages'],
            'workflowHistory' => $context['history'],
            'workflowReminders' => $context['reminders'],
            'workflowClosures' => $context['closures'],
            'workflowRules' => $context['rules'],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }
}
