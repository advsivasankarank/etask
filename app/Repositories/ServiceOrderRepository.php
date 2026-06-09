<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ServiceOrderRepository
{
    public function paginateForIndex(?string $search = null, ?int $clientId = null, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*)
                FROM service_orders so
                INNER JOIN clients c ON c.id = so.client_id
                INNER JOIN companies comp ON comp.id = so.company_id
                INNER JOIN service_types st ON st.id = so.service_type_id
                WHERE 1 = 1";

        $dataSql = "SELECT so.id,
                       so.so_no,
                       so.title,
                       so.current_stage_code,
                       so.priority_level,
                       so.created_at,
                       so.is_locked,
                       c.legal_name AS client_name,
                       c.pan,
                       c.tan,
                       c.mobile,
                       comp.display_name AS company_name,
                       st.name AS service_type_name,
                       so.work_basis,
                       so.compliance_subtype,
                       so.assessment_year,
                       so.period_label
                FROM service_orders so
                INNER JOIN clients c ON c.id = so.client_id
                INNER JOIN companies comp ON comp.id = so.company_id
                INNER JOIN service_types st ON st.id = so.service_type_id
                WHERE 1 = 1";

        $params = [];

        if ($clientId !== null && $clientId > 0) {
            $countSql .= " AND so.client_id = :client_id";
            $dataSql .= " AND so.client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        if ($search !== null && trim($search) !== '') {
            $filterSql = " AND (
                       c.pan LIKE :search_pan
                       OR c.tan LIKE :search_tan
                       OR c.legal_name LIKE :search_legal_name
                       OR c.mobile LIKE :search_mobile
                       OR so.so_no LIKE :search_so_no)";
            $countSql .= $filterSql;
            $dataSql .= $filterSql;
            $searchTerm = '%' . trim($search) . '%';
            $params['search_pan'] = $searchTerm;
            $params['search_tan'] = $searchTerm;
            $params['search_legal_name'] = $searchTerm;
            $params['search_mobile'] = $searchTerm;
            $params['search_so_no'] = $searchTerm;
        }

        $countStatement = Database::connection()->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataSql .= " ORDER BY so.id DESC LIMIT :limit OFFSET :offset";

        $statement = Database::connection()->prepare($dataSql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
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

    public function findDetailedById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.*,
                    c.client_code,
                    c.legal_name AS client_name,
                    c.pan,
                    c.tan,
                    c.mobile,
                    c.email,
                    comp.display_name AS company_name,
                    comp.code AS company_code,
                    fy.label AS financial_year_label,
                    fy.code AS financial_year_code,
                    st.name AS service_type_name,
                    st.code AS service_type_code,
                    so.work_basis,
                    so.compliance_subtype,
                    so.assessment_year,
                    so.period_month,
                    so.period_quarter,
                    so.period_year,
                    so.period_label,
                    ssf.is_document_pending,
                    ssf.is_payment_pending,
                    ssf.is_paid,
                    ssf.is_filing_done,
                    ssf.is_acknowledgement_captured,
                    ssf.is_e_verification_required,
                    ssf.is_e_verification_done,
                    ssf.e_verification_due_date,
                    ssf.is_overdue,
                    ssf.is_client_paid,
                    ssf.is_consultant_payment_pending,
                    crm.full_name AS assigned_crm_name,
                    acrm.full_name AS assistant_crm_name,
                    backend.full_name AS backend_name,
                    deo.full_name AS deo_name
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN companies comp ON comp.id = so.company_id
             INNER JOIN financial_years fy ON fy.id = so.financial_year_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
             LEFT JOIN users crm ON crm.id = so.assigned_crm_id
             LEFT JOIN users acrm ON acrm.id = so.assigned_assistant_crm_id
             LEFT JOIN users backend ON backend.id = so.assigned_backend_id
             LEFT JOIN users deo ON deo.id = so.assigned_deo_id
             WHERE so.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function clientIdForServiceOrder(int $serviceOrderId): ?int
    {
        $statement = Database::connection()->prepare(
            "SELECT client_id
             FROM service_orders
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $serviceOrderId]);

        $clientId = $statement->fetchColumn();
        return $clientId === false ? null : (int) $clientId;
    }

    public function create(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO service_orders (
                so_no, client_id, company_id, financial_year_id, pre_service_order_id,
                service_type_id, workflow_definition_id, work_basis, compliance_subtype, assessment_year,
                itr_case_nature, itr_tax_audit_applicable,
                period_month, period_quarter, period_year, period_label, title, description, priority_level,
                assigned_crm_id, assigned_assistant_crm_id, assigned_backend_id, assigned_deo_id,
                current_stage_code, sla_due_at, created_by, created_at, updated_at
            ) VALUES (
                :so_no, :client_id, :company_id, :financial_year_id, :pre_service_order_id,
                :service_type_id, :workflow_definition_id, :work_basis, :compliance_subtype, :assessment_year,
                :itr_case_nature, :itr_tax_audit_applicable,
                :period_month, :period_quarter, :period_year, :period_label, :title, :description, :priority_level,
                :assigned_crm_id, :assigned_assistant_crm_id, :assigned_backend_id, :assigned_deo_id,
                :current_stage_code, :sla_due_at, :created_by, NOW(), NOW()
            )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function insertStatusFlags(array $payload): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO service_order_status_flags (
                service_order_id, is_document_pending, is_payment_pending, is_paid, is_filing_done,
                is_acknowledgement_captured, is_e_verification_required, is_e_verification_done,
                e_verification_due_date, is_overdue, is_client_paid, is_consultant_payment_pending,
                created_at, updated_at
            ) VALUES (
                :service_order_id, :is_document_pending, :is_payment_pending, :is_paid, :is_filing_done,
                :is_acknowledgement_captured, :is_e_verification_required, :is_e_verification_done,
                :e_verification_due_date, :is_overdue, :is_client_paid, :is_consultant_payment_pending,
                NOW(), NOW()
            )"
        );
        $statement->execute($payload);
    }

    public function insertClosureRows(int $serviceOrderId): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO service_order_closures (service_order_id, closure_type, closure_status, created_at, updated_at)
             VALUES
             (:service_order_id_procedural, 'PROCEDURAL', 'PENDING', NOW(), NOW()),
             (:service_order_id_accounting, 'ACCOUNTING', 'PENDING', NOW(), NOW()),
             (:service_order_id_final, 'FINAL', 'PENDING', NOW(), NOW())"
        );
        $statement->execute([
            'service_order_id_procedural' => $serviceOrderId,
            'service_order_id_accounting' => $serviceOrderId,
            'service_order_id_final' => $serviceOrderId,
        ]);
    }

    public function insertStageHistory(int $serviceOrderId, int $userId, string $stageCode, string $stageName): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO workflow_stage_history (
                service_order_id, stage_code, stage_name, entered_at, entered_by, remarks
             ) VALUES (
                :service_order_id, :stage_code, :stage_name, NOW(), :entered_by, :remarks
             )"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'stage_code' => $stageCode,
            'stage_name' => $stageName,
            'entered_by' => $userId,
            'remarks' => 'Initial stage on service order creation',
        ]);
    }

    public function insertTransitionLog(int $serviceOrderId, int $userId, string $toStageCode): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO workflow_transition_logs (
                service_order_id, from_stage_code, to_stage_code, transition_type, transition_notes, triggered_by, triggered_at
             ) VALUES (
                :service_order_id, NULL, :to_stage_code, 'SYSTEM', :transition_notes, :triggered_by, NOW()
             )"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'to_stage_code' => $toStageCode,
            'transition_notes' => 'Service order initialized',
            'triggered_by' => $userId,
        ]);
    }

    public function recordActivity(
        int $userId,
        int $serviceOrderId,
        string $actionCode,
        string $description,
        string $moduleCode = 'SERVICE_ORDERS'
    ): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO activity_logs (
                user_id, module_code, action_code, entity_type, entity_id, description, created_at
             ) VALUES (
                :user_id, :module_code, :action_code, 'service_orders', :entity_id, :description, NOW()
             )"
        );
        $statement->execute([
            'user_id' => $userId,
            'module_code' => $moduleCode,
            'action_code' => $actionCode,
            'entity_id' => $serviceOrderId,
            'description' => $description,
        ]);
    }

    public function lockForUpdate(int $serviceOrderId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.*,
                    st.code AS service_type_code,
                    st.name AS service_type_name,
                    ssf.is_document_pending,
                    ssf.is_payment_pending,
                    ssf.is_paid,
                    ssf.is_filing_done,
                    ssf.is_acknowledgement_captured,
                    ssf.is_e_verification_required,
                    ssf.is_e_verification_done,
                    ssf.e_verification_due_date,
                    ssf.is_overdue,
                    ssf.is_client_paid,
                    ssf.is_consultant_payment_pending
             FROM service_orders so
             INNER JOIN service_types st ON st.id = so.service_type_id
             LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
             WHERE so.id = :id
             LIMIT 1
             FOR UPDATE"
        );
        $statement->execute(['id' => $serviceOrderId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function updateCurrentStage(int $serviceOrderId, string $stageCode): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_orders
             SET current_stage_code = :stage_code,
                 last_stage_changed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'stage_code' => $stageCode,
            'id' => $serviceOrderId,
        ]);
    }

    public function closeCurrentStageHistory(int $serviceOrderId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE workflow_stage_history
             SET exited_at = NOW()
             WHERE service_order_id = :service_order_id
               AND exited_at IS NULL"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);
    }

    public function appendStageHistory(int $serviceOrderId, int $userId, string $stageCode, string $stageName, string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO workflow_stage_history (
                service_order_id, stage_code, stage_name, entered_at, entered_by, remarks
             ) VALUES (
                :service_order_id, :stage_code, :stage_name, NOW(), :entered_by, :remarks
             )"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'stage_code' => $stageCode,
            'stage_name' => $stageName,
            'entered_by' => $userId,
            'remarks' => $remarks,
        ]);
    }

    public function appendTransitionLog(
        int $serviceOrderId,
        ?string $fromStageCode,
        string $toStageCode,
        string $transitionType,
        string $notes,
        int $userId
    ): void {
        $statement = Database::connection()->prepare(
            "INSERT INTO workflow_transition_logs (
                service_order_id, from_stage_code, to_stage_code, transition_type, transition_notes, triggered_by, triggered_at
             ) VALUES (
                :service_order_id, :from_stage_code, :to_stage_code, :transition_type, :transition_notes, :triggered_by, NOW()
             )"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'from_stage_code' => $fromStageCode,
            'to_stage_code' => $toStageCode,
            'transition_type' => $transitionType,
            'transition_notes' => $notes,
            'triggered_by' => $userId,
        ]);
    }

    public function updateStatusFlags(int $serviceOrderId, array $fields): void
    {
        if ($fields === []) {
            return;
        }

        $assignments = [];
        $params = ['service_order_id' => $serviceOrderId];

        foreach ($fields as $column => $value) {
            $assignments[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = "UPDATE service_order_status_flags SET " . implode(', ', $assignments) . ", updated_at = NOW() WHERE service_order_id = :service_order_id";
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
    }

    public function updateWorkflowMetadata(int $serviceOrderId, array $fields): void
    {
        if ($fields === []) {
            return;
        }

        $assignments = [];
        $params = ['id' => $serviceOrderId];

        foreach ($fields as $column => $value) {
            $assignments[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = "UPDATE service_orders SET " . implode(', ', $assignments) . ", updated_at = NOW() WHERE id = :id";
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
    }

    public function updateClosure(int $serviceOrderId, string $closureType, string $status, ?string $blockReason, ?string $notes, ?int $closedBy = null): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_order_closures
             SET closure_status = :closure_status_set,
                 closure_at = CASE WHEN :closure_status_case = 'COMPLETED' THEN NOW() ELSE closure_at END,
                 closed_by = CASE WHEN :closure_status_closed_by = 'COMPLETED' THEN :closed_by_value ELSE closed_by END,
                 block_reason = :block_reason,
                 notes = :notes,
                 updated_at = NOW()
             WHERE service_order_id = :service_order_id
               AND closure_type = :closure_type"
        );
        $statement->execute([
            'closure_status_set' => $status,
            'closure_status_case' => $status,
            'closure_status_closed_by' => $status,
            'closed_by_value' => $closedBy,
            'block_reason' => $blockReason,
            'notes' => $notes,
            'service_order_id' => $serviceOrderId,
            'closure_type' => $closureType,
        ]);
    }

    public function markProceduralClosed(int $serviceOrderId, int $userId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_orders
             SET procedural_closed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute(['id' => $serviceOrderId]);
    }

    public function markAccountingClosed(int $serviceOrderId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_orders
             SET accounting_closed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute(['id' => $serviceOrderId]);
    }

    public function markFinalClosed(int $serviceOrderId, int $userId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_orders
             SET final_closed_at = NOW(),
                 final_closed_by = :final_closed_by,
                 is_locked = 1,
                 lock_reason = 'Final closure lock',
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'final_closed_by' => $userId,
            'id' => $serviceOrderId,
        ]);
    }

    public function reminderExists(int $serviceOrderId, string $reminderType, int $scheduleDayNo): bool
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*)
             FROM reminders
             WHERE service_order_id = :service_order_id
               AND reminder_type = :reminder_type
               AND schedule_day_no = :schedule_day_no"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'reminder_type' => $reminderType,
            'schedule_day_no' => $scheduleDayNo,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function insertReminder(int $serviceOrderId, string $reminderType, int $scheduleDayNo, string $dueAt, ?int $assignedTo, int $createdBy, string $notes): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO reminders (
                service_order_id, reminder_type, schedule_day_no, due_at, status, assigned_to, created_by, notes, created_at
             ) VALUES (
                :service_order_id, :reminder_type, :schedule_day_no, :due_at, 'PENDING', :assigned_to, :created_by, :notes, NOW()
             )"
        );
        $statement->execute([
            'service_order_id' => $serviceOrderId,
            'reminder_type' => $reminderType,
            'schedule_day_no' => $scheduleDayNo,
            'due_at' => $dueAt,
            'assigned_to' => $assignedTo,
            'created_by' => $createdBy,
            'notes' => $notes,
        ]);

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

    public function markOverdueReminders(int $serviceOrderId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE reminders
             SET status = 'OVERDUE'
             WHERE service_order_id = :service_order_id
               AND reminder_type = 'E_VERIFICATION'
               AND status IN ('PENDING', 'SENT')
               AND due_at < NOW()"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);
    }

    public function markReminderDone(int $serviceOrderId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE reminders
             SET status = 'DONE'
             WHERE service_order_id = :service_order_id
               AND reminder_type = 'E_VERIFICATION'
               AND status IN ('PENDING', 'SENT', 'OVERDUE')"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);
    }

    public function logReminderFollowUp(int $reminderId, int $userId, string $note): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO reminder_logs (reminder_id, action_type, action_by, action_note, action_at)
             VALUES (:reminder_id, 'FOLLOW_UP_LOGGED', :action_by, :action_note, NOW())"
        );
        $statement->execute([
            'reminder_id' => $reminderId,
            'action_by' => $userId,
            'action_note' => $note,
        ]);
    }
}
