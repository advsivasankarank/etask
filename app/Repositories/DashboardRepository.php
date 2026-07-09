<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DashboardRepository
{
    public function adminMetrics(): array
    {
        return [
            'open_service_orders' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL"),
            'pso_pending_review' => $this->scalar("SELECT COUNT(*) FROM pre_service_orders WHERE current_status IN ('SUBMITTED', 'UNDER_REVIEW')"),
            'sla_breaches' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL AND sla_due_at IS NOT NULL AND sla_due_at < NOW()"),
            'overdue_everification' => $this->scalar("SELECT COUNT(*) FROM service_order_status_flags WHERE is_overdue = 1"),
            'pending_consultant_settlement' => $this->scalar("SELECT COUNT(*) FROM service_order_status_flags WHERE is_consultant_payment_pending = 1"),
            'unpaid_invoices' => $this->scalar("SELECT COUNT(*) FROM invoices WHERE payment_status <> 'PAID' AND accounting_status <> 'CANCELLED'"),
            'due_today' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL AND DATE(sla_due_at) = CURDATE()"),
            'overdue_count' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NULL AND sla_due_at IS NOT NULL AND sla_due_at < NOW()"),
            'procedural_pending' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE procedural_closed_at IS NULL AND final_closed_at IS NULL AND current_stage_code = 'PROCEDURALLY_CLOSED'"),
            'accounts_pending' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE procedural_closed_at IS NOT NULL AND accounting_closed_at IS NULL AND final_closed_at IS NULL"),
            'unbilled_completed' => $this->scalar("SELECT COUNT(*) FROM service_orders so WHERE so.final_closed_at IS NOT NULL AND NOT EXISTS (SELECT 1 FROM invoices i WHERE i.service_order_id = so.id AND i.accounting_status != 'CANCELLED')"),
            'pending_documents' => $this->scalar("SELECT COUNT(*) FROM documents WHERE is_active = 1 AND verification_status = 'PENDING'"),
            'staff_online_today' => $this->scalar("SELECT COUNT(DISTINCT user_id) FROM attendance_sessions WHERE DATE(login_at) = CURDATE()"),
            'daily_reports_pending' => $this->scalar("SELECT COUNT(*) FROM daily_work_reports WHERE report_date = CURDATE() AND status = 'SUBMITTED'"),
            'recently_closed' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE final_closed_at IS NOT NULL AND final_closed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
        ];
    }

    public function crmMetrics(int $userId): array
    {
        return [
            'my_service_orders' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE assigned_crm_id = :user_id AND final_closed_at IS NULL", ['user_id' => $userId]),
            'document_pending' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE assigned_crm_id = :user_id AND current_stage_code = 'DOCUMENT_PENDING'", ['user_id' => $userId]),
            'everification_followups' => $this->scalar("SELECT COUNT(*) FROM reminders WHERE assigned_to = :user_id AND reminder_type = 'E_VERIFICATION' AND status IN ('PENDING', 'SENT', 'OVERDUE')", ['user_id' => $userId]),
            'pso_queue' => $this->scalar("SELECT COUNT(*) FROM pre_service_orders WHERE current_status IN ('SUBMITTED', 'UNDER_REVIEW')"),
        ];
    }

    public function accountsMetrics(): array
    {
        return [
            'draft_or_unpaid_invoices' => $this->scalar("SELECT COUNT(*) FROM invoices WHERE payment_status <> 'PAID' AND accounting_status <> 'CANCELLED'"),
            'advance_payments' => $this->scalar("SELECT COUNT(*) FROM payments WHERE transaction_type = 'ADVANCE' AND status = 'SUCCESS'"),
            'consultant_pending' => $this->scalar("SELECT COUNT(*) FROM service_order_status_flags WHERE is_consultant_payment_pending = 1"),
            'client_paid_orders' => $this->scalar("SELECT COUNT(*) FROM service_order_status_flags WHERE is_client_paid = 1"),
        ];
    }

    public function consultantMetrics(int $userId): array
    {
        return [
            'assignments' => $this->scalar("SELECT COUNT(*) FROM consultant_assignments WHERE consultant_user_id = :user_id", ['user_id' => $userId]),
            'under_review' => $this->scalar("SELECT COUNT(*) FROM consultant_assignments WHERE consultant_user_id = :user_id AND status IN ('ASSIGNED', 'WORK_SUBMITTED', 'UNDER_INTERNAL_REVIEW')", ['user_id' => $userId]),
            'approved_bills' => $this->scalar(
                "SELECT COUNT(*)
                 FROM consultant_bills cb
                 INNER JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
                 WHERE ca.consultant_user_id = :user_id AND cb.review_status = 'APPROVED'",
                ['user_id' => $userId]
            ),
        ];
    }

    public function clientMetrics(int $clientId): array
    {
        return [
            'my_psos' => $this->scalar("SELECT COUNT(*) FROM pre_service_orders WHERE client_id = :client_id", ['client_id' => $clientId]),
            'my_open_sos' => $this->scalar("SELECT COUNT(*) FROM service_orders WHERE client_id = :client_id AND final_closed_at IS NULL", ['client_id' => $clientId]),
            'my_invoices' => $this->scalar("SELECT COUNT(*) FROM invoices WHERE client_id = :client_id", ['client_id' => $clientId]),
            'pending_payments' => $this->scalar("SELECT COUNT(*) FROM invoices WHERE client_id = :client_id AND payment_status <> 'PAID' AND accounting_status <> 'CANCELLED'", ['client_id' => $clientId]),
        ];
    }

    public function adminQueues(): array
    {
        return [
            'sla_breaches' => $this->fetchAll(
                "SELECT so.so_no, c.legal_name AS client_name, so.sla_due_at, so.current_stage_code
                 FROM service_orders so
                 INNER JOIN clients c ON c.id = so.client_id
                 WHERE so.final_closed_at IS NULL AND so.sla_due_at IS NOT NULL AND so.sla_due_at < NOW()
                 ORDER BY so.sla_due_at ASC
                 LIMIT 8"
            ),
            'pso_queue' => $this->fetchAll(
                "SELECT pso.pso_no, c.legal_name AS client_name, st.name AS service_type_name, pso.current_status
                 FROM pre_service_orders pso
                 INNER JOIN clients c ON c.id = pso.client_id
                 INNER JOIN service_types st ON st.id = pso.service_type_id
                 WHERE pso.current_status IN ('SUBMITTED', 'UNDER_REVIEW')
                 ORDER BY pso.id DESC
                 LIMIT 8"
            ),
        ];
    }

    public function crmQueues(int $userId): array
    {
        return [
            'my_stage_queue' => $this->fetchAll(
                "SELECT so.so_no, c.legal_name AS client_name, so.current_stage_code, so.sla_due_at
                 FROM service_orders so
                 INNER JOIN clients c ON c.id = so.client_id
                 WHERE so.assigned_crm_id = :user_id AND so.final_closed_at IS NULL
                 ORDER BY so.sla_due_at ASC, so.id DESC
                 LIMIT 8",
                ['user_id' => $userId]
            ),
            'everification_reminders' => $this->fetchAll(
                "SELECT r.id, so.so_no, c.legal_name AS client_name, r.schedule_day_no, r.status, r.due_at
                 FROM reminders r
                 INNER JOIN service_orders so ON so.id = r.service_order_id
                 INNER JOIN clients c ON c.id = so.client_id
                 WHERE r.assigned_to = :user_id
                   AND r.reminder_type = 'E_VERIFICATION'
                   AND r.status IN ('PENDING', 'SENT', 'OVERDUE')
                 ORDER BY r.due_at ASC
                 LIMIT 8",
                ['user_id' => $userId]
            ),
        ];
    }

    public function accountsQueues(): array
    {
        return [
            'unpaid_invoices' => $this->fetchAll(
                "SELECT i.invoice_no, c.legal_name AS client_name, i.net_payable, i.payment_status
                 FROM invoices i
                 INNER JOIN clients c ON c.id = i.client_id
                 WHERE i.payment_status <> 'PAID'
                   AND i.accounting_status <> 'CANCELLED'
                 ORDER BY i.id DESC
                 LIMIT 8"
            ),
            'consultant_settlement' => $this->fetchAll(
                "SELECT so.so_no, c.legal_name AS client_name, ssf.is_consultant_payment_pending
                 FROM service_orders so
                 INNER JOIN clients c ON c.id = so.client_id
                 INNER JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
                 WHERE ssf.is_consultant_payment_pending = 1
                 ORDER BY so.id DESC
                 LIMIT 8"
            ),
        ];
    }

    public function consultantQueues(int $userId): array
    {
        return [
            'my_assignments' => $this->fetchAll(
                "SELECT so.so_no, c.legal_name AS client_name, ca.status, st.name AS service_type_name
                 FROM consultant_assignments ca
                 INNER JOIN service_orders so ON so.id = ca.service_order_id
                 INNER JOIN clients c ON c.id = so.client_id
                 INNER JOIN service_types st ON st.id = so.service_type_id
                 WHERE ca.consultant_user_id = :user_id
                 ORDER BY ca.id DESC
                 LIMIT 8",
                ['user_id' => $userId]
            ),
        ];
    }

    public function clientQueues(int $clientId): array
    {
        return [
            'my_service_orders' => $this->fetchAll(
                "SELECT so.so_no, so.current_stage_code, st.name AS service_type_name
                 FROM service_orders so
                 INNER JOIN service_types st ON st.id = so.service_type_id
                 WHERE so.client_id = :client_id
                 ORDER BY so.id DESC
                 LIMIT 8",
                ['client_id' => $clientId]
            ),
            'my_invoices' => $this->fetchAll(
                "SELECT invoice_no, invoice_type, net_payable, payment_status
                 FROM invoices
                 WHERE client_id = :client_id
                 ORDER BY id DESC
                 LIMIT 8",
                ['client_id' => $clientId]
            ),
        ];
    }

    public function dashboardNotifications(?int $userId, ?int $clientContactId): array
    {
        $sql = "SELECT id, subject, message, linked_module, linked_id, delivery_status, created_at
                FROM notifications
                WHERE channel = 'IN_APP'
                  AND delivery_status IN ('PENDING', 'SENT', 'READ')";
        $params = [];

        if (($clientContactId ?? 0) > 0) {
            $sql .= " AND client_contact_id = :client_contact_id";
            $params['client_contact_id'] = $clientContactId;
        } else {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }

        $sql .= " ORDER BY id DESC LIMIT 8";
        return $this->fetchAll($sql, $params);
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
