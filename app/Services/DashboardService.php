<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Repositories\DashboardRepository;

final class DashboardService
{
    private DashboardRepository $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardRepository();
    }

    public function buildForCurrentUser(): array
    {
        $user = Auth::user() ?? [];
        $userId = (int) ($user['id'] ?? 0);
        $clientId = (int) ($user['client_id'] ?? 0);
        if (Auth::can('dashboard.admin')) {
            return [
                'persona' => 'Admin',
                'metrics' => $this->dashboard->adminMetrics(),
                'heroStats' => $this->dashboard->adminHeroStats(),
                'queues' => $this->dashboard->adminQueues(),
                'stageBreakdown' => $this->dashboard->adminStageBreakdown(),
                'creationTrend' => $this->dashboard->adminCreationTrend(14),
                'complianceDueThisWeek' => $this->dashboard->adminComplianceDueThisWeek(),
                'documentsAwaitingReview' => $this->dashboard->adminDocumentsAwaitingReview(),
                'upcomingDeadlines' => $this->dashboard->adminUpcomingDeadlines(3),
                'notifications' => $this->dashboard->dashboardNotifications($userId, null),
            ];
        }

        if (Auth::can('dashboard.crm')) {
            return [
                'persona' => 'CRM',
                'metrics' => $this->dashboard->crmMetrics($userId),
                'queues' => $this->dashboard->crmQueues($userId),
                'notifications' => $this->dashboard->dashboardNotifications($userId, null),
            ];
        }

        if (Auth::can('dashboard.accounts')) {
            return [
                'persona' => 'Accounts',
                'metrics' => $this->dashboard->accountsMetrics(),
                'queues' => $this->dashboard->accountsQueues(),
                'notifications' => $this->dashboard->dashboardNotifications($userId, null),
            ];
        }

        if (Auth::can('dashboard.consultant')) {
            return [
                'persona' => 'Consultant',
                'metrics' => $this->dashboard->consultantMetrics($userId),
                'queues' => $this->dashboard->consultantQueues($userId),
                'notifications' => $this->dashboard->dashboardNotifications($userId, null),
            ];
        }

        if (Auth::can('dashboard.client')) {
            return [
                'persona' => 'Client',
                'metrics' => $this->dashboard->clientMetrics($clientId),
                'queues' => $this->dashboard->clientQueues($clientId),
                'notifications' => $this->dashboard->dashboardNotifications(null, (int) ($user['client_contact_id'] ?? 0)),
            ];
        }

        return [
            'persona' => 'General',
            'metrics' => [
                'service_orders' => 0,
                'open_tasks' => 0,
            ],
            'queues' => [],
            'notifications' => $this->dashboard->dashboardNotifications($userId, null),
        ];
    }
}
