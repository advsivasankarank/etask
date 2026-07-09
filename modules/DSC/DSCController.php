<?php

declare(strict_types=1);

namespace Modules\DSC;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClientRepository;
use App\Repositories\DSCRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\UserRepository;
use Throwable;

final class DSCController
{
    private DSCRepository $dscRepo;
    private ClientRepository $clients;
    private ServiceOrderRepository $serviceOrders;
    private UserRepository $users;

    public function __construct()
    {
        $this->dscRepo = new DSCRepository();
        $this->clients = new ClientRepository();
        $this->serviceOrders = new ServiceOrderRepository();
        $this->users = new UserRepository();
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $search,
            'custody_status' => $request->input('custody_status', ''),
            'client_id' => $request->input('client_id', ''),
        ];

        $content = View::render(base_path('modules/DSC/views/index.php'), [
            'title' => 'DSC Register',
            'activeMenu' => 'dsc',
            'dscList' => $this->dscRepo->paginateRegister($filters, $page),
            'summary' => $this->dscRepo->summaryCounts(),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function create(): void
    {
        $content = View::render(base_path('modules/DSC/views/form.php'), [
            'title' => 'Add DSC',
            'activeMenu' => 'dsc',
            'mode' => 'create',
            'dsc' => null,
            'clients' => $this->clients->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function store(Request $request): void
    {
        $payload = $this->extractPayload($request);
        Session::flash('old', $payload);

        if ($payload['holder_name'] === '') {
            Session::flash('error', 'Holder name is required.');
            redirect('/dsc/create');
        }

        try {
            $id = $this->dscRepo->create($payload);
            Session::flash('success', 'DSC registered successfully.');
            redirect('/dsc/show?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/dsc/create');
        }
    }

    public function show(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $dsc = $this->dscRepo->findById($id);

        if ($dsc === null) {
            Response::abort(404, 'DSC not found.');
        }

        $content = View::render(base_path('modules/DSC/views/show.php'), [
            'title' => 'DSC Details',
            'activeMenu' => 'dsc',
            'dsc' => $dsc,
            'movements' => $this->dscRepo->movementsForDSC($id),
            'usageLogs' => $this->dscRepo->usageLogsForDSC($id),
            'renewals' => $this->dscRepo->renewalsForDSC($id),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $dsc = $this->dscRepo->findById($id);

        if ($dsc === null) {
            Response::abort(404, 'DSC not found.');
        }

        $content = View::render(base_path('modules/DSC/views/form.php'), [
            'title' => 'Edit DSC',
            'activeMenu' => 'dsc',
            'mode' => 'edit',
            'dsc' => $dsc,
            'clients' => $this->clients->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $payload = $this->extractPayload($request);
        Session::flash('old', $payload);

        if ($payload['holder_name'] === '') {
            Session::flash('error', 'Holder name is required.');
            redirect('/dsc/edit?id=' . $id);
        }

        try {
            $this->dscRepo->update($id, $payload);
            Session::flash('success', 'DSC updated successfully.');
            redirect('/dsc/show?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/dsc/edit?id=' . $id);
        }
    }

    public function archive(Request $request): void
    {
        $id = (int) $request->input('id', 0);

        try {
            $this->dscRepo->archive($id);
            Session::flash('success', 'DSC archived successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/dsc');
    }

    public function movement(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/DSC/views/movement.php'), [
            'title' => 'DSC Movement Register',
            'activeMenu' => 'dsc',
            'movements' => $this->dscRepo->paginateMovements($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function movementForm(): void
    {
        $content = View::render(base_path('modules/DSC/views/movement_form.php'), [
            'title' => 'Record DSC Movement',
            'activeMenu' => 'dsc',
            'dscList' => $this->dscRepo->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createMovement(Request $request): void
    {
        $payload = [
            'dsc_id' => (int) $request->input('dsc_id', 0),
            'from_user_id' => (int) $request->input('from_user_id', 0) ?: null,
            'to_user_id' => (int) $request->input('to_user_id', 0) ?: null,
            'from_location' => trim((string) $request->input('from_location', '')),
            'to_location' => trim((string) $request->input('to_location', '')),
            'movement_type' => strtoupper((string) $request->input('movement_type', 'TRANSFERRED')),
            'expected_return_date' => trim((string) $request->input('expected_return_date', '')),
            'purpose' => trim((string) $request->input('purpose', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];

        Session::flash('old', $payload);

        if ($payload['dsc_id'] <= 0) {
            Session::flash('error', 'DSC is required.');
            redirect('/dsc/movement/create');
        }

        try {
            $this->dscRepo->createMovement($payload);
            Session::flash('success', 'DSC movement recorded successfully.');
            redirect('/dsc/movement');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/dsc/movement/create');
        }
    }

    public function returnMovement(Request $request): void
    {
        $movementId = (int) $request->input('movement_id', 0);

        try {
            $this->dscRepo->returnMovement($movementId);
            Session::flash('success', 'DSC return recorded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/dsc/movement');
    }

    public function archiveMovement(Request $request): void
    {
        $movementId = (int) $request->input('movement_id', 0);

        try {
            $this->dscRepo->archiveMovement($movementId);
            Session::flash('success', 'DSC movement archived.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/dsc/movement');
    }

    public function usage(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [];

        $content = View::render(base_path('modules/DSC/views/usage.php'), [
            'title' => 'DSC Usage Log',
            'activeMenu' => 'dsc',
            'usageLogs' => $this->dscRepo->paginateUsage($filters, $page),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function usageForm(): void
    {
        $content = View::render(base_path('modules/DSC/views/usage_form.php'), [
            'title' => 'Log DSC Usage',
            'activeMenu' => 'dsc',
            'dscList' => $this->dscRepo->allActive(),
            'clients' => $this->clients->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createUsage(Request $request): void
    {
        $payload = [
            'dsc_id' => (int) $request->input('dsc_id', 0),
            'client_id' => (int) $request->input('client_id', 0) ?: null,
            'service_order_id' => (int) $request->input('service_order_id', 0) ?: null,
            'used_by' => (int) $request->input('used_by', 0) ?: null,
            'purpose' => trim((string) $request->input('purpose', '')),
            'portal_or_department' => trim((string) $request->input('portal_or_department', '')),
            'filing_reference' => trim((string) $request->input('filing_reference', '')),
            'acknowledgement_no' => trim((string) $request->input('acknowledgement_no', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];

        Session::flash('old', $payload);

        if ($payload['dsc_id'] <= 0 || $payload['purpose'] === '') {
            Session::flash('error', 'DSC and purpose are required.');
            redirect('/dsc/usage/create');
        }

        try {
            $this->dscRepo->createUsage($payload);
            Session::flash('success', 'DSC usage logged successfully.');
            redirect('/dsc/usage');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/dsc/usage/create');
        }
    }

    public function renewals(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = ['status' => $request->input('status', '')];

        $content = View::render(base_path('modules/DSC/views/renewals.php'), [
            'title' => 'DSC Renewals',
            'activeMenu' => 'dsc',
            'renewals' => $this->dscRepo->paginateRenewals($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateRenewal(Request $request): void
    {
        $id = (int) $request->input('renewal_id', 0);
        $payload = [
            'renewal_status' => strtoupper((string) $request->input('renewal_status', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
            'new_valid_from' => trim((string) $request->input('new_valid_from', '')),
            'new_valid_to' => trim((string) $request->input('new_valid_to', '')),
        ];

        try {
            $this->dscRepo->updateRenewal($id, $payload);
            Session::flash('success', 'Renewal status updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/dsc/renewals');
    }

    public function reports(Request $request): void
    {
        $filters = [
            'custody_status' => $request->input('custody_status', ''),
            'client_id' => $request->input('client_id', ''),
        ];

        $content = View::render(base_path('modules/DSC/views/reports.php'), [
            'title' => 'DSC Reports',
            'activeMenu' => 'dsc',
            'dscList' => $this->dscRepo->reportsData($filters),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    private function extractPayload(Request $request): array
    {
        return [
            'client_id' => (int) $request->input('client_id', 0) ?: null,
            'holder_name' => trim((string) $request->input('holder_name', '')),
            'holder_pan' => trim((string) $request->input('holder_pan', '')),
            'holder_email' => trim((string) $request->input('holder_email', '')),
            'holder_mobile' => trim((string) $request->input('holder_mobile', '')),
            'token_serial_no' => trim((string) $request->input('token_serial_no', '')),
            'dsc_type' => trim((string) $request->input('dsc_type', '')),
            'provider_name' => trim((string) $request->input('provider_name', '')),
            'valid_from' => trim((string) $request->input('valid_from', '')),
            'valid_to' => trim((string) $request->input('valid_to', '')),
            'custody_status' => (string) $request->input('custody_status', 'WITH_CLIENT'),
            'assigned_user_id' => (int) $request->input('assigned_user_id', 0) ?: null,
            'storage_location' => trim((string) $request->input('storage_location', '')),
            'password_status' => (string) $request->input('password_status', 'NOT_STORED'),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];
    }
}
