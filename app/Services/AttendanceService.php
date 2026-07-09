<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Logger;
use App\Repositories\AttendanceRepository;
use RuntimeException;

final class AttendanceService
{
    private AttendanceRepository $attendance;

    public function __construct()
    {
        $this->attendance = new AttendanceRepository();
    }

    public function startAttendanceSession(int $userId, ?string $ipAddress, ?string $userAgent): int
    {
        try {
            $sessionId = $this->attendance->createSession($userId, $ipAddress, $userAgent);
            Logger::info('attendance.session_started', ['user_id' => $userId, 'session_id' => $sessionId]);
            return $sessionId;
        } catch (\Throwable $throwable) {
            Logger::error('attendance.session_start_failed', ['user_id' => $userId, 'error' => $throwable->getMessage()]);
            return 0;
        }
    }

    public function closeAttendanceSession(int $sessionId): void
    {
        try {
            $session = $this->attendance->findSessionById($sessionId);
            if ($session === null) {
                return;
            }

            $this->attendance->closeOpenActivities($sessionId);

            $activeSeconds = 0;
            $idleSeconds = 0;
            $summary = $this->computeSessionDurations($sessionId);
            $activeSeconds = $summary['active_seconds'];
            $idleSeconds = $summary['idle_seconds'];

            $this->attendance->updateSessionDurations($sessionId, $activeSeconds, $idleSeconds);
            $this->attendance->closeSession($sessionId, date('Y-m-d H:i:s'));

            Logger::info('attendance.session_closed', [
                'session_id' => $sessionId,
                'active_seconds' => $activeSeconds,
                'idle_seconds' => $idleSeconds,
            ]);
        } catch (\Throwable $throwable) {
            Logger::error('attendance.session_close_failed', [
                'session_id' => $sessionId,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    public function getTodaysSession(int $userId): ?array
    {
        return $this->attendance->findTodaysSession($userId);
    }

    public function getOpenSession(int $userId): ?array
    {
        return $this->attendance->findOpenSession($userId);
    }

    public function startWorkActivity(int $userId, string $activityType, ?int $serviceOrderId, ?int $taskId, ?string $remarks): int
    {
        $session = $this->attendance->findOpenSession($userId);
        if ($session === null) {
            throw new RuntimeException('No active attendance session. Please log in again.');
        }

        $openActivity = $this->attendance->findOpenActivity((int) $session['id']);
        if ($openActivity !== null) {
            $this->attendance->stopActivity((int) $openActivity['id'], null);
        }

        $activityId = $this->attendance->startActivity(
            (int) $session['id'],
            $userId,
            $activityType,
            $serviceOrderId,
            $taskId,
            $remarks
        );

        Logger::info('attendance.activity_started', [
            'user_id' => $userId,
            'activity_id' => $activityId,
            'activity_type' => $activityType,
            'service_order_id' => $serviceOrderId,
        ]);

        return $activityId;
    }

    public function stopWorkActivity(int $userId, ?string $remarks): void
    {
        $openActivity = $this->attendance->findOpenActivityByUser($userId);
        if ($openActivity === null) {
            throw new RuntimeException('No active work activity to stop.');
        }

        $this->attendance->stopActivity((int) $openActivity['id'], $remarks);

        Logger::info('attendance.activity_stopped', [
            'user_id' => $userId,
            'activity_id' => (int) $openActivity['id'],
        ]);
    }

    public function pauseActivity(int $userId): int
    {
        $session = $this->attendance->findOpenSession($userId);
        if ($session === null) {
            throw new RuntimeException('No active attendance session.');
        }

        $openActivity = $this->attendance->findOpenActivity((int) $session['id']);
        if ($openActivity !== null) {
            $this->attendance->stopActivity((int) $openActivity['id'], null);
        }

        $activityId = $this->attendance->startActivity(
            (int) $session['id'],
            $userId,
            'BREAK',
            null,
            null,
            'Break / Pause'
        );

        Logger::info('attendance.activity_paused', ['user_id' => $userId, 'activity_id' => $activityId]);

        return $activityId;
    }

    public function resumeActivity(int $userId): void
    {
        $session = $this->attendance->findOpenSession($userId);
        if ($session === null) {
            throw new RuntimeException('No active attendance session.');
        }

        $openActivity = $this->attendance->findOpenActivity((int) $session['id']);
        if ($openActivity !== null) {
            $this->attendance->stopActivity((int) $openActivity['id'], null);
        }

        Logger::info('attendance.activity_resumed', ['user_id' => $userId]);
    }

    public function getTodaysDashboard(int $userId): array
    {
        $session = $this->attendance->findTodaysSession($userId);
        $activities = $this->attendance->getTodaysActivities($userId);
        $summary = $this->attendance->getTodaysActivitySummary($userId);
        $report = $this->attendance->findTodaysReport($userId);
        $openActivity = $this->attendance->findOpenActivityByUser($userId);

        $loginTime = $session ? date('h:i A', strtotime($session['login_at'])) : null;
        $logoutTime = ($session && $session['logout_at']) ? date('h:i A', strtotime($session['logout_at'])) : null;
        $isActive = $session !== null && $session['logout_at'] === null;

        $activeHours = (int) ($summary['active_seconds'] / 3600);
        $activeMinutes = ((int) ($summary['active_seconds'] % 3600)) / 60;
        $idleHours = (int) ($summary['idle_seconds'] / 3600);
        $idleMinutes = ((int) ($summary['idle_seconds'] % 3600)) / 60;

        return [
            'session' => $session,
            'login_time' => $loginTime,
            'logout_time' => $logoutTime,
            'is_active' => $isActive,
            'active_seconds' => (int) $summary['active_seconds'],
            'idle_seconds' => (int) $summary['idle_seconds'],
            'active_duration' => $activeHours . 'h ' . $activeMinutes . 'm',
            'idle_duration' => $idleHours . 'h ' . $idleMinutes . 'm',
            'total_activities' => (int) $summary['total_activities'],
            'report' => $report,
            'report_status' => $report ? $report['status'] : null,
            'open_activity' => $openActivity,
            'current_activity_type' => $openActivity ? $openActivity['activity_type'] : null,
            'activities' => $activities,
        ];
    }

    public function getTodaysReportForm(int $userId): array
    {
        $report = $this->attendance->findTodaysReport($userId);
        $activities = $this->attendance->getTodaysActivities($userId);
        $session = $this->attendance->findTodaysSession($userId);

        $autoDraft = $this->generateAutoDraft($activities);

        return [
            'report' => $report,
            'auto_draft' => $autoDraft,
            'activities' => $activities,
            'session' => $session,
            'can_edit' => $report === null || in_array($report['status'] ?? '', ['DRAFT', 'REOPENED'], true),
        ];
    }

    public function submitDailyReport(int $userId, array $input): int
    {
        $workDone = trim((string) ($input['work_done_today'] ?? ''));
        if ($workDone === '') {
            throw new RuntimeException('Work Done Today is required.');
        }

        $report = $this->attendance->findTodaysReport($userId);
        $existingId = $report ? (int) $report['id'] : null;

        if ($existingId !== null && in_array($report['status'] ?? '', ['REVIEWED'], true)) {
            throw new RuntimeException('This report has been reviewed and cannot be edited.');
        }

        $session = $this->attendance->findTodaysSession($userId);

        $data = [
            'user_id' => $userId,
            'attendance_session_id' => $session ? (int) $session['id'] : null,
            'work_done_today' => $workDone,
            'pending_work' => trim((string) ($input['pending_work'] ?? '')),
            'tomorrow_plan' => trim((string) ($input['tomorrow_plan'] ?? '')),
            'issues_faced' => trim((string) ($input['issues_faced'] ?? '')),
            'status' => 'SUBMITTED',
        ];

        $reportId = $this->attendance->saveDailyReport($data, $existingId);

        Logger::info('attendance.report_submitted', ['user_id' => $userId, 'report_id' => $reportId]);

        return $reportId;
    }

    public function getAdminReports(string $date, ?int $staffId, ?string $status, int $page = 1): array
    {
        return $this->attendance->getAdminReports($date, $staffId, $status, $page);
    }

    public function getReportDetail(int $reportId): array
    {
        $report = $this->attendance->findReportById($reportId);
        if ($report === null) {
            throw new RuntimeException('Report not found.');
        }

        $activities = $this->attendance->getTodaysActivities((int) $report['user_id']);

        return [
            'report' => $report,
            'activities' => $activities,
        ];
    }

    public function reviewReport(int $reportId, int $reviewedBy, string $remarks): void
    {
        $report = $this->attendance->findReportById($reportId);
        if ($report === null) {
            throw new RuntimeException('Report not found.');
        }

        if ((int) $report['user_id'] === $reviewedBy && !Auth::hasRole('SUPER_ADMIN')) {
            throw new RuntimeException('You cannot review your own report.');
        }

        $this->attendance->reviewReport($reportId, $reviewedBy, $remarks);

        Logger::info('attendance.report_reviewed', [
            'report_id' => $reportId,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    public function reopenReport(int $reportId, string $remarks): void
    {
        $report = $this->attendance->findReportById($reportId);
        if ($report === null) {
            throw new RuntimeException('Report not found.');
        }

        $this->attendance->reopenReport($reportId, $remarks);

        Logger::info('attendance.report_reopened', ['report_id' => $reportId]);
    }

    public function getProductivity(string $dateFrom, string $dateTo, ?int $staffId, ?int $serviceOrderId): array
    {
        $staffSummary = $this->attendance->getProductivitySummary($dateFrom, $dateTo, $staffId, $serviceOrderId);
        $soSummary = $this->attendance->getProductivityByServiceOrder($dateFrom, $dateTo, $staffId);

        foreach ($staffSummary as &$row) {
            $activeH = (int) ($row['total_active_seconds'] / 3600);
            $activeM = ((int) ($row['total_active_seconds'] % 3600)) / 60;
            $idleH = (int) ($row['total_idle_seconds'] / 3600);
            $idleM = ((int) ($row['total_idle_seconds'] % 3600)) / 60;
            $row['active_duration'] = $activeH . 'h ' . $activeM . 'm';
            $row['idle_duration'] = $idleH . 'h ' . $idleM . 'm';
            $row['avg_active_per_day'] = $row['login_days'] > 0
                ? round((float) $row['total_active_seconds'] / (float) $row['login_days'] / 60)
                : 0;
        }
        unset($row);

        foreach ($soSummary as &$row) {
            $hours = (int) ($row['total_seconds'] / 3600);
            $minutes = ((int) ($row['total_seconds'] % 3600)) / 60;
            $row['duration'] = $hours . 'h ' . $minutes . 'm';
        }
        unset($row);

        return [
            'staff_summary' => $staffSummary,
            'so_summary' => $soSummary,
        ];
    }

    public function hasMissingReports(int $userId): bool
    {
        return $this->attendance->hasMissingReports($userId);
    }

    public function hasTodaysReport(int $userId): bool
    {
        return $this->attendance->hasTodaysReport($userId);
    }

    public function getActiveStaffMembers(): array
    {
        return $this->attendance->getActiveStaffMembers();
    }

    public function getServiceOrders(): array
    {
        return $this->attendance->getServiceOrders();
    }

    private function generateAutoDraft(array $activities): string
    {
        if ($activities === []) {
            return '';
        }

        $lines = [];
        $breakMinutes = 0;

        foreach ($activities as $activity) {
            $type = $activity['activity_type'] ?? '';
            $start = $activity['started_at'] ? date('h:i A', strtotime($activity['started_at'])) : '';
            $end = $activity['ended_at'] ? date('h:i A', strtotime($activity['ended_at'])) : '';
            $duration = (int) ($activity['duration_seconds'] ?? 0);
            $durH = (int) ($duration / 3600);
            $durM = ((int) ($duration % 3600)) / 60;
            $durStr = $durH > 0 ? $durH . 'h ' . $durM . 'm' : $durM . ' minutes';
            $remarks = trim((string) ($activity['remarks'] ?? ''));

            if (in_array($type, ['IDLE', 'BREAK'], true)) {
                $breakMinutes += (int) ($duration / 60);
                continue;
            }

            $soNo = trim((string) ($activity['so_no'] ?? ''));
            $soTitle = trim((string) ($activity['so_title'] ?? ''));

            $line = 'Worked on ';
            $line .= $soNo !== '' ? $soNo : 'General work';
            if ($soTitle !== '') {
                $line .= ' - ' . $soTitle;
            }
            $line .= ' from ' . $start . ' to ' . $end . ' (' . $durStr . ').';
            if ($remarks !== '') {
                $line .= ' Remarks: ' . $remarks . '.';
            }

            $lines[] = $line;
        }

        if ($breakMinutes > 0) {
            $lines[] = 'Break / idle time: ' . $breakMinutes . ' minutes.';
        }

        return implode("\n", $lines);
    }

    private function computeSessionDurations(int $sessionId): array
    {
        $statement = \App\Core\Database::connection()->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN activity_type IN ('ACTIVE','TASK_LINKED') THEN duration_seconds ELSE 0 END), 0) AS active_seconds,
                COALESCE(SUM(CASE WHEN activity_type IN ('IDLE','BREAK') THEN duration_seconds ELSE 0 END), 0) AS idle_seconds
             FROM attendance_activity_logs
             WHERE attendance_session_id = :session_id AND ended_at IS NOT NULL"
        );
        $statement->execute(['session_id' => $sessionId]);

        return $statement->fetch(\PDO::FETCH_ASSOC) ?: ['active_seconds' => 0, 'idle_seconds' => 0];
    }
}
