<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Request;
use App\Repositories\ReportRepository;
use App\Repositories\SearchRepository;

final class SearchService
{
    public function __construct(
        private readonly SearchRepository $searches = new SearchRepository(),
        private readonly ReportRepository $reports = new ReportRepository()
    ) {
    }

    public function globalSearch(string $query, int $limitPerSource = 8): array
    {
        return $this->searches->globalSearch($this->context(), $query, $limitPerSource);
    }

    public function quickSearch(string $query, int $limitPerSource = 4): array
    {
        return $this->searches->quickSearch($this->context(), $query, $limitPerSource);
    }

    public function advancedSearch(array $filters, int $page = 1, int $perPage = 20): array
    {
        return $this->searches->advancedSearch($this->context(), $filters, $page, $perPage);
    }

    public function history(array $filters, int $page = 1, int $perPage = 20): array
    {
        return $this->searches->history($this->context(), $filters, $page, $perPage);
    }

    public function logSearch(Request $request, string $mode, string $query, string $sourceScope, array $filters, int $resultCount): void
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId <= 0) {
            return;
        }

        $this->searches->logSearch(
            $userId,
            $mode,
            trim($query),
            $sourceScope,
            $filters,
            $resultCount,
            $request->ip(),
            $request->userAgent()
        );
    }

    public function options(): array
    {
        return [
            'sources' => $this->allowedSources(),
            'companies' => $this->reports->filterOptions()['companies'] ?? [],
            'service_types' => $this->reports->filterOptions()['service_types'] ?? [],
            'portal_definitions' => ClientService::portalDefinitions(),
            'document_categories' => [
                'CLIENT_PAN_CARD_IMAGE' => 'Client PAN Card',
                'CLIENT_AADHAAR_CARD_IMAGE' => 'Client Aadhaar Card',
                'PSO_DOCUMENT' => 'PSO Document',
                'SO_SUPPORT' => 'Service Order Document',
                'CONSULTANT_DELIVERABLE' => 'Consultant Deliverable',
                'CONSULTANT_BILL' => 'Consultant Bill',
                'BILLING_PROOF' => 'Billing Proof',
                'GENERAL' => 'General',
            ],
        ];
    }

    public function canAuditHistory(): bool
    {
        return Auth::can('search.audit');
    }

    private function allowedSources(): array
    {
        $sources = [];

        if (Auth::canAny('clients.view', 'portal.self_access')) {
            $sources['clients'] = 'Clients';
        }
        if (Auth::can('service_orders.view') || Auth::can('portal.self_access') || Auth::isConsultantUser()) {
            $sources['service_orders'] = 'Service Orders';
        }
        if (Auth::canAny('clients.view', 'clients.credentials.manage')) {
            $sources['portal_credentials'] = 'Portal Credentials';
        }
        if (Auth::canAny('billing.view', 'reports.financial') || Auth::can('portal.self_access')) {
            $sources['invoices'] = 'Invoices';
            $sources['receipts'] = 'Receipts';
        }
        if (Auth::can('consultants.view') || Auth::isConsultantUser()) {
            $sources['consultants'] = 'Consultants';
        }
        if (Auth::can('documents.download') || Auth::can('portal.self_access') || Auth::isConsultantUser()) {
            $sources['documents'] = 'Documents';
        }

        return $sources;
    }

    private function context(): array
    {
        return [
            'user_id' => (int) (Auth::id() ?? 0),
            'client_id' => (int) (Auth::clientId() ?? 0),
            'actor_type' => Auth::actorType(),
            'permissions' => Auth::permissions(),
        ];
    }
}
