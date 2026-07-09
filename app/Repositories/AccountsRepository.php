<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AccountsRepository
{
    public function summaryCounts(): array
    {
        return [
            'total_invoiced' => $this->scalar("SELECT COALESCE(SUM(net_payable), 0) FROM invoices WHERE accounting_status != 'CANCELLED'"),
            'total_received' => $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'SUCCESS' AND transaction_type = 'INVOICE_PAYMENT'"),
            'outstanding' => $this->scalar("SELECT COALESCE(SUM(net_payable), 0) FROM invoices WHERE payment_status IN ('UNPAID','PARTIALLY_PAID') AND accounting_status != 'CANCELLED'") - $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'SUCCESS' AND transaction_type = 'INVOICE_PAYMENT'"),
            'overdue_amount' => $this->scalar("SELECT COALESCE(SUM(i.net_payable), 0) FROM invoices i WHERE i.payment_status IN ('UNPAID','PARTIALLY_PAID') AND i.accounting_status != 'CANCELLED' AND i.due_date < CURDATE()"),
            'due_today' => $this->scalar("SELECT COUNT(*) FROM invoices WHERE payment_status IN ('UNPAID','PARTIALLY_PAID') AND accounting_status != 'CANCELLED' AND due_date = CURDATE()"),
            'unbilled_completed' => $this->scalar("SELECT COUNT(*) FROM service_orders so WHERE so.final_closed_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM invoices i WHERE i.service_order_id = so.id AND i.accounting_status != 'CANCELLED')"),
            'consultant_payables' => $this->scalar("SELECT COALESCE(SUM(cb.total_amount), 0) FROM consultant_bills cb WHERE cb.review_status = 'APPROVED' AND NOT EXISTS (SELECT 1 FROM consultant_payments cp WHERE cp.consultant_bill_id = cb.id)"),
            'recent_receipts' => $this->scalar("SELECT COUNT(*) FROM receipts WHERE receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"),
        ];
    }

    public function paginateInvoices(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM invoices i
            LEFT JOIN clients c ON c.id = i.client_id
            LEFT JOIN service_orders so ON so.id = i.service_order_id
            WHERE i.accounting_status != 'CANCELLED'";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (i.invoice_no LIKE :search OR c.legal_name LIKE :search OR so.so_no LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['payment_status'] ?? '')) !== '') {
            $whereSql .= " AND i.payment_status = :payment_status";
            $params['payment_status'] = trim((string) $filters['payment_status']);
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND i.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT i.*, c.legal_name AS client_name, so.so_no
             {$baseSql}{$whereSql}
             ORDER BY i.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
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

    public function paginateReceipts(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM receipts r
            LEFT JOIN clients c ON c.id = r.client_id
            LEFT JOIN payments p ON p.id = r.payment_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (r.receipt_no LIKE :search OR c.legal_name LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['client_id'] ?? '')) !== '') {
            $whereSql .= " AND r.client_id = :client_id";
            $params['client_id'] = (int) $filters['client_id'];
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT r.*, c.legal_name AS client_name, p.payment_mode, p.reference_no
             {$baseSql}{$whereSql}
             ORDER BY r.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
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

    public function paginatePayments(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM payments p
            LEFT JOIN clients c ON c.id = p.client_id
            LEFT JOIN service_orders so ON so.id = p.service_order_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (c.legal_name LIKE :search OR p.reference_no LIKE :search OR so.so_no LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['transaction_type'] ?? '')) !== '') {
            $whereSql .= " AND p.transaction_type = :transaction_type";
            $params['transaction_type'] = trim((string) $filters['transaction_type']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT p.*, c.legal_name AS client_name, so.so_no
             {$baseSql}{$whereSql}
             ORDER BY p.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
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

    public function outstandingInvoices(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.*, c.legal_name AS client_name, so.so_no,
                    DATEDIFF(CURDATE(), COALESCE(i.due_date, i.invoice_date)) AS ageing_days
             FROM invoices i
             LEFT JOIN clients c ON c.id = i.client_id
             LEFT JOIN service_orders so ON so.id = i.service_order_id
             WHERE i.payment_status IN ('UNPAID','PARTIALLY_PAID')
               AND i.accounting_status != 'CANCELLED'
             ORDER BY ageing_days DESC, i.id DESC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ageingData(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.*, c.legal_name AS client_name,
                    DATEDIFF(CURDATE(), COALESCE(i.due_date, i.invoice_date)) AS ageing_days,
                    CASE
                        WHEN i.due_date IS NULL OR i.due_date >= CURDATE() THEN 'Not Due'
                        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 0 AND 30 THEN '0-30'
                        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 31 AND 60 THEN '31-60'
                        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 61 AND 90 THEN '61-90'
                        ELSE '90+'
                    END AS ageing_bucket
             FROM invoices i
             LEFT JOIN clients c ON c.id = i.client_id
             WHERE i.payment_status IN ('UNPAID','PARTIALLY_PAID')
               AND i.accounting_status != 'CANCELLED'
             ORDER BY ageing_days DESC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginateFollowups(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM accounts_followups af
            LEFT JOIN clients c ON c.id = af.client_id
            LEFT JOIN invoices i ON i.id = af.invoice_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND af.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT af.*, c.legal_name AS client_name, i.invoice_no
             {$baseSql}{$whereSql}
             ORDER BY af.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
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

    public function createFollowup(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO accounts_followups (client_id, invoice_id, service_order_id, followup_date, followup_mode, followup_note, next_followup_date, status, created_by)
             VALUES (:client_id, :invoice_id, :service_order_id, :followup_date, :followup_mode, :followup_note, :next_followup_date, :status, :created_by)"
        );
        $statement->execute([
            'client_id' => $data['client_id'] ?: null,
            'invoice_id' => $data['invoice_id'] ?: null,
            'service_order_id' => $data['service_order_id'] ?: null,
            'followup_date' => $data['followup_date'] ?: date('Y-m-d'),
            'followup_mode' => $data['followup_mode'] ?: null,
            'followup_note' => $data['followup_note'] ?: null,
            'next_followup_date' => $data['next_followup_date'] ?: null,
            'status' => $data['status'] ?? 'OPEN',
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function consultantPayables(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT cb.*, con.name AS consultant_name, ca.assignment_title,
                    cb.total_amount - COALESCE((SELECT SUM(cp.amount) FROM consultant_payments cp WHERE cp.consultant_bill_id = cb.id), 0) AS balance_payable
             FROM consultant_bills cb
             LEFT JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
             LEFT JOIN consultants con ON con.id = ca.consultant_user_id
             WHERE cb.review_status = 'APPROVED'
             ORDER BY cb.id DESC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function unbilledCompletedWork(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.so_no, so.title, c.legal_name AS client_name, st.name AS service_type_name,
                    so.final_closed_at, so.current_stage_code,
                    u.full_name AS assigned_crm_name
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             LEFT JOIN service_types st ON st.id = so.service_type_id
             LEFT JOIN users u ON u.id = so.assigned_crm_id
             WHERE so.final_closed_at IS NOT NULL
               AND NOT EXISTS (SELECT 1 FROM invoices i WHERE i.service_order_id = so.id AND i.accounting_status != 'CANCELLED')
             ORDER BY so.final_closed_at DESC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allActiveClients(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, legal_name FROM clients WHERE is_active = 1 ORDER BY legal_name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }
}
