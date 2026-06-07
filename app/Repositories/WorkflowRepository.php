<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class WorkflowRepository
{
    public function activeByServiceType(int $serviceTypeId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, service_type_id, version_no, name
             FROM workflow_definitions
             WHERE service_type_id = :service_type_id
               AND is_active = 1
             ORDER BY version_no DESC
             LIMIT 1"
        );
        $statement->execute(['service_type_id' => $serviceTypeId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function stageDefinitions(int $workflowDefinitionId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, stage_code, stage_name, stage_group, sort_order, is_milestone_click_required, auto_trigger_on, is_terminal
             FROM workflow_stage_definitions
             WHERE workflow_definition_id = :workflow_definition_id
             ORDER BY sort_order ASC"
        );
        $statement->execute(['workflow_definition_id' => $workflowDefinitionId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function stageHistory(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT stage_code, stage_name, entered_at, exited_at, remarks
             FROM workflow_stage_history
             WHERE service_order_id = :service_order_id
             ORDER BY id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reminders(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, reminder_type, schedule_day_no, due_at, sent_at, status, notes
             FROM reminders
             WHERE service_order_id = :service_order_id
             ORDER BY due_at ASC, id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closures(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT closure_type, closure_status, closure_at, block_reason, notes
             FROM service_order_closures
             WHERE service_order_id = :service_order_id
             ORDER BY id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function consultantOutstandingAmount(int $serviceOrderId): float
    {
        $statement = Database::connection()->prepare(
            "SELECT COALESCE(SUM(cb.total_amount), 0) - COALESCE(SUM(cp.amount), 0) AS outstanding_amount
             FROM consultant_assignments ca
             LEFT JOIN consultant_bills cb ON cb.consultant_assignment_id = ca.id AND cb.review_status = 'APPROVED'
             LEFT JOIN consultant_payments cp ON cp.consultant_bill_id = cb.id
             WHERE ca.service_order_id = :service_order_id"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return (float) ($statement->fetchColumn() ?: 0.0);
    }
}
