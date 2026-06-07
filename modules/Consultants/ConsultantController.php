<?php

declare(strict_types=1);

namespace Modules\Consultants;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ConsultantRepository;
use App\Services\ConsultantService;
use Throwable;

final class ConsultantController
{
    public function __construct(
        private readonly ConsultantRepository $consultants = new ConsultantRepository(),
        private readonly ConsultantService $consultantService = new ConsultantService()
    ) {
    }

    public function index(): void
    {
        $content = View::render(base_path('modules/Consultants/views/index.php'), [
            'title' => 'Consultants',
            'activeMenu' => 'consultants',
            'orders' => $this->consultants->serviceOrdersForConsultants(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function show(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);
        $dashboard = $this->consultantService->dashboard($serviceOrderId);

        $content = View::render(base_path('modules/Consultants/views/show.php'), [
            'title' => 'Consultant Workspace',
            'activeMenu' => 'consultants',
            'dashboard' => $dashboard,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function assign(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->assign($request->all(), (int) Auth::id());
            Session::flash('success', 'Consultant assigned successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }

    public function uploadDeliverable(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->uploadDeliverable(
                (int) $request->input('consultant_assignment_id', 0),
                $request->file('deliverable') ?? [],
                (int) Auth::id()
            );
            Session::flash('success', 'Consultant deliverable uploaded.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }

    public function reviewDeliverable(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->reviewDeliverable(
                (int) $request->input('deliverable_id', 0),
                (int) $request->input('consultant_assignment_id', 0),
                (int) Auth::id(),
                (string) $request->input('review_status', 'APPROVED'),
                trim((string) $request->input('review_notes', '')) ?: null
            );
            Session::flash('success', 'Deliverable review recorded.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }

    public function createBill(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->createBill($request->all(), $request->file('bill_document') ?? [], (int) Auth::id());
            Session::flash('success', 'Consultant bill added.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }

    public function reviewBill(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->reviewBill(
                (int) $request->input('consultant_bill_id', 0),
                (int) Auth::id(),
                (string) $request->input('review_status', 'APPROVED'),
                trim((string) $request->input('review_notes', '')) ?: null
            );
            Session::flash('success', 'Consultant bill review saved.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }

    public function recordPayment(Request $request): void
    {
        $serviceOrderId = (int) $request->input('service_order_id', 0);

        try {
            $this->consultantService->recordPayment($request->all(), (int) Auth::id());
            Session::flash('success', 'Consultant payment recorded.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/consultants/show?service_order_id=' . $serviceOrderId);
    }
}
