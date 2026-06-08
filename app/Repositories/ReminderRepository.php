<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOStatement;

final class ReminderRepository
{
    public function summary(): array
    {
        return [
            'open_reminders' => (int) $this->scalar("SELECT COUNT(*) FROM reminders WHERE status IN ('PENDING', 'SENT', 'OVERDUE')"),
            'due_today' => (int) $this->scalar("SELECT COUNT(*) FROM reminders WHERE status IN ('PENDING', 'SENT', 'OVERDUE') AND DATE(due_at) = CURDATE()"),
            'overdue' => (int) $this->scalar("SELECT COUNT(*) FROM reminders WHERE status = 'OVERDUE'"),
            'email_failures' => (int) $this->scalar("SELECT COUNT(*) FROM reminder_delivery_logs WHERE delivery_channel = 'EMAIL' AND delivery_status = 'FAILED'"),
        ];
    }

    public function dashboardNotifications(?int $userId, ?int $clientContactId, int $limit = 8): array
    {
        $sql = "SELECT id, subject, message, linked_module, linked_id, delivery_status, created_at, read_at
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

        $sql .= " ORDER BY id DESC LIMIT :limit";
        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function templates(): array
    {
        return Database::connection()->query(
            "SELECT id, code, reminder_type, channel, subject, message, is_active, updated_at
             FROM reminder_templates
             ORDER BY reminder_type ASC, channel ASC, code ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function templateById(int $templateId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM reminder_templates
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $templateId]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function activeTemplatesByType(string $reminderType): array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM reminder_templates
             WHERE reminder_type = :reminder_type
               AND is_active = 1
             ORDER BY channel ASC"
        );
        $statement->execute(['reminder_type' => $reminderType]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveTemplate(array $payload, ?int $templateId = null): int
    {
        if ($templateId === null) {
            $statement = Database::connection()->prepare(
                "INSERT INTO reminder_templates (
                    code, reminder_type, channel, subject, message, is_active, created_at, updated_at
                 ) VALUES (
                    :code, :reminder_type, :channel, :subject, :message, :is_active, NOW(), NOW()
                 )"
            );
            $statement->execute($payload);

            return (int) Database::connection()->lastInsertId();
        }

        $payload['id'] = $templateId;
        $statement = Database::connection()->prepare(
            "UPDATE reminder_templates
             SET code = :code,
                 reminder_type = :reminder_type,
                 channel = :channel,
                 subject = :subject,
                 message = :message,
                 is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);

        return $templateId;
    }

    public function escalationRules(): array
    {
        return Database::connection()->query(
            "SELECT id, reminder_type, day_offset, target_type, target_role_code, channel, is_active, updated_at
             FROM reminder_escalation_rules
             ORDER BY reminder_type ASC, day_offset ASC, channel ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function escalationRuleById(int $ruleId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM reminder_escalation_rules
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $ruleId]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function activeEscalationRulesByType(string $reminderType): array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM reminder_escalation_rules
             WHERE reminder_type = :reminder_type
               AND is_active = 1
             ORDER BY day_offset ASC, channel ASC"
        );
        $statement->execute(['reminder_type' => $reminderType]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveEscalationRule(array $payload, ?int $ruleId = null): int
    {
        if ($ruleId === null) {
            $statement = Database::connection()->prepare(
                "INSERT INTO reminder_escalation_rules (
                    reminder_type, day_offset, target_type, target_role_code, channel, is_active, created_at, updated_at
                 ) VALUES (
                    :reminder_type, :day_offset, :target_type, :target_role_code, :channel, :is_active, NOW(), NOW()
                 )"
            );
            $statement->execute($payload);

            return (int) Database::connection()->lastInsertId();
        }

        $payload['id'] = $ruleId;
        $statement = Database::connection()->prepare(
            "UPDATE reminder_escalation_rules
             SET reminder_type = :reminder_type,
                 day_offset = :day_offset,
                 target_type = :target_type,
                 target_role_code = :target_role_code,
                 channel = :channel,
                 is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute($payload);

        return $ruleId;
    }

    public function roleOptions(): array
    {
        return Database::connection()->query(
            "SELECT code, label
             FROM roles
             WHERE is_active = 1
             ORDER BY label ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeUsersByRole(string $roleCode): array
    {
        $statement = Database::connection()->prepare(
            "SELECT u.id, u.full_name, u.email
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code = :role_code
               AND u.is_active = 1
             ORDER BY u.full_name ASC"
        );
        $statement->execute(['role_code' => $roleCode]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function firstActiveUserByRoles(array $roleCodes): ?array
    {
        if ($roleCodes === []) {
            return null;
        }

        $placeholders = implode(', ', array_fill(0, count($roleCodes), '?'));
        $statement = Database::connection()->prepare(
            "SELECT DISTINCT u.id, u.full_name, u.email
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.code IN ({$placeholders})
               AND u.is_active = 1
             ORDER BY u.id ASC
             LIMIT 1"
        );
        $statement->execute($roleCodes);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function primaryContactByClientId(int $clientId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, contact_name, email
             FROM client_contacts
             WHERE client_id = :client_id
             ORDER BY is_primary DESC, id ASC
             LIMIT 1"
        );
        $statement->execute(['client_id' => $clientId]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function openReminderByDedupeKey(string $dedupeKey): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM reminders
             WHERE dedupe_key = :dedupe_key
               AND status IN ('PENDING', 'SENT', 'OVERDUE')
             LIMIT 1"
        );
        $statement->execute(['dedupe_key' => $dedupeKey]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function createReminder(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO reminders (
                service_order_id, client_id, pso_id, invoice_id, consultant_assignment_id, reminder_type, reminder_code,
                schedule_day_no, due_at, sent_at, resolved_at, status, assigned_to, recipient_contact_id, recipient_email,
                created_by, template_id, linked_module, linked_id, title, notes, escalation_level, dedupe_key,
                last_triggered_at, created_via, created_at
             ) VALUES (
                :service_order_id, :client_id, :pso_id, :invoice_id, :consultant_assignment_id, :reminder_type, :reminder_code,
                :schedule_day_no, :due_at, NULL, NULL, :status, :assigned_to, :recipient_contact_id, :recipient_email,
                :created_by, :template_id, :linked_module, :linked_id, :title, :notes, :escalation_level, :dedupe_key,
                NULL, :created_via, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function insertReminderLog(int $reminderId, string $actionType, ?int $actionBy, ?string $note): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO reminder_logs (reminder_id, action_type, action_by, action_note, action_at)
             VALUES (:reminder_id, :action_type, :action_by, :action_note, NOW())"
        );
        $statement->execute([
            'reminder_id' => $reminderId,
            'action_type' => $actionType,
            'action_by' => $actionBy,
            'action_note' => $note,
        ]);
    }

    public function dueRemindersForDispatch(): array
    {
        return Database::connection()->query(
            "SELECT r.*,
                    c.legal_name AS client_name,
                    c.email AS client_email,
                    so.so_no,
                    so.current_stage_code,
                    pso.pso_no,
                    i.invoice_no,
                    i.net_payable,
                    assigned.full_name AS assigned_user_name,
                    assigned.email AS assigned_user_email,
                    cc.contact_name,
                    cc.email AS contact_email
             FROM reminders r
             LEFT JOIN clients c ON c.id = r.client_id
             LEFT JOIN service_orders so ON so.id = r.service_order_id
             LEFT JOIN pre_service_orders pso ON pso.id = r.pso_id
             LEFT JOIN invoices i ON i.id = r.invoice_id
             LEFT JOIN users assigned ON assigned.id = r.assigned_to
             LEFT JOIN client_contacts cc ON cc.id = r.recipient_contact_id
             WHERE r.status IN ('PENDING', 'SENT', 'OVERDUE')
               AND r.due_at <= NOW()
               AND (r.last_triggered_at IS NULL OR DATE(r.last_triggered_at) < CURDATE())
             ORDER BY r.due_at ASC, r.id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function escalationCandidates(): array
    {
        return Database::connection()->query(
            "SELECT r.*,
                    c.legal_name AS client_name,
                    so.so_no,
                    pso.pso_no,
                    i.invoice_no
             FROM reminders r
             LEFT JOIN clients c ON c.id = r.client_id
             LEFT JOIN service_orders so ON so.id = r.service_order_id
             LEFT JOIN pre_service_orders pso ON pso.id = r.pso_id
             LEFT JOIN invoices i ON i.id = r.invoice_id
             WHERE r.status IN ('PENDING', 'SENT', 'OVERDUE')
               AND r.resolved_at IS NULL
             ORDER BY r.id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markReminderTriggered(int $reminderId, string $status): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE reminders
             SET sent_at = CASE WHEN sent_at IS NULL THEN NOW() ELSE sent_at END,
                 last_triggered_at = NOW(),
                 status = :status
             WHERE id = :id"
        );
        $statement->execute([
            'status' => $status,
            'id' => $reminderId,
        ]);
    }

    public function updateEscalationLevel(int $reminderId, int $escalationLevel): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE reminders
             SET escalation_level = GREATEST(escalation_level, :escalation_level),
                 status = CASE WHEN due_at < NOW() AND status IN ('PENDING', 'SENT') THEN 'OVERDUE' ELSE status END
             WHERE id = :id"
        );
        $statement->execute([
            'escalation_level' => $escalationLevel,
            'id' => $reminderId,
        ]);
    }

    public function resolveReminder(int $reminderId, string $note = 'Resolved by scheduler'): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE reminders
             SET status = 'DONE',
                 resolved_at = NOW()
             WHERE id = :id
               AND status IN ('PENDING', 'SENT', 'OVERDUE')"
        );
        $statement->execute(['id' => $reminderId]);
        $this->insertReminderLog($reminderId, 'COMPLETED', null, $note);
    }

    public function deliveryLog(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO reminder_delivery_logs (
                reminder_id, template_id, recipient_user_id, recipient_contact_id, recipient_email, delivery_channel,
                triggered_at, delivery_status, error_message, notification_id
             ) VALUES (
                :reminder_id, :template_id, :recipient_user_id, :recipient_contact_id, :recipient_email, :delivery_channel,
                NOW(), :delivery_status, :error_message, :notification_id
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function createNotification(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO notifications (
                user_id, client_contact_id, reminder_id, template_id, channel, subject, message, recipient_email,
                error_message, payload_json, linked_module, linked_id, sent_at, delivery_status, read_at, delivery_attempts, created_at
             ) VALUES (
                :user_id, :client_contact_id, :reminder_id, :template_id, :channel, :subject, :message, :recipient_email,
                :error_message, :payload_json, :linked_module, :linked_id, :sent_at, :delivery_status, NULL, :delivery_attempts, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function candidatePendingDocuments(): array
    {
        return Database::connection()->query(
            "SELECT so.id AS service_order_id,
                    so.client_id,
                    so.current_stage_code,
                    so.so_no,
                    c.legal_name AS client_name,
                    COALESCE(so.assigned_crm_id, so.assigned_assistant_crm_id, so.assigned_backend_id) AS assigned_user_id
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
             WHERE so.final_closed_at IS NULL
               AND (so.current_stage_code = 'DOCUMENT_PENDING' OR COALESCE(ssf.is_document_pending, 0) = 1)"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidatePendingPso(): array
    {
        return Database::connection()->query(
            "SELECT pso.id AS pso_id,
                    pso.client_id,
                    pso.pso_no,
                    pso.submitted_at AS reference_at,
                    c.legal_name AS client_name,
                    NULL AS assigned_user_id
             FROM pre_service_orders pso
             INNER JOIN clients c ON c.id = pso.client_id
             WHERE pso.current_status IN ('SUBMITTED', 'UNDER_REVIEW')"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidatePendingServiceOrders(): array
    {
        return Database::connection()->query(
            "SELECT so.id AS service_order_id,
                    so.client_id,
                    so.current_stage_code,
                    so.so_no,
                    c.legal_name AS client_name,
                    COALESCE(so.assigned_backend_id, so.assigned_crm_id, so.assigned_assistant_crm_id) AS assigned_user_id,
                    COALESCE(so.last_stage_changed_at, so.created_at) AS reference_at
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             WHERE so.final_closed_at IS NULL
               AND so.current_stage_code <> 'PROCEDURALLY_CLOSED'"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateWorkflowFollowUps(): array
    {
        return Database::connection()->query(
            "SELECT so.id AS service_order_id,
                    so.client_id,
                    so.current_stage_code,
                    so.so_no,
                    c.legal_name AS client_name,
                    COALESCE(so.assigned_crm_id, so.assigned_backend_id) AS assigned_user_id,
                    COALESCE(so.last_stage_changed_at, so.created_at) AS reference_at
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             WHERE so.final_closed_at IS NULL
               AND so.current_stage_code IN ('PREPARATION', 'REVIEW', 'FILING_PENDING', 'PAYMENT_PENDING', 'PAID', 'ACKNOWLEDGEMENT_CAPTURED', 'E_VERIFICATION_PENDING')"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateInvoiceDue(): array
    {
        return Database::connection()->query(
            "SELECT i.id AS invoice_id,
                    i.service_order_id,
                    i.client_id,
                    i.invoice_no,
                    i.due_date,
                    i.net_payable,
                    c.legal_name AS client_name,
                    c.email AS client_email,
                    so.so_no
             FROM invoices i
             INNER JOIN clients c ON c.id = i.client_id
             INNER JOIN service_orders so ON so.id = i.service_order_id
             WHERE i.accounting_status <> 'CANCELLED'
               AND i.payment_status <> 'PAID'
               AND i.due_date IS NOT NULL
               AND i.due_date >= CURDATE()
               AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateOverdueInvoices(): array
    {
        return Database::connection()->query(
            "SELECT i.id AS invoice_id,
                    i.service_order_id,
                    i.client_id,
                    i.invoice_no,
                    i.due_date,
                    i.net_payable,
                    c.legal_name AS client_name,
                    c.email AS client_email,
                    so.so_no
             FROM invoices i
             INNER JOIN clients c ON c.id = i.client_id
             INNER JOIN service_orders so ON so.id = i.service_order_id
             WHERE i.accounting_status <> 'CANCELLED'
               AND i.payment_status <> 'PAID'
               AND i.due_date IS NOT NULL
               AND i.due_date < CURDATE()"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateConsultantDeliverables(): array
    {
        return Database::connection()->query(
            "SELECT ca.id AS consultant_assignment_id,
                    ca.service_order_id,
                    so.client_id,
                    so.so_no,
                    c.legal_name AS client_name,
                    ca.consultant_user_id AS assigned_user_id,
                    u.email AS assigned_user_email,
                    ca.assigned_at,
                    COUNT(cd.id) AS deliverable_count
             FROM consultant_assignments ca
             INNER JOIN service_orders so ON so.id = ca.service_order_id
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN users u ON u.id = ca.consultant_user_id
             LEFT JOIN consultant_deliverables cd ON cd.consultant_assignment_id = ca.id
             WHERE ca.status IN ('ASSIGNED', 'WORK_SUBMITTED', 'UNDER_INTERNAL_REVIEW')
             GROUP BY ca.id, ca.service_order_id, so.client_id, so.so_no, c.legal_name, ca.consultant_user_id, u.email, ca.assigned_at"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateClientClarifications(): array
    {
        return Database::connection()->query(
            "SELECT soq.id AS query_id,
                    soq.service_order_id,
                    so.client_id,
                    so.so_no,
                    c.legal_name AS client_name,
                    cc.id AS recipient_contact_id,
                    cc.email AS recipient_email,
                    soq.raised_at
             FROM service_order_queries soq
             INNER JOIN service_orders so ON so.id = soq.service_order_id
             INNER JOIN clients c ON c.id = so.client_id
             LEFT JOIN client_contacts cc ON cc.id = soq.addressed_to_contact_id
             WHERE soq.status = 'OPEN'
               AND soq.addressed_to_contact_id IS NOT NULL"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function candidateComplianceDueDates(): array
    {
        return Database::connection()->query(
            "SELECT so.id AS service_order_id,
                    so.client_id,
                    so.so_no,
                    so.sla_due_at,
                    so.current_stage_code,
                    c.legal_name AS client_name,
                    COALESCE(so.assigned_crm_id, so.assigned_backend_id, so.assigned_assistant_crm_id) AS assigned_user_id
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             WHERE so.final_closed_at IS NULL
               AND so.sla_due_at IS NOT NULL
               AND DATE(so.sla_due_at) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function openRemindersForSync(string $reminderType): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, dedupe_key
             FROM reminders
             WHERE reminder_type = :reminder_type
               AND status IN ('PENDING', 'SENT', 'OVERDUE')"
        );
        $statement->execute(['reminder_type' => $reminderType]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registerReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM reminders r
            LEFT JOIN clients c ON c.id = r.client_id
            LEFT JOIN service_orders so ON so.id = r.service_order_id
            LEFT JOIN pre_service_orders pso ON pso.id = r.pso_id
            LEFT JOIN invoices i ON i.id = r.invoice_id
            LEFT JOIN users u ON u.id = r.assigned_to
            WHERE 1 = 1";
        [$whereSql, $params] = $this->buildReportFilters($filters);

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$whereSql}",
            "SELECT r.id,
                    r.reminder_type,
                    r.title,
                    r.status,
                    r.due_at,
                    r.sent_at,
                    r.resolved_at,
                    r.escalation_level,
                    r.linked_module,
                    r.linked_id,
                    c.legal_name AS client_name,
                    so.so_no,
                    pso.pso_no,
                    i.invoice_no,
                    u.full_name AS assigned_user_name
             {$baseSql}{$whereSql}",
            $params,
            $page,
            $perPage,
            ' ORDER BY r.id DESC'
        );
    }

    public function pendingReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $filters['status'] = $filters['status'] ?? 'OPEN_ONLY';
        return $this->registerReport($filters, $page, $perPage);
    }

    public function effectivenessReport(array $filters): array
    {
        [$whereSql, $params] = $this->buildDeliveryFilters($filters);

        $statement = Database::connection()->prepare(
            "SELECT r.reminder_type,
                    COUNT(*) AS total_deliveries,
                    SUM(CASE WHEN rdl.delivery_status = 'SENT' THEN 1 ELSE 0 END) AS sent_count,
                    SUM(CASE WHEN rdl.delivery_status = 'FAILED' THEN 1 ELSE 0 END) AS failed_count,
                    SUM(CASE WHEN r.resolved_at IS NOT NULL THEN 1 ELSE 0 END) AS resolved_count,
                    AVG(TIMESTAMPDIFF(HOUR, r.created_at, COALESCE(r.resolved_at, NOW()))) AS avg_resolution_hours
             FROM reminder_delivery_logs rdl
             INNER JOIN reminders r ON r.id = rdl.reminder_id
             {$whereSql}
             GROUP BY r.reminder_type
             ORDER BY r.reminder_type ASC"
        );
        $this->bindParams($statement, $params);
        $statement->execute();

        return ['items' => $statement->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function escalationReport(array $filters, int $page = 1, int $perPage = 25): array
    {
        $baseSql = "FROM reminder_logs rl
            INNER JOIN reminders r ON r.id = rl.reminder_id
            LEFT JOIN clients c ON c.id = r.client_id
            LEFT JOIN service_orders so ON so.id = r.service_order_id
            LEFT JOIN users u ON u.id = rl.action_by
            WHERE rl.action_type = 'ESCALATED'";
        [$whereSql, $params] = $this->buildEscalationFilters($filters);

        return $this->paginate(
            "SELECT COUNT(*) {$baseSql}{$whereSql}",
            "SELECT rl.id,
                    rl.action_at,
                    rl.action_note,
                    r.reminder_type,
                    r.escalation_level,
                    c.legal_name AS client_name,
                    so.so_no,
                    u.full_name AS action_by_name
             {$baseSql}{$whereSql}",
            $params,
            $page,
            $perPage,
            ' ORDER BY rl.id DESC'
        );
    }

    private function buildReportFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $where .= " AND (
                r.title LIKE :search_title
                OR c.legal_name LIKE :search_client_name
                OR so.so_no LIKE :search_so_no
                OR pso.pso_no LIKE :search_pso_no
                OR i.invoice_no LIKE :search_invoice_no
            )";
            $params['search_title'] = $term;
            $params['search_client_name'] = $term;
            $params['search_so_no'] = $term;
            $params['search_pso_no'] = $term;
            $params['search_invoice_no'] = $term;
        }

        if (trim((string) ($filters['reminder_type'] ?? '')) !== '') {
            $where .= " AND r.reminder_type = :reminder_type";
            $params['reminder_type'] = trim((string) $filters['reminder_type']);
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'OPEN_ONLY') {
            $where .= " AND r.status IN ('PENDING', 'SENT', 'OVERDUE')";
        } elseif ($status !== '') {
            $where .= " AND r.status = :status";
            $params['status'] = $status;
        }

        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(r.due_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }

        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(r.due_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        return [$where, $params];
    }

    private function buildDeliveryFilters(array $filters): array
    {
        $where = 'WHERE 1 = 1';
        $params = [];

        if (trim((string) ($filters['reminder_type'] ?? '')) !== '') {
            $where .= " AND r.reminder_type = :reminder_type";
            $params['reminder_type'] = trim((string) $filters['reminder_type']);
        }

        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(rdl.triggered_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }

        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(rdl.triggered_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        return [$where, $params];
    }

    private function buildEscalationFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['reminder_type'] ?? '')) !== '') {
            $where .= " AND r.reminder_type = :reminder_type";
            $params['reminder_type'] = trim((string) $filters['reminder_type']);
        }
        if (trim((string) ($filters['date_from'] ?? '')) !== '') {
            $where .= " AND DATE(rl.action_at) >= :date_from";
            $params['date_from'] = trim((string) $filters['date_from']);
        }
        if (trim((string) ($filters['date_to'] ?? '')) !== '') {
            $where .= " AND DATE(rl.action_at) <= :date_to";
            $params['date_to'] = trim((string) $filters['date_to']);
        }

        return [$where, $params];
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

    private function scalar(string $sql): mixed
    {
        return Database::connection()->query($sql)->fetchColumn();
    }
}
