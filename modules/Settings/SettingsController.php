<?php

declare(strict_types=1);

namespace Modules\Settings;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\SettingsRepository;
use Throwable;

final class SettingsController
{
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    public function index(): void
    {
        $content = View::render(base_path('modules/Settings/views/index.php'), [
            'title' => 'Settings',
            'activeMenu' => 'settings',
            'summary' => $this->settings->summaryCounts(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function company(): void
    {
        $company = $this->settings->getCompany();
        $content = View::render(base_path('modules/Settings/views/company.php'), [
            'title' => 'Company Settings',
            'activeMenu' => 'settings',
            'company' => $company,
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateCompany(Request $request): void
    {
        $payload = [
            'legal_name' => trim((string) $request->input('legal_name', '')),
            'display_name' => trim((string) $request->input('display_name', '')),
            'pan' => trim((string) $request->input('pan', '')),
            'gstin' => trim((string) $request->input('gstin', '')),
            'tan' => trim((string) $request->input('tan', '')),
            'email' => trim((string) $request->input('email', '')),
            'mobile' => trim((string) $request->input('mobile', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'address_line1' => trim((string) $request->input('address_line1', '')),
            'address_line2' => trim((string) $request->input('address_line2', '')),
            'city' => trim((string) $request->input('city', '')),
            'state_name' => trim((string) $request->input('state_name', '')),
            'postal_code' => trim((string) $request->input('postal_code', '')),
        ];

        Session::flash('old', $payload);

        try {
            $company = $this->settings->getCompany();
            if ($company !== null) {
                $this->settings->updateCompany($payload, (int) $company['id']);
            }
            $this->settings->logMaintenance('COMPANY_UPDATED', 'Company settings updated', (int) Auth::id());
            Session::flash('success', 'Company settings updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/company');
    }

    public function serviceTypes(): void
    {
        $content = View::render(base_path('modules/Settings/views/service_types.php'), [
            'title' => 'Service Type Reference',
            'activeMenu' => 'settings',
            'serviceTypes' => $this->settings->allServiceTypes(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function workflow(): void
    {
        $content = View::render(base_path('modules/Settings/views/workflow.php'), [
            'title' => 'Workflow Settings',
            'activeMenu' => 'settings',
            'settings' => [
                'reopen_requires_reason' => $this->settings->getSetting('workflow', 'reopen_requires_reason', '1'),
                'reminder_warnings_enabled' => $this->settings->getSetting('workflow', 'reminder_warnings_enabled', '1'),
            ],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateWorkflow(Request $request): void
    {
        try {
            $this->settings->saveSetting('workflow', 'reopen_requires_reason', (string) $request->input('reopen_requires_reason', '1'), (int) Auth::id());
            $this->settings->saveSetting('workflow', 'reminder_warnings_enabled', (string) $request->input('reminder_warnings_enabled', '1'), (int) Auth::id());
            $this->settings->logMaintenance('WORKFLOW_UPDATED', 'Workflow settings updated', (int) Auth::id());
            Session::flash('success', 'Workflow settings updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/workflow');
    }

    public function milestones(): void
    {
        $content = View::render(base_path('modules/Settings/views/milestones.php'), [
            'title' => 'Milestone Reference',
            'activeMenu' => 'settings',
            'milestones' => $this->settings->allMilestones(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function reminderTemplates(): void
    {
        $content = View::render(base_path('modules/Settings/views/reminder_templates.php'), [
            'title' => 'Reminder Template Reference',
            'activeMenu' => 'settings',
            'templates' => $this->settings->allReminderTemplates(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function numbering(): void
    {
        $content = View::render(base_path('modules/Settings/views/numbering.php'), [
            'title' => 'Numbering Settings',
            'activeMenu' => 'settings',
            'settings' => [
                'client_prefix' => $this->settings->getSetting('numbering', 'client_prefix', 'CL'),
                'so_prefix' => $this->settings->getSetting('numbering', 'so_prefix', 'SO'),
                'document_prefix' => $this->settings->getSetting('numbering', 'document_prefix', 'DOC'),
                'dsc_prefix' => $this->settings->getSetting('numbering', 'dsc_prefix', 'DSC'),
            ],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateNumbering(Request $request): void
    {
        try {
            $this->settings->saveSetting('numbering', 'client_prefix', trim((string) $request->input('client_prefix', 'CL')), (int) Auth::id());
            $this->settings->saveSetting('numbering', 'so_prefix', trim((string) $request->input('so_prefix', 'SO')), (int) Auth::id());
            $this->settings->saveSetting('numbering', 'document_prefix', trim((string) $request->input('document_prefix', 'DOC')), (int) Auth::id());
            $this->settings->saveSetting('numbering', 'dsc_prefix', trim((string) $request->input('dsc_prefix', 'DSC')), (int) Auth::id());
            $this->settings->logMaintenance('NUMBERING_UPDATED', 'Numbering settings updated', (int) Auth::id());
            Session::flash('success', 'Numbering settings updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/numbering');
    }

    public function roleDefaults(): void
    {
        $content = View::render(base_path('modules/Settings/views/role_defaults.php'), [
            'title' => 'Role Access Reference',
            'activeMenu' => 'settings',
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function documentCategories(): void
    {
        $content = View::render(base_path('modules/Settings/views/document_categories.php'), [
            'title' => 'Document Category Reference',
            'activeMenu' => 'settings',
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function dscCategories(): void
    {
        $content = View::render(base_path('modules/Settings/views/dsc_categories.php'), [
            'title' => 'DSC Category Reference',
            'activeMenu' => 'settings',
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function notifications(): void
    {
        $content = View::render(base_path('modules/Settings/views/notifications.php'), [
            'title' => 'Notification Settings',
            'activeMenu' => 'settings',
            'settings' => [
                'email_enabled' => $this->settings->getSetting('notifications', 'email_enabled', '0'),
                'sms_enabled' => $this->settings->getSetting('notifications', 'sms_enabled', '0'),
                'portal_enabled' => $this->settings->getSetting('notifications', 'portal_enabled', '1'),
            ],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateNotifications(Request $request): void
    {
        try {
            $this->settings->saveSetting('notifications', 'email_enabled', (string) $request->input('email_enabled', '0'), (int) Auth::id());
            $this->settings->saveSetting('notifications', 'sms_enabled', (string) $request->input('sms_enabled', '0'), (int) Auth::id());
            $this->settings->saveSetting('notifications', 'portal_enabled', (string) $request->input('portal_enabled', '1'), (int) Auth::id());
            $this->settings->logMaintenance('NOTIFICATIONS_UPDATED', 'Notification settings updated', (int) Auth::id());
            Session::flash('success', 'Notification settings updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/notifications');
    }

    public function security(): void
    {
        $content = View::render(base_path('modules/Settings/views/security.php'), [
            'title' => 'Security Settings',
            'activeMenu' => 'settings',
            'settings' => [
                'password_policy' => $this->settings->getSetting('security', 'password_policy', 'Minimum 8 characters'),
                'session_timeout' => $this->settings->getSetting('security', 'session_timeout', '120'),
                'audit_logging' => $this->settings->getSetting('security', 'audit_logging', '1'),
            ],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function updateSecurity(Request $request): void
    {
        try {
            $this->settings->saveSetting('security', 'password_policy', trim((string) $request->input('password_policy', 'Minimum 8 characters')), (int) Auth::id());
            $this->settings->saveSetting('security', 'session_timeout', trim((string) $request->input('session_timeout', '120')), (int) Auth::id());
            $this->settings->saveSetting('security', 'audit_logging', (string) $request->input('audit_logging', '1'), (int) Auth::id());
            $this->settings->logMaintenance('SECURITY_UPDATED', 'Security settings updated', (int) Auth::id());
            Session::flash('success', 'Security settings updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/security');
    }

    public function maintenance(): void
    {
        $content = View::render(base_path('modules/Settings/views/maintenance.php'), [
            'title' => 'Backup / Maintenance',
            'activeMenu' => 'settings',
            'logs' => $this->settings->recentMaintenanceLogs(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function logMaintenance(Request $request): void
    {
        $actionType = trim((string) $request->input('action_type', 'MANUAL_NOTE'));
        $actionNote = trim((string) $request->input('action_note', ''));

        try {
            $this->settings->logMaintenance($actionType, $actionNote ?: null, (int) Auth::id());
            Session::flash('success', 'Maintenance note recorded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/settings/maintenance');
    }
}
