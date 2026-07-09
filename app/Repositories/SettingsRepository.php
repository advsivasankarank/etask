<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class SettingsRepository
{
    public function summaryCounts(): array
    {
        return [
            'active_service_types' => $this->scalar("SELECT COUNT(*) FROM service_types WHERE is_active = 1"),
            'active_reminder_templates' => $this->scalar("SELECT COUNT(*) FROM reminder_templates WHERE is_active = 1"),
            'total_companies' => $this->scalar("SELECT COUNT(*) FROM companies WHERE is_active = 1"),
            'total_milestones' => $this->scalar("SELECT COUNT(*) FROM workflow_stage_definitions"),
            'last_maintenance' => $this->scalarText("SELECT action_type FROM maintenance_logs ORDER BY id DESC LIMIT 1"),
        ];
    }

    public function getSetting(string $group, string $key, string $default = ''): string
    {
        $statement = Database::connection()->prepare(
            "SELECT setting_value FROM app_settings WHERE setting_group = :group AND setting_key = :key LIMIT 1"
        );
        $statement->execute(['group' => $group, 'key' => $key]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? (string) ($row['setting_value'] ?? $default) : $default;
    }

    public function saveSetting(string $group, string $key, ?string $value, int $userId): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO app_settings (setting_group, setting_key, setting_value, updated_by)
             VALUES (:group, :key, :value, :updated_by)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()"
        );
        $statement->execute(['group' => $group, 'key' => $key, 'value' => $value, 'updated_by' => $userId]);
    }

    public function getCompany(): ?array
    {
        $statement = Database::connection()->prepare("SELECT * FROM companies WHERE is_active = 1 LIMIT 1");
        $statement->execute();
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function updateCompany(array $data, int $id): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE companies SET legal_name = :legal_name, display_name = :display_name, pan = :pan, gstin = :gstin, tan = :tan, email = :email, mobile = :mobile, phone = :phone, address_line1 = :address_line1, address_line2 = :address_line2, city = :city, state_name = :state_name, postal_code = :postal_code WHERE id = :id"
        );
        $statement->execute([
            'legal_name' => $data['legal_name'] ?? '',
            'display_name' => $data['display_name'] ?? '',
            'pan' => $data['pan'] ?? '',
            'gstin' => $data['gstin'] ?? '',
            'tan' => $data['tan'] ?? '',
            'email' => $data['email'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address_line1' => $data['address_line1'] ?? '',
            'address_line2' => $data['address_line2'] ?? '',
            'city' => $data['city'] ?? '',
            'state_name' => $data['state_name'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'id' => $id,
        ]);
    }

    public function allServiceTypes(): array
    {
        $statement = Database::connection()->prepare("SELECT * FROM service_types ORDER BY name ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allReminderTemplates(): array
    {
        $statement = Database::connection()->prepare("SELECT * FROM reminder_templates ORDER BY code ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allMilestones(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT wsd.*, st.name AS service_type_name
             FROM workflow_stage_definitions wsd
             LEFT JOIN workflow_definitions wd ON wd.id = wsd.workflow_definition_id
             LEFT JOIN service_types st ON st.id = wd.service_type_id
             ORDER BY wsd.sort_order ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logMaintenance(string $actionType, ?string $actionNote, int $userId): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO maintenance_logs (action_type, action_note, performed_by, status)
             VALUES (:action_type, :action_note, :performed_by, 'COMPLETED')"
        );
        $statement->execute(['action_type' => $actionType, 'action_note' => $actionNote, 'performed_by' => $userId]);
    }

    public function recentMaintenanceLogs(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT ml.*, u.full_name AS performed_by_name
             FROM maintenance_logs ml
             LEFT JOIN users u ON u.id = ml.performed_by
             ORDER BY ml.id DESC
             LIMIT 20"
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

    private function scalarText(string $sql, array $params = []): string
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchColumn();
        return $result !== false ? (string) $result : '';
    }
}
