<?php

declare(strict_types=1);

namespace Modules\Search;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\SearchService;

final class SearchController
{
    public function __construct(
        private readonly SearchService $search = new SearchService()
    ) {
    }

    public function index(Request $request): void
    {
        $query = trim((string) $request->input('q', ''));
        $results = ['sources' => [], 'total' => 0];

        if ($query !== '') {
            $results = $this->search->globalSearch($query, 8);
            $this->search->logSearch($request, 'GLOBAL', $query, 'all', ['q' => $query], (int) ($results['total'] ?? 0));
        }

        Response::html(View::render(base_path('modules/Search/views/index.php'), [
            'title' => 'Enterprise Search',
            'activeMenu' => 'search',
            'query' => $query,
            'results' => $results,
            'options' => $this->search->options(),
        ]));
    }

    public function quick(Request $request): void
    {
        $query = trim((string) $request->input('q', ''));
        $results = ['sources' => [], 'total' => 0];

        if ($query !== '') {
            $results = $this->search->quickSearch($query, 4);
            $this->search->logSearch($request, 'QUICK', $query, 'all', ['q' => $query], (int) ($results['total'] ?? 0));
        }

        Response::html(View::render(base_path('modules/Search/views/quick.php'), [
            'title' => 'Quick Search',
            'activeMenu' => 'search',
            'query' => $query,
            'results' => $results,
            'options' => $this->search->options(),
        ]));
    }

    public function advanced(Request $request): void
    {
        $options = $this->search->options();
        $availableSources = array_keys($options['sources']);
        $defaultSource = $availableSources[0] ?? 'clients';
        $page = max(1, (int) $request->input('page', 1));

        $filters = [
            'source' => (string) $request->input('source', $defaultSource),
            'q' => trim((string) $request->input('q', '')),
            'pan' => trim((string) $request->input('pan', '')),
            'tan' => trim((string) $request->input('tan', '')),
            'gstin' => trim((string) $request->input('gstin', '')),
            'mobile' => trim((string) $request->input('mobile', '')),
            'portal_code' => trim((string) $request->input('portal_code', '')),
            'company_id' => (int) $request->input('company_id', 0),
            'service_type_id' => (int) $request->input('service_type_id', 0),
            'document_category' => trim((string) $request->input('document_category', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $hasCriteria = $filters['q'] !== ''
            || $filters['pan'] !== ''
            || $filters['tan'] !== ''
            || $filters['gstin'] !== ''
            || $filters['mobile'] !== ''
            || $filters['portal_code'] !== ''
            || $filters['company_id'] > 0
            || $filters['service_type_id'] > 0
            || $filters['document_category'] !== ''
            || $filters['date_from'] !== ''
            || $filters['date_to'] !== '';

        $report = [
            'items' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => 20,
            'total_pages' => 1,
            'source' => $filters['source'],
        ];

        if ($hasCriteria) {
            $report = $this->search->advancedSearch($filters, $page, 20);
            $this->search->logSearch(
                $request,
                'ADVANCED',
                $filters['q'],
                $filters['source'],
                $filters,
                (int) ($report['total'] ?? 0)
            );
        }

        Response::html(View::render(base_path('modules/Search/views/advanced.php'), [
            'title' => 'Advanced Search',
            'activeMenu' => 'search',
            'filters' => $filters,
            'options' => $options,
            'report' => $report,
            'hasCriteria' => $hasCriteria,
        ]));
    }

    public function history(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'mode' => trim((string) $request->input('mode', '')),
            'source' => trim((string) $request->input('source', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'user_id' => (int) $request->input('user_id', 0),
        ];

        Response::html(View::render(base_path('modules/Search/views/history.php'), [
            'title' => 'Search History',
            'activeMenu' => 'search',
            'filters' => $filters,
            'options' => $this->search->options(),
            'report' => $this->search->history($filters, $page, 20),
            'canAudit' => $this->search->canAuditHistory(),
        ]));
    }
}
