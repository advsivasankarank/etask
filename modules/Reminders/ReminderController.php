<?php

declare(strict_types=1);

namespace Modules\Reminders;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\ReminderService;
use Throwable;

final class ReminderController
{
    private ReminderService $reminders;

    public function __construct()
    {
        $this->reminders = new ReminderService();
    }

    public function index(): void
    {
        Response::html(View::render(base_path('modules/Reminders/views/index.php'), [
            'title' => 'Reminders',
            'activeMenu' => 'reminders',
            'overview' => $this->reminders->overview(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]));
    }

    public function templates(): void
    {
        Response::html(View::render(base_path('modules/Reminders/views/templates.php'), [
            'title' => 'Reminder Templates',
            'activeMenu' => 'reminders',
            'templates' => $this->reminders->overview()['templates'],
        ]));
    }

    public function templateForm(Request $request): void
    {
        $templateId = (int) $request->input('id', 0);
        $template = null;
        foreach ($this->reminders->overview()['templates'] as $row) {
            if ((int) $row['id'] === $templateId) {
                $template = $row;
                break;
            }
        }

        Response::html(View::render(base_path('modules/Reminders/views/template_form.php'), [
            'title' => $template === null ? 'Create Reminder Template' : 'Edit Reminder Template',
            'activeMenu' => 'reminders',
            'template' => $template,
            'options' => $this->reminders->templateOptions(),
            'old' => Session::pullFlash('old_template', []),
            'error' => Session::pullFlash('error'),
        ]));
    }

    public function saveTemplate(Request $request): void
    {
        Session::flash('old_template', $request->all());

        try {
            $id = $this->reminders->saveTemplate($request->all());
            Session::flash('success', 'Reminder template saved successfully.');
            redirect('/reminders/templates/form?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/reminders/templates/form' . (((int) $request->input('id', 0)) > 0 ? '?id=' . (int) $request->input('id', 0) : ''));
        }
    }

    public function escalations(): void
    {
        Response::html(View::render(base_path('modules/Reminders/views/escalations.php'), [
            'title' => 'Escalation Rules',
            'activeMenu' => 'reminders',
            'rules' => $this->reminders->overview()['escalation_rules'],
        ]));
    }

    public function escalationForm(Request $request): void
    {
        $ruleId = (int) $request->input('id', 0);
        $rule = null;
        foreach ($this->reminders->overview()['escalation_rules'] as $row) {
            if ((int) $row['id'] === $ruleId) {
                $rule = $row;
                break;
            }
        }

        Response::html(View::render(base_path('modules/Reminders/views/escalation_form.php'), [
            'title' => $rule === null ? 'Create Escalation Rule' : 'Edit Escalation Rule',
            'activeMenu' => 'reminders',
            'rule' => $rule,
            'options' => $this->reminders->escalationOptions(),
            'old' => Session::pullFlash('old_rule', []),
            'error' => Session::pullFlash('error'),
        ]));
    }

    public function saveEscalation(Request $request): void
    {
        Session::flash('old_rule', $request->all());

        try {
            $id = $this->reminders->saveEscalationRule($request->all());
            Session::flash('success', 'Escalation rule saved successfully.');
            redirect('/reminders/escalations/form?id=' . $id);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/reminders/escalations/form' . (((int) $request->input('id', 0)) > 0 ? '?id=' . (int) $request->input('id', 0) : ''));
        }
    }

    public function register(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = $this->reportFilters($request);

        Response::html(View::render(base_path('modules/Reminders/views/register.php'), [
            'title' => 'Reminder Register',
            'activeMenu' => 'reminders',
            'filters' => $filters,
            'options' => $this->reminders->templateOptions(),
            'report' => $this->reminders->register($filters, $page),
        ]));
    }

    public function pending(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = $this->reportFilters($request);

        Response::html(View::render(base_path('modules/Reminders/views/pending.php'), [
            'title' => 'Pending Reminder Report',
            'activeMenu' => 'reminders',
            'filters' => $filters,
            'options' => $this->reminders->templateOptions(),
            'report' => $this->reminders->pendingReport($filters, $page),
        ]));
    }

    public function effectiveness(Request $request): void
    {
        $filters = $this->reportFilters($request);

        Response::html(View::render(base_path('modules/Reminders/views/effectiveness.php'), [
            'title' => 'Reminder Effectiveness Report',
            'activeMenu' => 'reminders',
            'filters' => $filters,
            'options' => $this->reminders->templateOptions(),
            'report' => $this->reminders->effectivenessReport($filters),
        ]));
    }

    public function escalationReport(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = $this->reportFilters($request);

        Response::html(View::render(base_path('modules/Reminders/views/escalation_report.php'), [
            'title' => 'Escalation Report',
            'activeMenu' => 'reminders',
            'filters' => $filters,
            'options' => $this->reminders->templateOptions(),
            'report' => $this->reminders->escalationReport($filters, $page),
        ]));
    }

    public function runScheduler(): void
    {
        try {
            $result = $this->reminders->runScheduler();
            Session::flash('success', 'Reminder scheduler completed. Created: ' . $result['created'] . ', Triggered: ' . $result['triggered'] . ', Escalated: ' . $result['escalated'] . '.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/reminders');
    }

    private function reportFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'reminder_type' => trim((string) $request->input('reminder_type', '')),
            'status' => trim((string) $request->input('status', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];
    }
}
