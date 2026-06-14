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

    public function universalResults(string $query, int $limitPerSource = 5): array
    {
        $results = $this->globalSearch($query, $limitPerSource);
        return $this->flattenResults($results, $query);
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

    public function recentSearches(int $limit = 6): array
    {
        return $this->searches->recentSearches($this->context(), $limit);
    }

    public function quickAccess(): array
    {
        $items = [];

        if (Auth::can('clients.view')) {
            $items[] = ['label' => 'Clients', 'description' => 'Open client workspace', 'url' => url('/clients'), 'icon' => 'CLIENT'];
        }
        if (Auth::can('service_orders.view') || Auth::can('portal.self_access')) {
            $items[] = ['label' => 'Service Orders', 'description' => 'Open active workspaces', 'url' => url('/service-orders'), 'icon' => 'SERVICE'];
        }
        if (Auth::canAny('billing.view', 'reports.financial') || Auth::can('portal.self_access')) {
            $items[] = ['label' => 'Billing', 'description' => 'Open invoices and receipts', 'url' => url('/billing'), 'icon' => 'BILLING'];
        }
        if (Auth::can('documents.download') || Auth::can('portal.self_access')) {
            $items[] = ['label' => 'Documents', 'description' => 'Open document search workspace', 'url' => url('/search/advanced?source=documents'), 'icon' => 'DOCUMENT'];
        }
        if (Auth::can('search.history') || Auth::can('search.audit')) {
            $items[] = ['label' => 'Recent Searches', 'description' => 'Open detailed search history', 'url' => url('/search/history'), 'icon' => 'HISTORY'];
        }

        return $items;
    }

    public function cardsForSource(string $sourceKey, array $rows, string $query = ''): array
    {
        $items = [];

        foreach ($rows as $row) {
            $card = $this->toResultCard($sourceKey, $row);
            if ($card === null) {
                continue;
            }

            $card['score'] = $this->scoreResult($card, $query);
            $items[] = $card;
        }

        usort($items, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp((string) $a['title'], (string) $b['title']));

        return $items;
    }

    public function recentRecords(int $limit = 5): array
    {
        $records = [];
        $seen = [];

        foreach ($this->recentSearches(8) as $entry) {
            $query = trim((string) ($entry['query_text'] ?? ''));
            if ($query === '') {
                continue;
            }

            $flattened = $this->universalResults($query, 2);
            foreach (($flattened['items'] ?? []) as $item) {
                $key = (string) ($item['url'] ?? '');
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $records[] = $item;
                if (count($records) >= $limit) {
                    return $records;
                }
            }
        }

        return $records;
    }

    public function commandPaletteData(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [
                'query' => '',
                'items' => [],
                'groups' => [],
                'recent_searches' => $this->recentSearches(6),
                'recent_records' => $this->recentRecords(5),
                'quick_access' => $this->quickAccess(),
            ];
        }

        $results = $this->universalResults($query, 4);
        return [
            'query' => $query,
            'items' => $results['items'],
            'groups' => $results['groups'],
            'recent_searches' => [],
            'recent_records' => [],
            'quick_access' => [],
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
        if (Auth::canAny('users.manage.portal', 'users.manage.internal', 'clients.view')) {
            $sources['portal_users'] = 'Portal Users';
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

    private function flattenResults(array $results, string $query): array
    {
        $items = [];
        $groups = [];

        foreach (($results['sources'] ?? []) as $sourceKey => $sourceResults) {
            $cards = $this->cardsForSource($sourceKey, $sourceResults['items'] ?? [], $query);
            foreach ($cards as $card) {
                $items[] = $card;
                $groups[$sourceKey]['label'] = $sourceResults['label'] ?? ucfirst(str_replace('_', ' ', $sourceKey));
                $groups[$sourceKey]['items'][] = $card;
            }
        }

        usort($items, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp((string) $a['title'], (string) $b['title']));
        foreach ($groups as &$group) {
            usort($group['items'], static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp((string) $a['title'], (string) $b['title']));
        }
        unset($group);

        return [
            'items' => $items,
            'groups' => $groups,
            'total' => count($items),
        ];
    }

    private function toResultCard(string $sourceKey, array $row): ?array
    {
        return match ($sourceKey) {
            'clients' => [
                'type' => 'CLIENT',
                'source' => $sourceKey,
                'title' => (string) ($row['legal_name'] ?? 'Client'),
                'subtitle' => trim((string) (($row['trade_name'] ?? '') !== '' ? $row['trade_name'] : ($row['client_code'] ?? ''))),
                'meta' => array_filter([
                    ($row['pan'] ?? '') !== '' ? 'PAN ' . $row['pan'] : '',
                    ($row['gstin'] ?? '') !== '' ? 'GSTIN ' . $row['gstin'] : '',
                    ($row['mobile'] ?? '') !== '' ? 'Mobile ' . $row['mobile'] : '',
                ]),
                'badge' => !empty($row['is_active']) ? 'Active' : 'Archived',
                'action_label' => 'Open Client',
                'url' => url('/clients/show?id=' . $row['id']),
                'search_blob' => implode(' ', [(string) ($row['legal_name'] ?? ''), (string) ($row['trade_name'] ?? ''), (string) ($row['client_code'] ?? ''), (string) ($row['pan'] ?? ''), (string) ($row['gstin'] ?? '')]),
            ],
            'service_orders' => [
                'type' => 'SERVICE ORDER',
                'source' => $sourceKey,
                'title' => (string) ($row['so_no'] ?? 'Service Order'),
                'subtitle' => (string) (($row['title'] ?? '') !== '' ? $row['title'] : ($row['service_type_name'] ?? 'Service')),
                'meta' => array_filter([
                    (string) ($row['client_name'] ?? ''),
                    (string) ($row['service_type_name'] ?? ''),
                    (string) ($row['period_label'] ?? ($row['assessment_year'] ?? '')),
                ]),
                'badge' => $this->humanizeStage((string) ($row['current_stage_code'] ?? 'IN_PROGRESS')),
                'action_label' => 'Open Workspace',
                'url' => url('/service-orders/show?id=' . $row['id']),
                'search_blob' => implode(' ', [
                    (string) ($row['so_no'] ?? ''),
                    (string) ($row['title'] ?? ''),
                    (string) ($row['service_type_name'] ?? ''),
                    (string) ($row['client_name'] ?? ''),
                    (string) ($row['trade_name'] ?? ''),
                    (string) ($row['client_code'] ?? ''),
                    (string) ($row['pan'] ?? ''),
                    (string) ($row['gstin'] ?? ''),
                    (string) ($row['mobile'] ?? ''),
                ]),
            ],
            'portal_users' => [
                'type' => 'PORTAL USER',
                'source' => $sourceKey,
                'title' => (string) ($row['username'] ?? 'Portal User'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    (string) ($row['full_name'] ?? ''),
                    ($row['pan'] ?? '') !== '' ? 'PAN ' . $row['pan'] : '',
                    ($row['mobile'] ?? '') !== '' ? 'Mobile ' . $row['mobile'] : '',
                ]),
                'badge' => !empty($row['is_active']) ? 'Active' : 'Archived',
                'action_label' => 'Open User',
                'url' => url('/users/show?id=' . $row['id']),
                'search_blob' => implode(' ', [(string) ($row['username'] ?? ''), (string) ($row['full_name'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['pan'] ?? ''), (string) ($row['tan'] ?? '')]),
            ],
            'portal_credentials' => [
                'type' => 'PORTAL ACCESS',
                'source' => $sourceKey,
                'title' => (string) ($row['portal_label'] ?? 'Portal Credential'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    ($row['user_identifier'] ?? '') !== '' ? 'User ID ' . $row['user_identifier'] : '',
                    ($row['pan'] ?? '') !== '' ? 'PAN ' . $row['pan'] : '',
                ]),
                'badge' => 'Credential',
                'action_label' => 'Open Client',
                'url' => url('/clients/credentials?id=' . $row['client_id']),
                'search_blob' => implode(' ', [(string) ($row['portal_label'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['user_identifier'] ?? ''), (string) ($row['pan'] ?? '')]),
            ],
            'invoices' => [
                'type' => 'INVOICE',
                'source' => $sourceKey,
                'title' => (string) ($row['invoice_no'] ?? 'Invoice'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    (string) ($row['so_no'] ?? ''),
                    'Outstanding ' . number_format((float) ($row['net_payable'] ?? 0), 2),
                ]),
                'badge' => (string) ($row['payment_status'] ?? 'Open'),
                'action_label' => 'Open Invoice',
                'url' => url('/billing/invoice?id=' . $row['id']),
                'search_blob' => implode(' ', [(string) ($row['invoice_no'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['so_no'] ?? ''), (string) ($row['pan'] ?? '')]),
            ],
            'receipts' => [
                'type' => 'RECEIPT',
                'source' => $sourceKey,
                'title' => (string) ($row['receipt_no'] ?? 'Receipt'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    (string) ($row['so_no'] ?? ''),
                    'Amount ' . number_format((float) ($row['receipt_amount'] ?? 0), 2),
                ]),
                'badge' => (string) ($row['payment_mode'] ?? 'Receipt'),
                'action_label' => 'Open Receipt',
                'url' => url('/billing/receipt?id=' . $row['id']),
                'search_blob' => implode(' ', [(string) ($row['receipt_no'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['so_no'] ?? ''), (string) ($row['reference_no'] ?? '')]),
            ],
            'consultants' => [
                'type' => 'CONSULTANT',
                'source' => $sourceKey,
                'title' => (string) ($row['consultant_name'] ?? 'Consultant'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    (string) ($row['email'] ?? ''),
                    (string) ($row['so_no'] ?? ''),
                ]),
                'badge' => (string) ($row['status'] ?? 'Assigned'),
                'action_label' => 'Open Consultant',
                'url' => url('/consultants/show?service_order_id=' . $row['service_order_id']),
                'search_blob' => implode(' ', [(string) ($row['consultant_name'] ?? ''), (string) ($row['email'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['so_no'] ?? '')]),
            ],
            'documents' => [
                'type' => 'DOCUMENT',
                'source' => $sourceKey,
                'title' => (string) ($row['document_name'] ?? 'Document'),
                'subtitle' => (string) ($row['client_name'] ?? ''),
                'meta' => array_filter([
                    (string) ($row['document_category'] ?? ''),
                    (string) ($row['latest_file_name'] ?? ''),
                ]),
                'badge' => (string) ($row['linked_module'] ?? 'Document'),
                'action_label' => 'Open Document',
                'url' => url('/documents/show?id=' . $row['id']),
                'search_blob' => implode(' ', [(string) ($row['document_name'] ?? ''), (string) ($row['client_name'] ?? ''), (string) ($row['document_category'] ?? ''), (string) ($row['latest_file_name'] ?? '')]),
            ],
            default => null,
        };
    }

    private function scoreResult(array $card, string $query): int
    {
        $needle = strtolower(trim($query));
        $blob = strtolower(trim((string) ($card['search_blob'] ?? '')));
        $title = strtolower(trim((string) ($card['title'] ?? '')));
        $subtitle = strtolower(trim((string) ($card['subtitle'] ?? '')));
        $score = 0;

        if ($needle === '') {
            return $score;
        }

        if ($title === $needle) {
            $score += 120;
        } elseif (str_starts_with($title, $needle)) {
            $score += 90;
        } elseif (str_contains($title, $needle)) {
            $score += 70;
        }

        if ($subtitle !== '') {
            if ($subtitle === $needle) {
                $score += 80;
            } elseif (str_starts_with($subtitle, $needle)) {
                $score += 60;
            } elseif (str_contains($subtitle, $needle)) {
                $score += 40;
            }
        }

        if (str_contains($blob, $needle)) {
            $score += 20;
        }

        return $score;
    }

    private function humanizeStage(string $stageCode): string
    {
        $label = ucwords(strtolower(str_replace('_', ' ', trim($stageCode))));
        return $label !== '' ? $label : 'In Progress';
    }
}
