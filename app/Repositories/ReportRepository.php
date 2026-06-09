<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOStatement;

final class ReportRepository
{
    public function overviewCards(): array
    {
        return [
            'clients' => (int) Database::connection()->query("SELECT COUNT(*) FROM clients WHERE is_active = 1")->fetchColumn(),
            'service_orders' => (int) Database::connection()->query("SELECT COUNT(*) FROM service_orders WHERE archived_at IS NULL")->fetchColumn(),
            'invoices' => (int) Database::connection()->query("SELECT COUNT(*) FROM invoices WHERE accounting_status <> 'CANCELLED'")->fetchColumn(),
            'receipts' => (int) Database::connection()->query("SELECT COUNT(*) FROM receipts")->fetchColumn(),
            'outstanding_amount' => (float) Database::connection()->query(
                "SELECT COALESCE(SUM(outstanding_amount), 0)
                 FROM (
                    SELECT GREATEST(i.net_payable - COALESCE(SUM(pa.allocated_amount), 0), 0) AS outstanding_amount
                    FROM invoices i
                    LEFT JOIN payment_allocations pa ON pa.invoice_id = i.id
                    WHERE i.accounting_status <> 'CANCELLED'
                    GROUP BY i.id, i.net_payable
                 ) report_rows"
            )->fetchColumn(),
            'gst_orders' => (int) Database::connection()->query(
                "SELECT COUNT(*)
                 FROM service_orders so
                 INNER JOIN service_types st ON st.id = so.service_type_id
                 WHERE st.service_group = 'GST'
                   AND so.archived_at IS NULL"
            )->fetchColumn(),
            'revenue' => (float) Database::connection()->query(
                "SELECT COALESCE(SUM(i.net_payable), 0)
                 FROM invoices i
                 WHERE i.accounting_status <> 'CANCELLED'"
            )->fetchColumn(),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'companies' => $this->fetchPairs("SELECT id, display_name AS label FROM companies WHERE is_active = 1 ORDER BY display_name ASC"),
            'service_types' => $this->fetchPairs("SELECT id, name AS label FROM service_types WHERE is_active = 1 ORDER BY name ASC"),
            'financial_years' => $this->fetchPairs("SELECT id, label FROM financial_years WHERE is_active = 1 ORDER BY start_date DESC"),
            'crm_users' => $this->fetchPairs(
                "SELECT DISTINCT u.id, u.full_name AS label
                 FROM users u
                 INNER JOIN user_role_map urm ON urm.user_id = u.id
                 INNER JOIN roles r ON r.id = urm.role_id
                 WHERE r.code IN ('CRM', 'ASSISTANT_CRM', 'ADMIN', 'SUPER_ADMIN')
                   AND u.is_active = 1
                 ORDER BY u.full_name ASC"
            ),
        ];
    }

    public function clientRegister(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM clients c
            LEFT JOIN users crm ON crm.id = c.assigned_crm_id
            LEFT JOIN client_contacts cc ON cc.client_id = c.id AND cc.is_primary = 1
            WHERE 1 = 1";

        [$whereSql, $params] = $this->buildClientFilters($filters);

        $countSql = "SELECT COUNT(DISTINCT c.id) {$baseSql}{$whereSql}";
        $dataSql = "SELECT c.id,
                c.client_code,
                c.client_type,
                c.legal_name,
                c.trade_name,
                c.pan,
                c.tan,
                c.gstin,
                c.aadhaar_last4,
                c.email,
                c.mobile,
                c.city,
                c.state_name,
                c.is_active,
                c.onboarded_at,
                crm.full_name AS assigned_crm_name,
                cc.contact_name AS primary_contact_name,
                cc.mobile AS primary_contact_mobile,
                cc.email AS primary_contact_email
            {$baseSql}{$whereSql}
            GROUP BY c.id, c.client_code, c.client_type, c.legal_name, c.trade_name, c.pan, c.tan, c.gstin, c.aadhaar_last4,
                c.email, c.mobile, c.city, c.state_name, c.is_active, c.onboarded_at, crm.full_name, cc.contact_name, cc.mobile, cc.email";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY c.legal_name ASC');
    }

