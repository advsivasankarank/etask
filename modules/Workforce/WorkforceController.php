<?php

declare(strict_types=1);

namespace Modules\Workforce;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\WorkforceRepository;
use Throwable;

final class WorkforceController
{
    private WorkforceRepository $workforce;

    public function __construct()
    {
        $this->workforce = new WorkforceRepository();
    }

    public function index(): void
    {
        $content = View::render(base_path('modules/Workforce/views/index.php'), [
            'title' => 'Workforce Dashboard',
            'activeMenu' => 'workforce',
            'summary' => $this->workforce->summaryCounts(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function consultants(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
        ];

        $content = View::render(base_path('modules/Workforce/views/consultants.php'), [
            'title' => 'Consultant Register',
            'activeMenu' => 'workforce',
            'consultants' => $this->workforce->paginateConsultants($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function consultantForm(): void
    {
        $content = View::render(base_path('modules/Workforce/views/consultant_form.php'), [
            'title' => 'Add Consultant',
            'activeMenu' => 'workforce',
            'mode' => 'create',
            'consultant' => null,
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function storeConsultant(Request $request): void
    {
        $payload = $this->extractConsultantPayload($request);
        Session::flash('old', $payload);

        if ($payload['name'] === '') {
            Session::flash('error', 'Consultant name is required.');
            redirect('/workforce/consultants/create');
        }

        try {
            $id = $this->workforce->createConsultant($payload);
            Session::flash('success', 'Consultant added successfully.');
            redirect('/workforce/consultants/show?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/workforce/consultants/create');
        }
    }

    public function showConsultant(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $consultant = $this->workforce->findConsultantById($id);

        if ($consultant === null) {
            Response::abort(404, 'Consultant not found.');
        }

        $content = View::render(base_path('modules/Workforce/views/consultant_show.php'), [
            'title' => 'Consultant Details',
            'activeMenu' => 'workforce',
            'consultant' => $consultant,
            'assignments' => $this->workforce->assignmentsForConsultant($id),
            'bills' => $this->workforce->billsForConsultant($id),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function editConsultant(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $consultant = $this->workforce->findConsultantById($id);

        if ($consultant === null) {
            Response::abort(404, 'Consultant not found.');
        }

        $content = View::render(base_path('modules/Workforce/views/consultant_form.php'), [
            'title' => 'Edit Consultant',
            'activeMenu' => 'workforce',
            'mode' => 'edit',
            'consultant' => $consultant,
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateConsultant(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $payload = $this->extractConsultantPayload($request);
        Session::flash('old', $payload);

        if ($payload['name'] === '') {
            Session::flash('error', 'Consultant name is required.');
            redirect('/workforce/consultants/edit?id=' . $id);
        }

        try {
            $this->workforce->updateConsultant($id, $payload);
            Session::flash('success', 'Consultant updated successfully.');
            redirect('/workforce/consultants/show?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/workforce/consultants/edit?id=' . $id);
        }
    }

    public function archiveConsultant(Request $request): void
    {
        $id = (int) $request->input('id', 0);

        try {
            $this->workforce->archiveConsultant($id);
            Session::flash('success', 'Consultant archived successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/workforce/consultants');
    }

    public function consultantAssignments(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/Workforce/views/consultant_assignments.php'), [
            'title' => 'Consultant Assignments',
            'activeMenu' => 'workforce',
            'assignments' => $this->workforce->paginateAssignments($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function consultantAssignmentForm(): void
    {
        $content = View::render(base_path('modules/Workforce/views/consultant_assignment_form.php'), [
            'title' => 'Create Assignment',
            'activeMenu' => 'workforce',
            'consultants' => $this->workforce->allActiveConsultants(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createAssignment(Request $request): void
    {
        $payload = [
            'consultant_id' => (int) $request->input('consultant_id', 0),
            'service_order_id' => (int) $request->input('service_order_id', 0) ?: null,
            'client_id' => (int) $request->input('client_id', 0) ?: null,
            'assignment_title' => trim((string) $request->input('assignment_title', '')),
            'assignment_description' => trim((string) $request->input('assignment_description', '')),
            'assigned_by' => (int) Auth::id(),
            'due_date' => trim((string) $request->input('due_date', '')),
            'status' => 'ASSIGNED',
            'fee_agreed' => (float) $request->input('fee_agreed', 0) ?: null,
            'remarks' => trim((string) $request->input('remarks', '')),
        ];

        Session::flash('old', $payload);

        if ($payload['consultant_id'] <= 0 || $payload['assignment_title'] === '') {
            Session::flash('error', 'Consultant and assignment title are required.');
            redirect('/workforce/consultant-assignments/create');
        }

        try {
            $this->workforce->createAssignment($payload);
            Session::flash('success', 'Assignment created successfully.');
            redirect('/workforce/consultant-assignments');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/workforce/consultant-assignments/create');
        }
    }

    public function updateAssignmentStatus(Request $request): void
    {
        $id = (int) $request->input('assignment_id', 0);
        $status = strtoupper((string) $request->input('status', ''));

        if (!in_array($status, ['ASSIGNED', 'IN_PROGRESS', 'DELIVERED', 'APPROVED', 'REWORK', 'CANCELLED'], true)) {
            Session::flash('error', 'Invalid status.');
            redirect('/workforce/consultant-assignments');
        }

        try {
            $this->workforce->updateAssignmentStatus($id, $status);
            Session::flash('success', 'Assignment status updated.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/workforce/consultant-assignments');
    }

    public function consultantDeliverables(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/Workforce/views/consultant_deliverables.php'), [
            'title' => 'Consultant Deliverables',
            'activeMenu' => 'workforce',
            'deliverables' => $this->workforce->paginateDeliverables($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateDeliverableStatus(Request $request): void
    {
        $id = (int) $request->input('deliverable_id', 0);
        $status = strtoupper((string) $request->input('status', ''));

        if (!in_array($status, ['PENDING', 'SUBMITTED', 'APPROVED', 'REWORK', 'REJECTED'], true)) {
            Session::flash('error', 'Invalid status.');
            redirect('/workforce/consultant-deliverables');
        }

        try {
            $this->workforce->updateDeliverableStatus($id, $status);
            Session::flash('success', 'Deliverable status updated.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/workforce/consultant-deliverables');
    }

    public function consultantBills(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/Workforce/views/consultant_bills.php'), [
            'title' => 'Consultant Bills',
            'activeMenu' => 'workforce',
            'bills' => $this->workforce->paginateBills($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateBillStatus(Request $request): void
    {
        $id = (int) $request->input('bill_id', 0);
        $status = strtoupper((string) $request->input('status', ''));

        if (!in_array($status, ['DRAFT', 'SUBMITTED', 'APPROVED', 'PAID', 'REJECTED'], true)) {
            Session::flash('error', 'Invalid status.');
            redirect('/workforce/consultant-bills');
        }

        try {
            $this->workforce->updateBillStatus($id, $status);
            Session::flash('success', 'Bill status updated.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/workforce/consultant-bills');
    }

    public function consultantPayments(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [];

        $content = View::render(base_path('modules/Workforce/views/consultant_payments.php'), [
            'title' => 'Consultant Payments',
            'activeMenu' => 'workforce',
            'payments' => $this->workforce->paginatePayments($filters, $page),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createPayment(Request $request): void
    {
        $payload = [
            'consultant_bill_id' => (int) $request->input('consultant_bill_id', 0),
            'payment_date' => trim((string) $request->input('payment_date', '')),
            'amount' => (float) $request->input('amount', 0),
            'mode' => trim((string) $request->input('mode', '')),
            'reference_no' => trim((string) $request->input('reference_no', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];

        if ($payload['consultant_bill_id'] <= 0 || $payload['amount'] <= 0) {
            Session::flash('error', 'Bill and amount are required.');
            redirect('/workforce/consultant-payments');
        }

        try {
            $this->workforce->createPayment($payload);
            Session::flash('success', 'Payment recorded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/workforce/consultant-payments');
    }

    private function extractConsultantPayload(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'firm_name' => trim((string) $request->input('firm_name', '')),
            'mobile' => trim((string) $request->input('mobile', '')),
            'email' => trim((string) $request->input('email', '')),
            'pan' => trim((string) $request->input('pan', '')),
            'gstin' => trim((string) $request->input('gstin', '')),
            'address' => trim((string) $request->input('address', '')),
            'expertise' => trim((string) $request->input('expertise', '')),
            'status' => (string) $request->input('status', 'ACTIVE'),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];
    }
}
