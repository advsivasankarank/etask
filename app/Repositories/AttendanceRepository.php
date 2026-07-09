<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AttendanceRepository
{
    public function createSession(int $userId, ?string $ipAddress, ?string $userAgent): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO attendance_sessions (user_id, login_at, ip_address, user_agent, created_at)
             VALUES (:user_id, NOW(), :ip_address, :user_agent, NOW())"
        );
        $statement->execute([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function closeSession(int $sessionId, string $logoutAt): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE attendance_sessions SET logout_at = :logout_at WHERE id = :id"
        );
        $statement->execute([
            'logout_at' => $logoutAt,
            'id' => $sessionId,
        ]);
    }

    public function updateSessionDurations(int $sessionId, int $activeSeconds, int $idleSeconds): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE attendance_sessions
             SET total_active_seconds = :active, total_idle_seconds = :idle
             WHERE id = :id"
        );
        $statement->execute([
            'active' => $activeSeconds,
            'idle' => $idleSeconds,
            'id' => $sessionId,
        ]);
    }

    public function findOpenSession(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM attendance_sessions
             WHERE user_id = :user_id AND logout_at IS NULL
             ORDER BY login_at DESC LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findTodaysSession(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM attendance_sessions
             WHERE user_id = :user_id AND DATE(login_at) = CURDATE()
             ORDER BY login_at DESC LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findSessionById(int $sessionId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM attendance_sessions WHERE id = :id"
        );
        $statement->execute(['id' => $sessionId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function startActivity(int $sessionId, int $userId, string $activityType, ?int $serviceOrderId, ?int $taskId, ?string $remarks): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO attendance_activity_logs
             (attendance_session_id, user_id, service_order_task_id, activity_type, started_at, remarks, created_at)
             VALUES (:session_id, :user_id, :task_id, :activity_type, NOW(), :remarks, NOW())"
        );
        $statement->execute([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'task_id' => $taskId,
            'activity_type' => $activityType,
            'remarks' => $remarks,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function stopActivity(int $activityId, ?string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE attendance_activity_logs
             SET ended_at = NOW(),
                 duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()),
                 remarks = COALESCE(:remarks, remarks)
             WHERE id = :id AND ended_at IS NULL"
        );
        $statement->execute([
            'remarks' => $remarks,
            'id' => $activityId,
        ]);
    }

    public function findOpenActivity(int $sessionId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM attendance_activity_logs
             WHERE attendance_session_id = :session_id AND ended_at IS NULL
             ORDER BY started_at DESC LIMIT 1"
        );
        $statement->execute(['session_id' => $sessionId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findOpenActivityByUser(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT aal.* FROM attendance_activity_logs aal
             INNER JOIN attendance_sessions att ON att.id = aal.attendance_session_id
             WHERE aal.user_id = :user_id AND aal.ended_at IS NULL AND att.logout_at IS NULL
             ORDER BY aal.started_at DESC LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function closeOpenActivities(int $sessionId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE attendance_activity_logs
             SET ended_at = NOW(),
                 duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW())
             WHERE attendance_session_id = :session_id AND ended_at IS NULL"
        );
        $statement->execute(['session_id' => $sessionId]);
    }

    public function getTodaysActivities(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT aal.*, so.so_no, so.title AS so_title
             FROM attendance_activity_logs aal
             INNER JOIN attendance_sessions att ON att.id = aal.attendance_session_id
             LEFT JOIN service_order_tasks sot ON sot.id = aal.service_order_task_id
             LEFT JOIN service_orders so ON so.id = sot.service_order_id
             WHERE aal.user_id = :user_id AND DATE(aal.started_at) = CURDATE()
             ORDER BY aal.started_at ASC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodaysActivitySummary(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN activity_type IN ('ACTIVE','TASK_LINKED') THEN duration_seconds ELSE 0 END), 0) AS active_seconds,
                COALESCE(SUM(CASE WHEN activity_type IN ('IDLE','BREAK') THEN duration_seconds ELSE 0 END), 0) AS idle_seconds,
                COUNT(*) AS total_activities
             FROM attendance_activity_logs aal
             INNER JOIN attendance_sessions att ON att.id = aal.attendance_session_id
             WHERE aal.user_id = :user_id AND DATE(aal.started_at) = CURDATE()
               AND aal.ended_at IS NOT NULL"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: ['active_seconds' => 0, 'idle_seconds' => 0, 'total_activities' => 0];
    }

    public function saveDailyReport(array $data, ?int $existingId = null): int
    {
        if ($existingId !== null && $existingId > 0) {
            $statement = Database::connection()->prepare(
                "UPDATE daily_work_reports
                 SET work_done_today = :work_done_today,
                     pending_work = :pending_work,
                     tomorrow_plan = :tomorrow_plan,
                     issues_faced = :issues_faced,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id"
            );
            $statement->execute([
                'work_done_today' => $data['work_done_today'],
                'pending_work' => $data['pending_work'] ?: null,
                'tomorrow_plan' => $data['tomorrow_plan'] ?: null,
                'issues_faced' => $data['issues_faced'] ?: null,
                'status' => $data['status'],
                'id' => $existingId,
            ]);

            return $existingId;
        }

        $statement = Database::connection()->prepare(
            "INSERT INTO daily_work_reports
             (user_id, attendance_session_id, report_date, work_done_today, pending_work, tomorrow_plan, issues_faced, status, created_at)
             VALUES (:user_id, :session_id, CURDATE(), :work_done_today, :pending_work, :tomorrow_plan, :issues_faced, :status, NOW())"
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'session_id' => $data['attendance_session_id'] ?? null,
            'work_done_today' => $data['work_done_today'],
            'pending_work' => $data['pending_work'] ?: null,
            'tomorrow_plan' => $data['tomorrow_plan'] ?: null,
            'issues_faced' => $data['issues_faced'] ?: null,
            'status' => $data['status'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function findTodaysReport(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT dwr.*, u.full_name AS reviewed_by_name
             FROM daily_work_reports dwr
             LEFT JOIN users u ON u.id = dwr.reviewed_by
             WHERE dwr.user_id = :user_id AND dwr.report_date = CURDATE()
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findReportById(int $reportId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT dwr.*, u.full_name AS staff_name, rv.full_name AS reviewed_by_name
             FROM daily_work_reports dwr
             INNER JOIN users u ON u.id = dwr.user_id
             LEFT JOIN users rv ON rv.id = dwr.reviewed_by
             WHERE dwr.id = :id"
        );
        $statement->execute(['id' => $reportId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function reviewReport(int $reportId, int $reviewedBy, string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE daily_work_reports
             SET reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 admin_remarks = :remarks,
                 status = 'REVIEWED'
             WHERE id = :id"
        );
        $statement->execute([
            'reviewed_by' => $reviewedBy,
            'remarks' => $remarks,
            'id' => $reportId,
        ]);
    }

    public function reopenReport(int $reportId, string $remarks): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE daily_work_reports
             SET admin_remarks = :remarks,
                 status = 'REOPENED'
             WHERE id = :id"
        );
        $statement->execute([
            'remarks' => $remarks,
            'id' => $reportId,
        ]);
    }

    public function getAdminReports(string $date, ?int $staffId, ?string $status, int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM daily_work_reports dwr WHERE dwr.report_date = :date";
        $dataSql = "SELECT dwr.*, u.full_name AS staff_name, rv.full_name AS reviewed_by_name,
                           att.login_at, att.logout_at, att.total_active_seconds, att.total_idle_seconds
                    FROM daily_work_reports dwr
                    INNER JOIN users u ON u.id = dwr.user_id
                    LEFT JOIN users rv ON rv.id = dwr.reviewed_by
                    LEFT JOIN attendance_sessions att ON att.user_id = dwr.user_id AND DATE(att.login_at) = dwr.report_date
                    WHERE dwr.report_date = :date";

        $params = ['date' => $date];

        if ($staffId !== null && $staffId > 0) {
            $countSql .= " AND dwr.user_id = :staff_id";
            $dataSql .= " AND dwr.user_id = :staff_id";
            $params['staff_id'] = $staffId;
        }

        if ($status !== '' && $status !== null) {
            $countSql .= " AND dwr.status = :status";
            $dataSql .= " AND dwr.status = :status";
            $params['status'] = $status;
        }

        $countStatement = Database::connection()->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataSql .= " ORDER BY dwr.report_date DESC, u.full_name ASC LIMIT :limit OFFSET :offset";

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

    public function getProductivitySummary(string $dateFrom, string $dateTo, ?int $staffId, ?int $serviceOrderId): array
    {
        $sql = "SELECT
                    u.id AS user_id,
                    u.full_name,
                    COUNT(DISTINCT att.id) AS login_days,
                    COALESCE(SUM(att.total_active_seconds), 0) AS total_active_seconds,
                    COALESCE(SUM(att.total_idle_seconds), 0) AS total_idle_seconds,
                    COUNT(DISTINCT CASE WHEN dwr.id IS NOT NULL THEN dwr.id END) AS reports_submitted,
                    COUNT(DISTINCT CASE WHEN dwr.id IS NULL THEN att.login_at END) AS reports_missing
                FROM users u
                INNER JOIN user_role_map urm ON urm.user_id = u.id
                INNER JOIN roles r ON r.id = urm.role_id
                LEFT JOIN attendance_sessions att ON att.user_id = u.id
                    AND DATE(att.login_at) BETWEEN :date_from AND :date_to
                LEFT JOIN daily_work_reports dwr ON dwr.user_id = u.id
                    AND dwr.report_date BETWEEN :date_from AND :date_to
                WHERE r.scope = 'SYSTEM' AND r.code != 'CLIENT' AND u.is_active = 1";

        $params = ['date_from' => $dateFrom, 'date_to' => $dateTo];

        if ($staffId !== null && $staffId > 0) {
            $sql .= " AND u.id = :staff_id";
            $params['staff_id'] = $staffId;
        }

        $sql .= " GROUP BY u.id, u.full_name ORDER BY u.full_name ASC";

        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductivityByServiceOrder(string $dateFrom, string $dateTo, ?int $staffId): array
    {
        $sql = "SELECT
                    u.full_name AS staff_name,
                    so.so_no,
                    so.title AS so_title,
                    c.legal_name AS client_name,
                    COUNT(aal.id) AS activity_count,
                    COALESCE(SUM(aal.duration_seconds), 0) AS total_seconds,
                    MAX(aal.started_at) AS last_activity_at
                FROM attendance_activity_logs aal
                INNER JOIN users u ON u.id = aal.user_id
                INNER JOIN attendance_sessions att ON att.id = aal.attendance_session_id
                LEFT JOIN service_order_tasks sot ON sot.id = aal.service_order_task_id
                LEFT JOIN service_orders so ON so.id = sot.service_order_id
                LEFT JOIN clients c ON c.id = so.client_id
                WHERE DATE(aal.started_at) BETWEEN :date_from AND :date_to
                  AND aal.ended_at IS NOT NULL
                  AND aal.activity_type IN ('ACTIVE','TASK_LINKED')";

        $params = ['date_from' => $dateFrom, 'date_to' => $dateTo];

        if ($staffId !== null && $staffId > 0) {
            $sql .= " AND aal.user_id = :staff_id";
            $params['staff_id'] = $staffId;
        }

        $sql .= " GROUP BY u.full_name, so.so_no, so.title, c.legal_name
                  ORDER BY total_seconds DESC";

        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasMissingReports(int $userId): bool
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*)
             FROM attendance_sessions att
             WHERE att.user_id = :user_id
               AND att.login_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
               AND DATE(att.login_at) != CURDATE()
               AND NOT EXISTS (
                   SELECT 1 FROM daily_work_reports dwr
                   WHERE dwr.user_id = att.user_id AND dwr.report_date = DATE(att.login_at)
               )
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function hasTodaysReport(int $userId): bool
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*)
             FROM daily_work_reports
             WHERE user_id = :user_id AND report_date = CURDATE()
               AND status IN ('SUBMITTED','REVIEWED')
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function getActiveStaffMembers(): array
    {
        $statement = Database::connection()->query(
            "SELECT u.id, u.full_name, u.username
             FROM users u
             INNER JOIN user_role_map urm ON urm.user_id = u.id
             INNER JOIN roles r ON r.id = urm.role_id
             WHERE r.scope = 'SYSTEM' AND r.code != 'CLIENT' AND u.is_active = 1
             ORDER BY u.full_name ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getServiceOrders(): array
    {
        $statement = Database::connection()->query(
            "SELECT so.id, so.so_no, so.title, c.legal_name AS client_name
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             WHERE so.archived_at IS NULL
             ORDER BY so.so_no DESC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, full_name, username FROM users WHERE id = :id"
        );
        $statement->execute(['id' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
