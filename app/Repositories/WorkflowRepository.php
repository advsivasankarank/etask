<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Logger;
use PDO;
use PDOException;

final class WorkflowRepository
{
    private static ?bool $milestoneTableAvailable = null;

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
            "SELECT wsh.stage_code,
                    wsh.stage_name,
                    wsh.entered_at,
                    wsh.exited_at,
                    wsh.remarks,
                    wsh.entered_by,
                    u.full_name AS entered_by_name
             FROM workflow_stage_history wsh
             LEFT JOIN users u ON u.id = wsh.entered_by
             WHERE wsh.service_order_id = :service_order_id
             ORDER BY wsh.id ASC"
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
            "SELECT soc.closure_type,
                    soc.closure_status,
                    soc.closure_at,
                    soc.block_reason,
                    soc.notes,
                    soc.closed_by,
                    u.full_name AS closed_by_name
             FROM service_order_closures soc
             LEFT JOIN users u ON u.id = soc.closed_by
             WHERE soc.service_order_id = :service_order_id
             ORDER BY soc.id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function milestoneTrackers(int $serviceOrderId): array
    {
        if (!$this->milestoneTableExists()) {
            return [];
        }

        try {
            $statement = Database::connection()->prepare(
                "SELECT som.stage_code,
                        som.stage_name,
                        som.tracking_status,
                        som.remarks,
                        som.completed_at,
                        som.completed_by,
                        som.updated_at,
                        som.updated_by,
                        cu.full_name AS completed_by_name,
                        uu.full_name AS updated_by_name
                 FROM service_order_milestones som
                 LEFT JOIN users cu ON cu.id = som.completed_by
                 LEFT JOIN users uu ON uu.id = som.updated_by
                 WHERE som.service_order_id = :service_order_id
                 ORDER BY som.id ASC"
            );
            $statement->execute(['service_order_id' => $serviceOrderId]);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            if ($this->isMissingMilestoneTableException($exception)) {
                self::$milestoneTableAvailable = false;
                Logger::warning('workflow.milestone_table_missing_runtime', [
                    'service_order_id' => $serviceOrderId,
                    'sql_state' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
                return [];
            }

            throw $exception;
        }
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

    private function milestoneTableExists(): bool
    {
        if (self::$milestoneTableAvailable !== null) {
            return self::$milestoneTableAvailable;
        }

        try {
            $statement = Database::connection()->query(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'service_order_milestones'"
            );

            self::$milestoneTableAvailable = (int) $statement->fetchColumn() === 1;
        } catch (PDOException $exception) {
            if ($this->isMissingMilestoneTableException($exception)) {
                self::$milestoneTableAvailable = false;
            } else {
                throw $exception;
            }
        }

        if (self::$milestoneTableAvailable === false) {
            Logger::warning('workflow.milestone_table_missing', [
                'table' => 'service_order_milestones',
            ]);
        }

        return self::$milestoneTableAvailable;
    }

    private function isMissingMilestoneTableException(PDOException $exception): bool
    {
        $message = strtoupper($exception->getMessage());
        $code = strtoupper((string) $exception->getCode());

        return $code === '42S02'
            || str_contains($message, '1146')
            || str_contains($message, 'SERVICE_ORDER_MILESTONES')
            || str_contains($message, 'BASE TABLE OR VIEW NOT FOUND');
    }
}
