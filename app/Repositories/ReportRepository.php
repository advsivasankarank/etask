<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ReportRepository
{
    public function summaryCounts(): array
    {
        return [
            'total_clients' => $this->scalar("SELECT COUNT(*) FROM clients WHERE is_active = 1"),
            'active_service_orders' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL"),
            'overdue_service_orders' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL AND sla_due_at IS NOT NULL AND sla_due_at < NOW()"),
            'pending_documents' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1 AND verification_status = 'PENDING'"),
            'dsc_expiring_soon' => $this->scalar("SELECT COUNT(*) FROM dsc_register WHERE is_active = 1 AND valid_to IS NOT NULL AND valid_to BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"),
            'staff_present_today' => $this->scalar("SELECT COUNT(DISTINCT user_id) FROM attendance_sessions WHERE DATE(login_at) = CURDATE()"),
            'outstanding_amount' => $this->scalar("SELECT COALESCE(SUM(net_payable), 0) FROM invoices WHERE payment_status IN ('UNPAID','PARTIALLY_PAID') AND accounting_status != 'CANCELLED'") - $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'SUCCESS' AND transaction_type = 'INVOICE_PAYMENT'"),
            'consultant_payables' => $this->scalar("SELECT COALESCE(SUM(cb.total_amount), 0) FROM consultant_bills cb WHERE cb.review_status = 'APPROVED' AND NOT EXISTS (SELECT 1 FROM consultant_payments cp WHERE cp.consultant_bill_id = cb.id)"),
        ];
    }

    public function clientSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT c.id, c.legal_name, c.pan, c.mobile,
                    (SELECT COUNT(*) FROM service_orders so WHERE so.client_id = c.id AND so.final_closed_at IS NULL) AS active_so,
                    (SELECT COUNT(*) FROM invoices i WHERE i.client_id = c.id AND i.payment_status IN ('UNPAID','PARTIALLY_PAID') AND i.accounting_status != 'CANCELLED') AS unpaid_invoices
             FROM clients c WHERE c.is_active = 1 ORDER BY c.legal_name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function serviceOrderSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.so_no, c.legal_name AS client_name, st.name AS service_type_name, so.current_stage_code,
                    so.sla_due_at, so.created_at,
                    CASE WHEN so.final_closed_at IS NOT NULL THEN 'Closed' WHEN so.sla_due_at < NOW() THEN 'Overdue' ELSE 'Active' END AS status_label
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             LEFT JOIN service_types st ON st.id = so.service_type_id
             WHERE so.final_closed_at IS NULL OR so.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             ORDER BY so.sla_due_at ASC, so.id DESC
             LIMIT 50"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function attendanceSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT u.full_name,
                    MAX(CASE WHEN DATE(asess.login_at) = CURDATE() THEN 1 ELSE 0 END) AS present_today,
                    MAX(CASE WHEN DATE(asess.login_at) = CURDATE() AND asess.logout_at IS NULL THEN 1 ELSE 0 END) AS on_work
             FROM users u
             LEFT JOIN attendance_sessions asess ON asess.user_id = u.id
             WHERE u.is_active = 1
             GROUP BY u.id, u.full_name
             ORDER BY u.full_name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function documentSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT verification_status, COUNT(*) AS count
             FROM documents WHERE is_active = 1
             GROUP BY verification_status"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dscSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT custody_status, COUNT(*) AS count
             FROM dsc_register WHERE is_active = 1
             GROUP BY custody_status"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accountsSummary(): array
    {
        return [
            'total_invoiced' => $this->scalar("SELECT COALESCE(SUM(net_payable), 0) FROM invoices WHERE accounting_status != 'CANCELLED'"),
            'total_received' => $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'SUCCESS' AND transaction_type = 'INVOICE_PAYMENT'"),
            'outstanding' => $this->scalar("SELECT COALESCE(SUM(net_payable), 0) FROM invoices WHERE payment_status IN ('UNPAID','PARTIALLY_PAID') AND accounting_status != 'CANCELLED'") - $this->scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'SUCCESS' AND transaction_type = 'INVOICE_PAYMENT'"),
        ];
    }

    public function consultantSummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT con.name, con.status,
                    (SELECT COUNT(*) FROM consultant_assignments ca WHERE ca.consultant_user_id = con.id AND ca.status IN ('ASSIGNED','WORK_SUBMITTED')) AS pending_assignments,
                    (SELECT COALESCE(SUM(cb.total_amount), 0) FROM consultant_bills cb
                     INNER JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
                     WHERE ca.consultant_user_id = con.id AND cb.review_status = 'APPROVED'
                     AND NOT EXISTS (SELECT 1 FROM consultant_payments cp WHERE cp.consultant_bill_id = cb.id)) AS balance_payable
             FROM consultants con WHERE con.status != 'BLACKLISTED' ORDER BY con.name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activitySummary(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT al.action_code, COUNT(*) AS count, MAX(al.created_at) AS last_at
             FROM activity_logs al
             WHERE al.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY al.action_code
             ORDER BY count DESC
             LIMIT 20"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function overdueServiceOrders(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.so_no, c.legal_name AS client_name, st.name AS service_type_name, so.sla_due_at, so.current_stage_code
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             LEFT JOIN service_types st ON st.id = so.service_type_id
             WHERE so.final_closed_at IS NULL AND so.sla_due_at IS NOT NULL AND so.sla_due_at < NOW()
             ORDER BY so.sla_due_at ASC
             LIMIT 25"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pendingFollowups(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT af.*, c.legal_name AS client_name, i.invoice_no
             FROM accounts_followups af
             LEFT JOIN clients c ON c.id = af.client_id
             LEFT JOIN invoices i ON i.id = af.invoice_id
             WHERE af.status IN ('OPEN','PROMISED')
             ORDER BY af.next_followup_date ASC, af.id DESC
             LIMIT 25"
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