    public function serviceOrderRegister(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM service_orders so
            INNER JOIN clients c ON c.id = so.client_id
            INNER JOIN companies comp ON comp.id = so.company_id
            INNER JOIN financial_years fy ON fy.id = so.financial_year_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            LEFT JOIN users crm ON crm.id = so.assigned_crm_id
            LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
            LEFT JOIN service_order_closures procedural_closure
                ON procedural_closure.service_order_id = so.id
               AND procedural_closure.closure_type = 'PROCEDURAL'
            LEFT JOIN service_order_closures accounting_closure
                ON accounting_closure.service_order_id = so.id
               AND accounting_closure.closure_type = 'ACCOUNTING'
            LEFT JOIN service_order_closures final_closure
                ON final_closure.service_order_id = so.id
               AND final_closure.closure_type = 'FINAL'
            WHERE 1 = 1";

        [$whereSql, $params] = $this->buildServiceOrderFilters($filters);

        $countSql = "SELECT COUNT(*) {$baseSql}{$whereSql}";
        $dataSql = "SELECT so.id,
                so.so_no,
                so.title,
                so.current_stage_code,
                so.priority_level,
                so.work_basis,
                so.compliance_subtype,
                so.assessment_year,
                so.period_label,
                so.created_at,
                so.sla_due_at,
                c.legal_name AS client_name,
                c.pan,
                c.tan,
                c.mobile,
                comp.display_name AS company_name,
                fy.label AS financial_year_label,
                st.name AS service_type_name,
                st.service_group,
                crm.full_name AS assigned_crm_name,
                COALESCE(ssf.is_filing_done, 0) AS is_filing_done,
                COALESCE(ssf.is_acknowledgement_captured, 0) AS is_acknowledgement_captured,
                COALESCE(ssf.is_client_paid, 0) AS is_client_paid,
                procedural_closure.closure_status AS procedural_closure_status,
                accounting_closure.closure_status AS accounting_closure_status,
                final_closure.closure_status AS final_closure_status
            {$baseSql}{$whereSql}";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY so.id DESC');
    }

    public function psoRegister(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM pre_service_orders pso
            INNER JOIN clients c ON c.id = pso.client_id
            INNER JOIN companies comp ON comp.id = pso.company_id
            INNER JOIN service_types st ON st.id = pso.service_type_id
            LEFT JOIN client_contacts cc ON cc.id = pso.requested_by_contact_id
            LEFT JOIN service_orders so ON so.id = pso.converted_so_id
            WHERE 1 = 1";

        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                pso.pso_no LIKE :search_pso_no
                OR c.legal_name LIKE :search_client_name
                OR c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
            )";
            $params['search_pso_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
        }

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND pso.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND pso.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if (trim((string) ($filters['current_status'] ?? '')) !== '') {
            $where .= " AND pso.current_status = :current_status";
            $params['current_status'] = trim((string) $filters['current_status']);
        }

        [$where, $params] = $this->appendDateRange($where, $params, 'pso.submitted_at', $filters);

