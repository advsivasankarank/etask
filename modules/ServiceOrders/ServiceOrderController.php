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
use App\Repositories\ConsultantRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\BillingService;
use App\Services\DocumentUploadService;
use App\Services\ServiceOrderService;
use App\Services\WorkflowService;
use Throwable;

final class ServiceOrderController
{
    private ServiceOrderRepository $serviceOrders;
    private ClientRepository $clients;
    private ServiceTypeRepository $serviceTypes;
    private CompanyRepository $companies;
    private DocumentRepository $documents;
    private ConsultantRepository $consultants;
    private ServiceOrderService $serviceOrderService;
    private WorkflowService $workflows;
    private BillingService $billingService;
    private DocumentUploadService $documentUploads;

    public function __construct()
    {
        $this->serviceOrders = new ServiceOrderRepository();
        $this->clients = new ClientRepository();
        $this->serviceTypes = new ServiceTypeRepository();
        $this->companies = new CompanyRepository();
        $this->documents = new DocumentRepository();
        $this->consultants = new ConsultantRepository();
        $this->serviceOrderService = new ServiceOrderService();
        $this->workflows = new WorkflowService();
        $this->billingService = new BillingService();
        $this->documentUploads = new DocumentUploadService();
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $clientId = Auth::isPortalUser() ? Auth::clientId() : null;
        $page = max(1, (int) $request->input('page', 1));
        $pagination = $this->serviceOrders->paginateForIndex($search, $clientId, $page, 12);

        if (Auth::isPortalUser()) {
            $content = View::render(base_path('modules/ServiceOrders/views/portal_index.php'), [
                'title' => 'My Services',
                'activeMenu' => 'service_orders',
                'orders' => $pagination['items'],
                'pagination' => $pagination,
                'search' => $search,
                'success' => Session::pullFlash('success'),
                'error' => Session::pullFlash('error'),
            ]);

            Response::html($content);
            return;
        }

        $content = View::render(base_path('modules/ServiceOrders/views/index.php'), [
            'title' => 'Service Orders',
            'activeMenu' => 'service_orders',
            'orders' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'summary' => $this->serviceOrders->summaryCounts(),
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
            'itrCaseOptions' => ['BUSINESS' => 'Business Case', 'NON_BUSINESS' => 'Other Than Business Case'],
            'yesNoOptions' => ['YES' => 'Yes', 'NO' => 'No'],
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
            'itr_case_nature' => (string) $request->input('itr_case_nature', ''),
            'itr_tax_audit_applicable' => (string) $request->input('itr_tax_audit_applicable', ''),
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

        if (Auth::isPortalUser()) {
            $content = View::render(base_path('modules/ServiceOrders/views/portal_show.php'), [
                'title' => 'Service Tracking',
                'activeMenu' => 'service_orders',
                'order' => $order,
                'billing' => $this->billingService->billingDashboard($id),
                'linkedDocuments' => $this->documents->forLinkedRecord('SO', $id),
                'activityTimeline' => $this->serviceOrders->activityTimeline($id),
                'workflowStages' => $context['stages'],
                'workflowHistory' => $context['history'],
                'workflowMilestones' => $context['milestones'],
                'workflowReminders' => $context['reminders'],
                'workflowClosures' => $context['closures'],
                'workflowRules' => $context['rules'],
                'success' => Session::pullFlash('success'),
                'error' => Session::pullFlash('error'),
            ]);

            Response::html($content);
            return;
        }

        $content = View::render(base_path('modules/ServiceOrders/views/show.php'), [
            'title' => 'Service Order Details',
            'activeMenu' => 'service_orders',
            'order' => $order,
            'billing' => $this->billingService->billingDashboard($id),
            'linkedDocuments' => $this->documents->forLinkedRecord('SO', $id),
            'consultantAssignments' => $this->consultants->assignments($id),
            'consultants' => $this->consultants->consultants(),
            'reviewers' => $this->consultants->internalReviewers(),
            'activityTimeline' => $this->serviceOrders->activityTimeline($id),
            'workflowStages' => $context['stages'],
            'workflowHistory' => $context['history'],
            'workflowMilestones' => $context['milestones'],
            'workflowReminders' => $context['reminders'],
            'workflowClosures' => $context['closures'],
            'workflowRules' => $context['rules'],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function uploadDocument(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        if ($serviceOrderId <= 0) {
            Session::flash('error', 'A valid service order is required for document upload.');
            redirect('/service-orders');
        }

        $order = $this->serviceOrders->findDetailedById($serviceOrderId);
        if ($order === null) {
            Session::flash('error', 'Service order not found.');
            redirect('/service-orders');
        }

        if (Auth::isPortalUser() && (int) ($order['client_id'] ?? 0) !== (int) Auth::clientId()) {
            Response::abort(403, 'You do not have access to this service order.');
        }

        try {
            $documentCategory = trim((string) $request->input('document_category', 'SERVICE_ORDER_DOC')) ?: 'SERVICE_ORDER_DOC';
            $files = $request->files()['documents'] ?? null;

            if ($files === null || !isset($files['name'])) {
                throw new \RuntimeException('Please choose at least one file to upload.');
            }

            $documentIds = $this->documentUploads->uploadLinkedDocuments(
                clientId: (int) $order['client_id'],
                linkedModule: 'SO',
                linkedId: $serviceOrderId,
                documentCategory: $documentCategory,
                files: $files,
                uploadedBy: (int) Auth::id(),
                directoryKey: 'service_orders'
            );

            if ($documentIds === []) {
                throw new \RuntimeException('No valid documents were uploaded.');
            }

            Session::flash('success', 'Service order document uploaded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . $serviceOrderId . '#documents');
    }
}
