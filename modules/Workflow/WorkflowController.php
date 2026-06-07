<?php

declare(strict_types=1);

namespace Modules\Workflow;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Services\WorkflowService;
use Throwable;

final class WorkflowController
{
    public function __construct(
        private readonly WorkflowService $workflows = new WorkflowService()
    ) {
    }

    public function advance(Request $request): void
    {
        try {
            $this->workflows->advanceMilestone((int) $request->input('service_order_id', 0), (int) Auth::id());
            Session::flash('success', 'Workflow milestone advanced successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function recordPayment(Request $request): void
    {
        try {
            $this->workflows->recordTaxPayment(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('payment_reference_no', '')
            );
            Session::flash('success', 'Tax payment recorded and workflow advanced.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function captureAcknowledgement(Request $request): void
    {
        try {
            $this->workflows->captureAcknowledgement(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('acknowledgement_no', '')
            );
            Session::flash('success', 'Acknowledgement captured successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function markEVerificationDone(Request $request): void
    {
        try {
            $this->workflows->markEVerificationDone(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('note', '')
            );
            Session::flash('success', 'E-verification marked as done.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function completeProceduralClosure(Request $request): void
    {
        try {
            $this->workflows->completeProceduralClosure(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('note', '')
            );
            Session::flash('success', 'Procedural closure completed.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function completeAccountingClosure(Request $request): void
    {
        try {
            $this->workflows->completeAccountingClosure(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('note', '')
            );
            Session::flash('success', 'Accounting closure completed.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function completeFinalClosure(Request $request): void
    {
        try {
            $this->workflows->completeFinalClosure(
                (int) $request->input('service_order_id', 0),
                (int) Auth::id(),
                (string) $request->input('note', '')
            );
            Session::flash('success', 'Final closure completed and service order locked.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }

    public function logFollowUp(Request $request): void
    {
        try {
            $this->workflows->logEVerificationFollowUp(
                (int) $request->input('service_order_id', 0),
                (int) $request->input('reminder_id', 0),
                (int) Auth::id(),
                (string) $request->input('follow_up_note', '')
            );
            Session::flash('success', 'CRM follow-up logged.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/service-orders/show?id=' . (int) $request->input('service_order_id', 0));
    }
}