        $countSql = "SELECT COUNT(*) {$baseSql}{$where}";
        $dataSql = "SELECT pso.id,
                pso.pso_no,
                pso.title,
                pso.requested_for_period,
                pso.current_status,
                pso.submitted_at,
                pso.reviewed_at,
                c.legal_name AS client_name,
                c.pan,
                c.tan,
                st.name AS service_type_name,
                comp.display_name AS company_name,
                cc.contact_name AS requested_by_name,
                so.so_no AS converted_so_no
            {$baseSql}{$where}";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY pso.id DESC');
    }

    public function invoiceRegister(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM invoices i
            INNER JOIN clients c ON c.id = i.client_id
            INNER JOIN companies comp ON comp.id = i.company_id
            INNER JOIN financial_years fy ON fy.id = i.financial_year_id
            INNER JOIN service_orders so ON so.id = i.service_order_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            LEFT JOIN (
                SELECT invoice_id, COALESCE(SUM(allocated_amount), 0) AS allocated_total
                FROM payment_allocations
                GROUP BY invoice_id
            ) pa ON pa.invoice_id = i.id
            WHERE i.accounting_status <> 'CANCELLED'";

        [$whereSql, $params] = $this->buildInvoiceFilters($filters);

        $countSql = "SELECT COUNT(*) {$baseSql}{$whereSql}";
        $dataSql = "SELECT i.id,
                i.service_order_id,
                i.invoice_no,
                i.invoice_date,
                i.due_date,
                i.invoice_type,
                i.service_fee,
                i.disbursement_total,
                i.tax_total,
                i.gross_total,
                i.advance_adjusted,
                i.net_payable,
                i.payment_status,
                i.accounting_status,
                c.legal_name AS client_name,
                c.pan,
                comp.display_name AS company_name,
                fy.label AS financial_year_label,
                so.so_no,
                st.name AS service_type_name,
                COALESCE(pa.allocated_total, 0) AS allocated_total,
                GREATEST(i.net_payable - COALESCE(pa.allocated_total, 0), 0) AS outstanding_amount
            {$baseSql}{$whereSql}";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY i.invoice_date DESC, i.id DESC');
    }

    public function receiptRegister(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM receipts r
            INNER JOIN payments p ON p.id = r.payment_id
            INNER JOIN clients c ON c.id = r.client_id
            INNER JOIN companies comp ON comp.id = r.company_id
            LEFT JOIN service_orders so ON so.id = p.service_order_id
            LEFT JOIN (
                SELECT receipt_id, COUNT(*) AS item_count
                FROM payment_receipt_items
                GROUP BY receipt_id
            ) pri ON pri.receipt_id = r.id
            WHERE 1 = 1";

        [$whereSql, $params] = $this->buildReceiptFilters($filters);

        $countSql = "SELECT COUNT(*) {$baseSql}{$whereSql}";
        $dataSql = "SELECT r.id,
                r.receipt_no,
                r.receipt_date,
                r.receipt_amount,
                c.legal_name AS client_name,
                c.pan,
                comp.display_name AS company_name,
                p.payment_mode,
                p.transaction_type,
                p.reference_no,
                p.status AS payment_status,
                so.so_no,
                COALESCE(pri.item_count, 0) AS allocation_count
            {$baseSql}{$whereSql}";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY r.receipt_date DESC, r.id DESC');
    }

    public function outstandingReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM invoices i
            INNER JOIN clients c ON c.id = i.client_id
            INNER JOIN companies comp ON comp.id = i.company_id
            INNER JOIN service_orders so ON so.id = i.service_order_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            LEFT JOIN (
                SELECT invoice_id, COALESCE(SUM(allocated_amount), 0) AS allocated_total
                FROM payment_allocations
                GROUP BY invoice_id
            ) pa ON pa.invoice_id = i.id
            WHERE i.accounting_status <> 'CANCELLED'";

        [$whereSql, $params] = $this->buildOutstandingFilters($filters);
        $countSql = "SELECT COUNT(*)
            FROM (
                SELECT i.id,
                    GREATEST(i.net_payable - COALESCE(pa.allocated_total, 0), 0) AS outstanding_amount,
                    CASE
                        WHEN i.due_date IS NULL THEN 0
                        WHEN i.due_date < CURDATE() THEN DATEDIFF(CURDATE(), i.due_date)
                        ELSE 0
                    END AS due_days
                {$baseSql}{$whereSql}
            ) report_rows
            WHERE outstanding_amount > 0";

        if (($filters['overdue_only'] ?? '') === '1') {
            $countSql .= " AND due_days > 0";
        }

        $dataSql = "SELECT i.id,
                i.invoice_no,
                i.invoice_date,
                i.due_date,
                i.net_payable,
                i.payment_status,
                c.legal_name AS client_name,
                c.mobile,
                comp.display_name AS company_name,
                so.so_no,
                st.name AS service_type_name,
                COALESCE(pa.allocated_total, 0) AS collected_amount,
                GREATEST(i.net_payable - COALESCE(pa.allocated_total, 0), 0) AS outstanding_amount,
                CASE
                    WHEN i.due_date IS NULL THEN 0
                    WHEN i.due_date < CURDATE() THEN DATEDIFF(CURDATE(), i.due_date)
                    ELSE 0
                END AS due_days
            {$baseSql}{$whereSql}
            HAVING outstanding_amount > 0";

        if (($filters['overdue_only'] ?? '') === '1') {
            $dataSql .= " AND due_days > 0";
        }

        $rows = $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY due_days DESC, i.invoice_date ASC');
        $rows['summary'] = $this->outstandingSummary($filters);

        return $rows;
    }

    public function gstSummary(array $filters): array
    {
        $baseSql = "FROM service_orders so
            INNER JOIN service_types st ON st.id = so.service_type_id
            INNER JOIN companies comp ON comp.id = so.company_id
            LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
            LEFT JOIN (
                SELECT service_order_id, COALESCE(SUM(net_payable), 0) AS invoice_total
                FROM invoices
                WHERE accounting_status <> 'CANCELLED'
                GROUP BY service_order_id
            ) invoice_summary ON invoice_summary.service_order_id = so.id
            LEFT JOIN (
                SELECT service_order_id, COALESCE(SUM(amount), 0) AS paid_total
                FROM payments
                WHERE status = 'SUCCESS' AND transaction_type <> 'REFUND'
                GROUP BY service_order_id
            ) payments_summary ON payments_summary.service_order_id = so.id
            WHERE st.service_group = 'GST'
              AND so.archived_at IS NULL";

        [$whereSql, $params] = $this->buildGstFilters($filters);

        $summarySql = "SELECT
                COUNT(DISTINCT so.id) AS total_orders,
                COUNT(DISTINCT CASE WHEN so.current_stage_code = 'FILING_DONE' OR COALESCE(ssf.is_filing_done, 0) = 1 THEN so.id END) AS filing_done_orders,
                COUNT(DISTINCT CASE WHEN COALESCE(ssf.is_acknowledgement_captured, 0) = 1 THEN so.id END) AS acknowledgement_orders,
                COUNT(DISTINCT CASE WHEN COALESCE(ssf.is_paid, 0) = 1 THEN so.id END) AS payment_done_orders,
                COALESCE(SUM(COALESCE(invoice_summary.invoice_total, 0)), 0) AS billed_total,
                COALESCE(SUM(COALESCE(payments_summary.paid_total, 0)), 0) AS collected_total
            {$baseSql}{$whereSql}";

        $rowsSql = "SELECT
                comp.display_name AS company_name,
                st.name AS service_type_name,
                so.work_basis,
                COALESCE(so.period_label, CONCAT('Created ', DATE_FORMAT(so.created_at, '%Y-%m'))) AS report_period,
                COUNT(DISTINCT so.id) AS total_orders,
                COUNT(DISTINCT CASE WHEN so.current_stage_code = 'FILING_DONE' OR COALESCE(ssf.is_filing_done, 0) = 1 THEN so.id END) AS filing_done_orders,
                COUNT(DISTINCT CASE WHEN COALESCE(ssf.is_acknowledgement_captured, 0) = 1 THEN so.id END) AS acknowledgement_orders,
                COUNT(DISTINCT CASE WHEN COALESCE(ssf.is_paid, 0) = 1 THEN so.id END) AS payment_done_orders,
                COALESCE(SUM(COALESCE(invoice_summary.invoice_total, 0)), 0) AS billed_total,
                COALESCE(SUM(COALESCE(payments_summary.paid_total, 0)), 0) AS collected_total
            {$baseSql}{$whereSql}
            GROUP BY comp.display_name, st.name, so.work_basis, report_period
            ORDER BY report_period DESC, comp.display_name ASC, st.name ASC";

        $summaryStatement = Database::connection()->prepare($summarySql);
        $this->bindParams($summaryStatement, $params);
        $summaryStatement->execute();

        $rowsStatement = Database::connection()->prepare($rowsSql);
        $this->bindParams($rowsStatement, $params);
        $rowsStatement->execute();

        return [
            'summary' => $summaryStatement->fetch(PDO::FETCH_ASSOC) ?: [],
            'items' => $rowsStatement->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function revenueReport(array $filters): array
    {
        $baseSql = "FROM invoices i
            INNER JOIN companies comp ON comp.id = i.company_id
            INNER JOIN service_orders so ON so.id = i.service_order_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            LEFT JOIN (
                SELECT invoice_id, COALESCE(SUM(allocated_amount), 0) AS allocated_total
                FROM payment_allocations
                GROUP BY invoice_id
            ) collected ON collected.invoice_id = i.id
            WHERE i.accounting_status <> 'CANCELLED'";

        [$whereSql, $params] = $this->buildRevenueFilters($filters);

        $summarySql = "SELECT
                COUNT(*) AS invoice_count,
                COALESCE(SUM(i.gross_total), 0) AS gross_total,
                COALESCE(SUM(i.tax_total), 0) AS tax_total,
                COALESCE(SUM(i.net_payable), 0) AS net_total,
                COALESCE(SUM(COALESCE(collected.allocated_total, 0)), 0) AS collected_total,
                COALESCE(SUM(GREATEST(i.net_payable - COALESCE(collected.allocated_total, 0), 0)), 0) AS outstanding_total
            {$baseSql}{$whereSql}";

        $rowsSql = "SELECT
                DATE_FORMAT(i.invoice_date, '%Y-%m') AS revenue_month,
                comp.display_name AS company_name,
                st.name AS service_type_name,
                COUNT(*) AS invoice_count,
                COALESCE(SUM(i.gross_total), 0) AS gross_total,
                COALESCE(SUM(i.tax_total), 0) AS tax_total,
                COALESCE(SUM(i.net_payable), 0) AS net_total,
                COALESCE(SUM(COALESCE(collected.allocated_total, 0)), 0) AS collected_total,
                COALESCE(SUM(GREATEST(i.net_payable - COALESCE(collected.allocated_total, 0), 0)), 0) AS outstanding_total
            {$baseSql}{$whereSql}
            GROUP BY revenue_month, comp.display_name, st.name
            ORDER BY revenue_month DESC, comp.display_name ASC, st.name ASC";

        $summaryStatement = Database::connection()->prepare($summarySql);
        $this->bindParams($summaryStatement, $params);
        $summaryStatement->execute();

        $rowsStatement = Database::connection()->prepare($rowsSql);
        $this->bindParams($rowsStatement, $params);
        $rowsStatement->execute();

        return [
            'summary' => $summaryStatement->fetch(PDO::FETCH_ASSOC) ?: [],
            'items' => $rowsStatement->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function consultantReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM consultant_assignments ca
            INNER JOIN users consultant ON consultant.id = ca.consultant_user_id
            INNER JOIN service_orders so ON so.id = ca.service_order_id
            INNER JOIN clients c ON c.id = so.client_id
            INNER JOIN companies comp ON comp.id = so.company_id
            INNER JOIN service_types st ON st.id = so.service_type_id
            LEFT JOIN users reviewer ON reviewer.id = ca.internal_reviewer_id
            LEFT JOIN (
                SELECT consultant_assignment_id, COUNT(*) AS deliverable_count
                FROM consultant_deliverables
                GROUP BY consultant_assignment_id
            ) deliverables ON deliverables.consultant_assignment_id = ca.id
            LEFT JOIN (
                SELECT consultant_assignment_id, COUNT(*) AS bill_count, COALESCE(SUM(total_amount), 0) AS billed_total
                FROM consultant_bills
                GROUP BY consultant_assignment_id
            ) bills ON bills.consultant_assignment_id = ca.id
            LEFT JOIN (
                SELECT cb.consultant_assignment_id, COALESCE(SUM(cp.amount), 0) AS paid_total
                FROM consultant_payments cp
                INNER JOIN consultant_bills cb ON cb.id = cp.consultant_bill_id
                GROUP BY cb.consultant_assignment_id
            ) payments ON payments.consultant_assignment_id = ca.id
            WHERE 1 = 1";

        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                consultant.full_name LIKE :search_consultant_name
                OR c.legal_name LIKE :search_client_name
                OR so.so_no LIKE :search_so_no
            )";
            $params['search_consultant_name'] = $term;
            $params['search_client_name'] = $term;
            $params['search_so_no'] = $term;
        }

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND so.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND so.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $where .= " AND ca.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        [$where, $params] = $this->appendDateRange($where, $params, 'ca.assigned_at', $filters);

        $countSql = "SELECT COUNT(*) {$baseSql}{$where}";
        $dataSql = "SELECT ca.id,
                ca.service_order_id,
                ca.status,
                ca.assigned_at,
                consultant.full_name AS consultant_name,
                reviewer.full_name AS reviewer_name,
                so.so_no,
                c.legal_name AS client_name,
                comp.display_name AS company_name,
                st.name AS service_type_name,
                COALESCE(deliverables.deliverable_count, 0) AS deliverable_count,
                COALESCE(bills.bill_count, 0) AS bill_count,
                COALESCE(bills.billed_total, 0) AS billed_total,
                COALESCE(payments.paid_total, 0) AS paid_total
            {$baseSql}{$where}";

        return $this->paginate($countSql, $dataSql, $params, $page, $perPage, ' ORDER BY ca.id DESC');
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

    private function fetchPairs(string $sql): array
    {
        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildClientFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                c.legal_name LIKE :search_legal_name
                OR c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
                OR c.gstin LIKE :search_gstin
                OR c.mobile LIKE :search_mobile
            )";
            $params['search_legal_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
            $params['search_gstin'] = $term;
            $params['search_mobile'] = $term;
        }

        if ((int) ($filters['crm_id'] ?? 0) > 0) {
            $where .= " AND c.assigned_crm_id = :crm_id";
            $params['crm_id'] = (int) $filters['crm_id'];
        }

        $status = (string) ($filters['status'] ?? 'active');
        if ($status === 'active') {
            $where .= " AND c.is_active = 1";
        } elseif ($status === 'archived') {
            $where .= " AND c.is_active = 0";
        }

        return [$where, $params];
    }

    private function buildServiceOrderFilters(array $filters): array
    {
        $where = ' AND so.archived_at IS NULL';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                so.so_no LIKE :search_so_no
                OR c.legal_name LIKE :search_client_name
                OR c.pan LIKE :search_pan
                OR c.tan LIKE :search_tan
            )";
            $params['search_so_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_pan'] = $term;
            $params['search_tan'] = $term;
        }

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND so.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND so.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if ((int) ($filters['financial_year_id'] ?? 0) > 0) {
            $where .= " AND so.financial_year_id = :financial_year_id";
            $params['financial_year_id'] = (int) $filters['financial_year_id'];
        }
        if (trim((string) ($filters['stage_code'] ?? '')) !== '') {
            $where .= " AND so.current_stage_code = :stage_code";
            $params['stage_code'] = trim((string) $filters['stage_code']);
        }
        if (trim((string) ($filters['work_basis'] ?? '')) !== '') {
            $where .= " AND so.work_basis = :work_basis";
            $params['work_basis'] = trim((string) $filters['work_basis']);
        }

        return $this->appendDateRange($where, $params, 'so.created_at', $filters);
    }

    private function buildInvoiceFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                i.invoice_no LIKE :search_invoice_no
                OR so.so_no LIKE :search_so_no
                OR c.legal_name LIKE :search_client_name
                OR c.pan LIKE :search_pan
            )";
            $params['search_invoice_no'] = $term;
            $params['search_so_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_pan'] = $term;
        }
        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND i.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if (trim((string) ($filters['payment_status'] ?? '')) !== '') {
            $where .= " AND i.payment_status = :payment_status";
            $params['payment_status'] = trim((string) $filters['payment_status']);
        }
        if (trim((string) ($filters['invoice_type'] ?? '')) !== '') {
            $where .= " AND i.invoice_type = :invoice_type";
            $params['invoice_type'] = trim((string) $filters['invoice_type']);
        }

        return $this->appendDateRange($where, $params, 'i.invoice_date', $filters);
    }

    private function buildReceiptFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                r.receipt_no LIKE :search_receipt_no
                OR so.so_no LIKE :search_so_no
                OR c.legal_name LIKE :search_client_name
                OR p.reference_no LIKE :search_reference_no
            )";
            $params['search_receipt_no'] = $term;
            $params['search_so_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_reference_no'] = $term;
        }
        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND r.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if (trim((string) ($filters['payment_mode'] ?? '')) !== '') {
            $where .= " AND p.payment_mode = :payment_mode";
            $params['payment_mode'] = trim((string) $filters['payment_mode']);
        }

        return $this->appendDateRange($where, $params, 'r.receipt_date', $filters);
    }

    private function buildOutstandingFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                i.invoice_no LIKE :search_invoice_no
                OR so.so_no LIKE :search_so_no
                OR c.legal_name LIKE :search_client_name
                OR c.mobile LIKE :search_mobile
            )";
            $params['search_invoice_no'] = $term;
            $params['search_so_no'] = $term;
            $params['search_client_name'] = $term;
            $params['search_mobile'] = $term;
        }
        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND i.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if (trim((string) ($filters['payment_status'] ?? '')) !== '') {
            $where .= " AND i.payment_status = :payment_status";
            $params['payment_status'] = trim((string) $filters['payment_status']);
        }

        return $this->appendDateRange($where, $params, 'i.invoice_date', $filters);
    }

    private function buildGstFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND so.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if (trim((string) ($filters['work_basis'] ?? '')) !== '') {
            $where .= " AND so.work_basis = :work_basis";
            $params['work_basis'] = trim((string) $filters['work_basis']);
        }
        if (trim((string) ($filters['period_year'] ?? '')) !== '') {
            $where .= " AND so.period_year = :period_year";
            $params['period_year'] = trim((string) $filters['period_year']);
        }

        return $this->appendDateRange($where, $params, 'so.created_at', $filters);
    }

    private function buildRevenueFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if ((int) ($filters['company_id'] ?? 0) > 0) {
            $where .= " AND i.company_id = :company_id";
            $params['company_id'] = (int) $filters['company_id'];
        }
        if ((int) ($filters['service_type_id'] ?? 0) > 0) {
            $where .= " AND so.service_type_id = :service_type_id";
            $params['service_type_id'] = (int) $filters['service_type_id'];
        }
        if ((int) ($filters['financial_year_id'] ?? 0) > 0) {
            $where .= " AND i.financial_year_id = :financial_year_id";
            $params['financial_year_id'] = (int) $filters['financial_year_id'];
        }

        return $this->appendDateRange($where, $params, 'i.invoice_date', $filters);
    }

    private function appendDateRange(string $where, array $params, string $column, array $filters): array
    {
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        if ($dateFrom !== '') {
            $where .= " AND DATE({$column}) >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= " AND DATE({$column}) <= :date_to";
            $params['date_to'] = $dateTo;
        }

        return [$where, $params];
    }

    private function outstandingSummary(array $filters): array
    {
        $baseSql = "FROM invoices i
            LEFT JOIN (
                SELECT invoice_id, COALESCE(SUM(allocated_amount), 0) AS allocated_total
                FROM payment_allocations
                GROUP BY invoice_id
            ) pa ON pa.invoice_id = i.id
            WHERE i.accounting_status <> 'CANCELLED'";

        [$whereSql, $params] = $this->buildOutstandingFilters($filters);
        $sql = "SELECT
                COUNT(*) AS invoice_count,
                COALESCE(SUM(i.net_payable), 0) AS invoiced_total,
                COALESCE(SUM(COALESCE(pa.allocated_total, 0)), 0) AS collected_total,
                COALESCE(SUM(GREATEST(i.net_payable - COALESCE(pa.allocated_total, 0), 0)), 0) AS outstanding_total
            {$baseSql}{$whereSql}";

        $statement = Database::connection()->prepare($sql);
        $this->bindParams($statement, $params);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
