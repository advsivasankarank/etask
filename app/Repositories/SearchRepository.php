<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOStatement;

final class SearchRepository
{
    public function quickSearch(array $context, string $query, int $limitPerSource = 4): array
    {
        return $this->multiSourceSearch($context, ['q' => $query], max(1, $limitPerSource));
    }

    public function globalSearch(array $context, string $query, int $limitPerSource = 8): array
    {
        return $this->multiSourceSearch($context, ['q' => $query], max(1, $limitPerSource));
    }

    public function advancedSearch(array $context, array $filters, int $page = 1, int $perPage = 20): array
    {
        $source = strtolower(trim((string) ($filters['source'] ?? 'clients')));
        if ($source === '' || !$this->canAccessSource($context, $source)) {
            return [
                'source' => $source,
                'items' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $perPage,
                'total_pages' => 1,
            ];
        }

        $search = $this->runSourceSearch($source, $context, $filters, $page, $perPage);
        $search['source'] = $source;

        return $search;
    }

    public function history(array $context, array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM search_history sh
            INNER JOIN users u ON u.id = sh.user_id
            WHERE 1 = 1";

        $whereSql = '';
        $params = [];

        if (!$this->hasPermission($context, 'search.audit')) {
            $whereSql .= " AND sh.user_id = :current_user_id";
            $params['current_user_id'] = (int) ($context['user_id'] ?? 0);
        } elseif ((int) ($filters['user_id'] ?? 0) > 0) {
            $whereSql .= " AND sh.user_id = :filter_user_id";
            $params['filter_user_id'] = (int) $filters['user_id'];
        }

        if (trim((string) ($filters['q'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['q']) . '%';
            $whereSql .= " AND (
                sh.query_text LIKE :search_query
                OR sh.source_scope LIKE :search_source_scope
                OR u.full_name LIKE :search_user_name
            )";
            $params['search_query'] = $term;
            $params['search_source_scope'] = $term;
            $params['search_user_name'] = $term;
        }

        if (trim((string) ($filters['mode'] ?? '')) !== '') {
            $whereSql .= " AND sh.search_mode = :search_mode";
            $params['search_mode'] = strtoupper(trim((string) $filters['mode']));
        }

        if (trim((string) ($filters['source'] ?? '')) !== '') {
            $whereSql .= " AND sh.source_scope LIKE :source_filter";
            $params['source_filter'] = '%' . strtolower(trim((string) $filters['source'])) . '%';
        }

        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $whereSql .= " AND DATE(sh.created_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }

        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $whereSql .= " AND DATE(sh.created_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $this->bindParams($countStatement, $params);
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT sh.id,
                    sh.search_mode,
                    sh.query_text,
                    sh.source_scope,
                    sh.result_count,
                    sh.ip_address,
                    sh.created_at,
                    u.full_name AS user_name
             {$baseSql}{$whereSql}
             ORDER BY sh.id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindParams($dataStatement, $params);
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function recentSearches(array $context, int $limit = 6): array
    {
        $limit = max(1, min(20, $limit));
        $statement = Database::connection()->prepare(
            "SELECT sh.query_text,
                    sh.search_mode,
                    sh.source_scope,
                    sh.result_count,
                    sh.created_at
             FROM search_history sh
             WHERE sh.user_id = :user_id
               AND sh.query_text <> ''
             ORDER BY sh.id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':user_id', (int) ($context['user_id'] ?? 0), PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logSearch(
        int $userId,
        string $mode,
        string $query,
        string $sourceScope,
        array $filters,
        int $resultCount,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        $historyStatement = Database::connection()->prepare(
            "INSERT INTO search_history (
                user_id, search_mode, query_text, source_scope, filters_json, result_count, ip_address, user_agent, created_at
             ) VALUES (
                :user_id, :search_mode, :query_text, :source_scope, :filters_json, :result_count, :ip_address, :user_agent, NOW()
             )"
        );
        $historyStatement->execute([
            'user_id' => $userId,
            'search_mode' => strtoupper($mode),
            'query_text' => $query,
            'source_scope' => strtolower($sourceScope),
            'filters_json' => json_encode($filters, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'result_count' => $resultCount,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
        $historyId = (int) Database::connection()->lastInsertId();

        $activityStatement = Database::connection()->prepare(
            "INSERT INTO activity_logs (
                user_id, module_code, action_code, entity_type, entity_id, description, ip_address, user_agent, created_at
             ) VALUES (
                :user_id, 'SEARCH', :action_code, 'search_history', :entity_id, :description, :ip_address, :user_agent, NOW()
             )"
        );
        $activityStatement->execute([
            'user_id' => $userId,
            'action_code' => strtoupper($mode) . '_SEARCH',
            'entity_id' => $historyId,
            'description' => sprintf(
                'Search executed. Mode: %s; Scope: %s; Query: %s; Results: %d',
                strtoupper($mode),
                strtolower($sourceScope),
                $query !== '' ? $query : '[no-query]',
                $resultCount
            ),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    private function multiSourceSearch(array $context, array $filters, int $limitPerSource): array
    {
        $sources = [];
        $total = 0;

        foreach ($this->allSources() as $source => $label) {
            if (!$this->canAccessSource($context, $source)) {
                continue;
            }

            $result = $this->runSourceSearch($source, $context, $filters, 1, $limitPerSource);
            $sources[$source] = [
                'label' => $label,
                'items' => $result['items'],
                'total' => $result['total'],
            ];
            $total += (int) $result['total'];
        }

        return [
            'sources' => $sources,
            'total' => $total,
        ];
    }

    private function runSourceSearch(string $source, array $context, array $filters, int $page, int $perPage): array
    {
        return match ($source) {
            'clients' => $this->searchClients($context, $filters, $page, $perPage),
            'service_orders' => $this->searchServiceOrders($context, $filters, $page, $perPage),
            'portal_users' => $this->searchPortalUsers($context, $filters, $page, $perPage),
            'portal_credentials' => $this->searchPortalCredentials($context, $filters, $page, $perPage),
            'invoices' => $this->searchInvoices($context, $filters, $page, $perPage),
            'receipts' => $this->searchReceipts($context, $filters, $page, $perPage),
            'consultants' => $this->searchConsultants($context, $filters, $page, $perPage),
            'documents' => $this->searchDocuments($context, $filters, $page, $perPage),
            default => [
                'items' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $perPage,
                'total_pages' => 1,
            ],
        };
    }

    private function searchClients(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'clients')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM clients c
            LEFT JOIN users crm ON crm.id = c.assigned_crm_id
            WHERE 1 = 1";
        $where = '';
        $params = [];

        if ($this->isClientUser($context)) {
            $where .= " AND c.id = :scope_client_id";
            $params['scope_client_id'] = (int) ($context['client_id'] ?? 0);
        }

        if (!$this->isInternalUser($context) && !$this->isClientUser($context)) {
            return $this->emptyPage($perPage);
        }

        $this->appendClientTextFilters($where, $params, $filters, 'c');

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT c.id,
                    c.client_code,
                    c.legal_name,
                    c.trade_name,
                    c.pan,
                    c.tan,
                    c.gstin,
                    c.email,
                    c.mobile,
                    c.is_active,
                    crm.full_name AS assigned_crm_name
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY c.legal_name ASC'
        );
    }

    private function searchServiceOrders(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'service_orders')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM service_orders so
            INNER JOIN clients c ON c.id = so.client_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            INNER JOIN companies comp ON comp.id = so.company_id
            WHERE 1 = 1";
        $where = '';
        $params = [];

        $this->appendServiceOrderScope($context, $where, $params);
        $this->appendServiceOrderFilters($where, $params, $filters);

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT so.id,
                    so.so_no,
                    so.title,
                    so.current_stage_code,
                    so.work_basis,
                    so.assessment_year,
                    so.period_label,
                    so.created_at,
                    c.client_code,
                    c.trade_name,
                    c.gstin,
                    c.mobile,
                    c.email,
                    c.legal_name AS client_name,
                    c.pan,
                    c.tan,
                    comp.display_name AS company_name,
                    st.name AS service_type_name
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY so.id DESC'
        );
    }

    private function searchPortalCredentials(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'portal_credentials')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM client_portal_credentials cpc
            INNER JOIN clients c ON c.id = cpc.client_id
            WHERE cpc.is_active = 1";
        $where = '';
        $params = [];

        if ($this->isClientUser($context) || $this->isConsultantUser($context)) {
            return $this->emptyPage($perPage);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                c.legal_name LIKE :search_client_name
                OR c.pan LIKE :search_pan
                OR cpc.portal_label LIKE :search_portal_label
                OR cpc.user_identifier LIKE :search_user_identifier
            )";
            $params['search_client_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_portal_label'] = $term;
            $params['search_user_identifier'] = $term;
        }

        if (trim((string) ($filters['portal_code'] ?? '')) !== '') {
            $where .= " AND cpc.portal_code = :portal_code";
            $params['portal_code'] = trim((string) $filters['portal_code']);
        }

        if (trim((string) ($filters['pan'] ?? '')) !== '') {
            $where .= " AND c.pan LIKE :filter_pan";
            $params['filter_pan'] = '%' . trim((string) $filters['pan']) . '%';
        }

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT cpc.id,
                    cpc.client_id,
                    cpc.portal_code,
                    cpc.portal_label,
                    cpc.user_identifier,
                    cpc.updated_at,
                    c.legal_name AS client_name,
                    c.pan
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY c.legal_name ASC, cpc.portal_label ASC'
        );
    }

    private function searchPortalUsers(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'portal_users')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM users u
            INNER JOIN client_contacts cc ON cc.id = u.client_contact_id
            INNER JOIN clients c ON c.id = cc.client_id
            WHERE EXISTS (
                SELECT 1
                FROM user_role_map urm
                INNER JOIN roles r ON r.id = urm.role_id
                WHERE urm.user_id = u.id
                  AND r.code = 'CLIENT'
            )";
        $where = '';
        $params = [];

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                u.username LIKE :search_username
                OR u.full_name LIKE :search_full_name
                OR u.email LIKE :search_email
                OR u.mobile LIKE :search_mobile
                OR c.legal_name LIKE :search_client_name
                OR c.trade_name LIKE :search_trade_name
                OR c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
                OR c.gstin LIKE :search_gstin
            )";
            $params['search_username'] = $term;
            $params['search_full_name'] = $term;
            $params['search_email'] = $term;
            $params['search_mobile'] = $term;
            $params['search_client_name'] = $term;
            $params['search_trade_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
            $params['search_gstin'] = $term;
        }

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT u.id,
                    u.username,
                    u.full_name,
                    u.email,
                    u.mobile,
                    u.is_active,
                    c.id AS client_id,
                    c.legal_name AS client_name,
                    c.pan,
                    c.tan,
                    c.gstin
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY u.full_name ASC, u.id DESC'
        );
    }

    private function searchInvoices(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'invoices')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM invoices i
            INNER JOIN clients c ON c.id = i.client_id
            INNER JOIN service_orders so ON so.id = i.service_order_id
            INNER JOIN companies comp ON comp.id = i.company_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            WHERE i.accounting_status <> 'CANCELLED'";
        $where = '';
        $params = [];

        if ($this->isClientUser($context)) {
            $where .= " AND i.client_id = :scope_client_id";
            $params['scope_client_id'] = (int) ($context['client_id'] ?? 0);
        } elseif ($this->isConsultantUser($context)) {
            return $this->emptyPage($perPage);
        }

        $this->appendInvoiceFilters($where, $params, $filters);

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT i.id,
                    i.service_order_id,
                    i.invoice_no,
                    i.invoice_date,
                    i.net_payable,
                    i.payment_status,
                    c.legal_name AS client_name,
                    c.pan,
                    so.so_no,
                    comp.display_name AS company_name,
                    st.name AS service_type_name
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY i.invoice_date DESC, i.id DESC'
        );
    }

    private function searchReceipts(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'receipts')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM receipts r
            INNER JOIN payments p ON p.id = r.payment_id
            INNER JOIN clients c ON c.id = r.client_id
            LEFT JOIN service_orders so ON so.id = p.service_order_id
            WHERE 1 = 1";
        $where = '';
        $params = [];

        if ($this->isClientUser($context)) {
            $where .= " AND r.client_id = :scope_client_id";
            $params['scope_client_id'] = (int) ($context['client_id'] ?? 0);
        } elseif ($this->isConsultantUser($context)) {
            return $this->emptyPage($perPage);
        }

        $this->appendReceiptFilters($where, $params, $filters);

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT r.id,
                    r.receipt_no,
                    r.receipt_date,
                    r.receipt_amount,
                    c.legal_name AS client_name,
                    so.so_no,
                    p.payment_mode,
                    p.reference_no,
                    COALESCE(so.id, 0) AS service_order_id
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY r.receipt_date DESC, r.id DESC'
        );
    }

    private function searchConsultants(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'consultants')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM consultant_assignments ca
            INNER JOIN users u ON u.id = ca.consultant_user_id
            INNER JOIN service_orders so ON so.id = ca.service_order_id
            INNER JOIN clients c ON c.id = so.client_id
            WHERE 1 = 1";
        $where = '';
        $params = [];

        if ($this->isConsultantUser($context)) {
            $where .= " AND ca.consultant_user_id = :scope_user_id";
            $params['scope_user_id'] = (int) ($context['user_id'] ?? 0);
        } elseif ($this->isClientUser($context)) {
            return $this->emptyPage($perPage);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                u.full_name LIKE :search_consultant_name
                OR u.email LIKE :search_consultant_email
                OR c.legal_name LIKE :search_client_name
                OR so.so_no LIKE :search_so_no
            )";
            $params['search_consultant_name'] = $term;
            $params['search_consultant_email'] = $term;
            $params['search_client_name'] = $term;
            $params['search_so_no'] = $term;
        }

        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(ca.assigned_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(ca.assigned_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT ca.id,
                    ca.service_order_id,
                    ca.status,
                    ca.assigned_at,
                    u.id AS consultant_user_id,
                    u.full_name AS consultant_name,
                    u.email,
                    c.legal_name AS client_name,
                    so.so_no
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY ca.id DESC'
        );
    }

    private function searchDocuments(array $context, array $filters, int $page, int $perPage): array
    {
        if (!$this->canAccessSource($context, 'documents')) {
            return $this->emptyPage($perPage);
        }

        $baseSql = "FROM documents d
            INNER JOIN clients c ON c.id = d.client_id
            LEFT JOIN consultant_assignments ca
                ON d.linked_module = 'CONSULTANT'
               AND ca.id = d.linked_id
            LEFT JOIN service_orders so
                ON d.linked_module = 'SO'
               AND so.id = d.linked_id
            WHERE d.is_active = 1";
        $where = '';
        $params = [];

        if ($this->isClientUser($context)) {
            $where .= " AND d.client_id = :scope_client_id
                        AND d.linked_module IN ('CLIENT', 'PSO', 'SO', 'BILLING')";
            $params['scope_client_id'] = (int) ($context['client_id'] ?? 0);
        } elseif ($this->isConsultantUser($context)) {
            $where .= " AND d.linked_module = 'CONSULTANT'
                        AND ca.consultant_user_id = :scope_user_id";
            $params['scope_user_id'] = (int) ($context['user_id'] ?? 0);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                d.document_name LIKE :search_document_name
                OR d.latest_file_name LIKE :search_file_name
                OR c.legal_name LIKE :search_client_name
            )";
            $params['search_document_name'] = $term;
            $params['search_file_name'] = $term;
            $params['search_client_name'] = $term;
        }

        if (trim((string) ($filters['document_category'] ?? '')) !== '') {
            $where .= " AND d.document_category = :document_category";
            $params['document_category'] = trim((string) $filters['document_category']);
        }
        if (trim((string) ($filters['pan'] ?? '')) !== '') {
            $where .= " AND c.pan LIKE :filter_pan";
            $params['filter_pan'] = '%' . trim((string) $filters['pan']) . '%';
        }
        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(d.uploaded_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(d.uploaded_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$where}",
            "SELECT d.id,
                    d.client_id,
                    d.linked_module,
                    d.linked_id,
                    d.document_category,
                    d.document_name,
                    d.latest_file_name,
                    d.uploaded_at,
                    ca.service_order_id AS consultant_service_order_id,
                    c.legal_name AS client_name
             {$baseSql}{$where}",
            $params,
            $page,
            $perPage,
            ' ORDER BY d.id DESC'
        );
    }

    private function paginate(string $countSql, string $dataSql, array $params, int $page, int $perPage, string $orderBy): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countStatement = Database::connection()->prepare($countSql);
        $this->bindParams($countStatement, $params);
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $statement = Database::connection()->prepare($dataSql . $orderBy . ' LIMIT :limit OFFSET :offset');
        $this->bindParams($statement, $params);
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    private function bindParams(PDOStatement $statement, array $params): void
    {
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    private function appendClientTextFilters(string &$where, array &$params, array $filters, string $alias): void
    {
        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                {$alias}.legal_name LIKE :search_legal_name
                OR {$alias}.trade_name LIKE :search_trade_name
                OR {$alias}.client_code LIKE :search_client_code
                OR {$alias}.pan LIKE :search_pan
                OR {$alias}.tan LIKE :search_tan
                OR {$alias}.gstin LIKE :search_gstin
                OR {$alias}.mobile LIKE :search_mobile
                OR {$alias}.email LIKE :search_email
            )";
            $params['search_legal_name'] = $term;
            $params['search_trade_name'] = $term;
            $params['search_client_code'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
            $params['search_gstin'] = $term;
            $params['search_mobile'] = $term;
            $params['search_email'] = $term;
        }

        foreach (['pan', 'tan', 'gstin', 'mobile'] as $field) {
            if (trim((string) ($filters[$field] ?? '')) !== '') {
                $where .= " AND {$alias}.{$field} LIKE :filter_{$field}";
                $params['filter_' . $field] = '%' . trim((string) $filters[$field]) . '%';
            }
        }
    }

    private function appendServiceOrderScope(array $context, string &$where, array &$params): void
    {
        if ($this->isClientUser($context)) {
            $where .= " AND so.client_id = :scope_client_id";
            $params['scope_client_id'] = (int) ($context['client_id'] ?? 0);
            return;
        }

        if ($this->isConsultantUser($context)) {
            $where .= " AND EXISTS (
                SELECT 1
                FROM consultant_assignments sca
                WHERE sca.service_order_id = so.id
                  AND sca.consultant_user_id = :scope_user_id
            )";
            $params['scope_user_id'] = (int) ($context['user_id'] ?? 0);
        }
    }

    private function appendServiceOrderFilters(string &$where, array &$params, array $filters): void
    {
        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                so.so_no LIKE :search_so_no
                OR so.title LIKE :search_title
                OR st.name LIKE :search_service_type_name
                OR c.legal_name LIKE :search_client_name
                OR c.trade_name LIKE :search_trade_name
                OR c.client_code LIKE :search_client_code
                OR c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
                OR c.gstin LIKE :search_gstin
                OR c.mobile LIKE :search_mobile
                OR c.email LIKE :search_email
            )";
            $params['search_so_no'] = $term;
            $params['search_title'] = $term;
            $params['search_service_type_name'] = $term;
            $params['search_client_name'] = $term;
            $params['search_trade_name'] = $term;
            $params['search_client_code'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
            $params['search_gstin'] = $term;
            $params['search_mobile'] = $term;
            $params['search_email'] = $term;
        }

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND so.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND so.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if (trim((string) ($filters['pan'] ?? '')) !== '') {
            $where .= " AND c.pan LIKE :filter_pan";
            $params['filter_pan'] = '%' . trim((string) $filters['pan']) . '%';
        }
        if (trim((string) ($filters['tan'] ?? '')) !== '') {
            $where .= " AND c.tan LIKE :filter_tan";
            $params['filter_tan'] = '%' . trim((string) $filters['tan']) . '%';
        }
        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(so.created_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(so.created_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }
    }

    private function appendInvoiceFilters(string &$where, array &$params, array $filters): void
    {
        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                i.invoice_no LIKE :search_invoice_no
                OR c.legal_name LIKE :search_client_name
                OR c.pan LIKE :search_pan
                OR so.so_no LIKE :search_so_no
            )";
            $params['search_invoice_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_so_no'] = $term;
        }

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND i.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND so.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if (trim((string) ($filters['pan'] ?? '')) !== '') {
            $where .= " AND c.pan LIKE :filter_pan";
            $params['filter_pan'] = '%' . trim((string) $filters['pan']) . '%';
        }
        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(i.invoice_date) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(i.invoice_date) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }
    }

    private function appendReceiptFilters(string &$where, array &$params, array $filters): void
    {
        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $term = '%' . $keyword . '%';
            $where .= " AND (
                r.receipt_no LIKE :search_receipt_no
                OR c.legal_name LIKE :search_client_name
                OR so.so_no LIKE :search_so_no
                OR p.reference_no LIKE :search_reference_no
            )";
            $params['search_receipt_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_so_no'] = $term;
            $params['search_reference_no'] = $term;
        }

        if (trim((string) ($filters['pan'] ?? '')) !== '') {
            $where .= " AND c.pan LIKE :filter_pan";
            $params['filter_pan'] = '%' . trim((string) $filters['pan']) . '%';
        }
        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(r.receipt_date) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(r.receipt_date) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }
    }

    private function canAccessSource(array $context, string $source): bool
    {
        return match ($source) {
            'clients' => $this->hasPermission($context, 'clients.view') || $this->hasPermission($context, 'portal.self_access'),
            'service_orders' => $this->hasPermission($context, 'service_orders.view') || $this->isClientUser($context) || $this->isConsultantUser($context),
            'portal_users' => $this->hasPermissionAny($context, ['users.manage.portal', 'users.manage.internal', 'clients.view']),
            'portal_credentials' => $this->hasPermissionAny($context, ['clients.view', 'clients.credentials.manage']),
            'invoices', 'receipts' => $this->hasPermissionAny($context, ['billing.view', 'reports.financial']) || $this->isClientUser($context),
            'consultants' => $this->hasPermission($context, 'consultants.view') || $this->isConsultantUser($context),
            'documents' => $this->hasPermission($context, 'documents.download') || $this->hasPermission($context, 'portal.self_access') || $this->isConsultantUser($context),
            default => false,
        };
    }

    private function allSources(): array
    {
        return [
            'clients' => 'Clients',
            'service_orders' => 'Service Orders',
            'portal_users' => 'Portal Users',
            'portal_credentials' => 'Portal Credentials',
            'invoices' => 'Invoices',
            'receipts' => 'Receipts',
            'consultants' => 'Consultants',
            'documents' => 'Documents',
        ];
    }

    private function hasPermission(array $context, string $permission): bool
    {
        return in_array($permission, $context['permissions'] ?? [], true);
    }

    private function hasPermissionAny(array $context, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($context, $permission)) {
                return true;
            }
        }

        return false;
    }

    private function isClientUser(array $context): bool
    {
        return strtoupper((string) ($context['actor_type'] ?? '')) === 'PORTAL' || $this->hasPermission($context, 'portal.self_access');
    }

    private function isConsultantUser(array $context): bool
    {
        return strtoupper((string) ($context['actor_type'] ?? '')) === 'CONSULTANT';
    }

    private function isInternalUser(array $context): bool
    {
        return !$this->isClientUser($context) && !$this->isConsultantUser($context);
    }

    private function emptyPage(int $perPage): array
    {
        return [
            'items' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => $perPage,
            'total_pages' => 1,
        ];
    }
}
