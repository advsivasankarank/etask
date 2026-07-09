<?php

declare(strict_types=1);

namespace Modules\Attendance;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AttendanceService;
use Throwable;

final class AttendanceController
{
    private AttendanceService $attendance;

    public function __construct()
    {
        $this->attendance = new AttendanceService();
    }

    public function index(): void
    {
        $userId = (int) Auth::id();
        $dashboard = $this->attendance->getTodaysDashboard($userId);
        $hasMissing = $this->attendance->hasMissingReports($userId);

        $content = View::render(base_path('modules/Attendance/views/index.php'), [
            'title' => 'Staff Monitor',
            'activeMenu' => 'attendance',
            'dashboard' => $dashboard,
            'has_missing_reports' => $hasMissing,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function today(): void
    {
        $userId = (int) Auth::id();
        $dashboard = $this->attendance->getTodaysDashboard($userId);

        $content = View::render(base_path('modules/Attendance/views/today.php'), [
            'title' => 'My Work Day',
            'activeMenu' => 'attendance',
            'dashboard' => $dashboard,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function showActivityForm(): void
    {
        $userId = (int) Auth::id();
        $serviceOrders = $this->attendance->getServiceOrders();
        $openActivity = $this->attendance->getOpenSession($userId);

        $content = View::render(base_path('modules/Attendance/views/activity_form.php'), [
            'title' => 'Start Work',
            'activeMenu' => 'attendance',
            'service_orders' => $serviceOrders,
            'open_session' => $openActivity,
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function startActivity(Request $request): void
    {
        $userId = (int) Auth::id();
        $activityType = trim((string) $request->input('activity_type', 'ACTIVE'));
        $serviceOrderId = (int) $request->input('service_order_id', 0) ?: null;
        $remarks = trim((string) $request->input('remarks', ''));

        if (!in_array($activityType, ['ACTIVE', 'TASK_LINKED'], true)) {
            $activityType = 'ACTIVE';
        }

        try {
            $this->attendance->startWorkActivity($userId, $activityType, $serviceOrderId, null, $remarks);
            Session::flash('success', 'Work activity started.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/attendance');
    }

    public function stopActivity(Request $request): void
    {
        $userId = (int) Auth::id();
        $remarks = trim((string) $request->input('remarks', ''));

        try {
            $this->attendance->stopWorkActivity($userId, $remarks);
            Session::flash('success', 'Work activity stopped.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/attendance');
    }

    public function pauseActivity(Request $request): void
    {
        $userId = (int) Auth::id();

        try {
            $this->attendance->pauseActivity($userId);
            Session::flash('success', 'Break started.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/attendance');
    }

    public function resumeActivity(Request $request): void
    {
        $userId = (int) Auth::id();

        try {
            $this->attendance->resumeActivity($userId);
            Session::flash('success', 'Work resumed. Start a new work activity.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/attendance/activity/start');
    }

    public function showReportForm(): void
    {
        $userId = (int) Auth::id();
        $formData = $this->attendance->getTodaysReportForm($userId);

        $content = View::render(base_path('modules/Attendance/views/report_form.php'), [
            'title' => 'Daily Work Report',
            'activeMenu' => 'attendance',
            'report' => $formData['report'],
            'auto_draft' => $formData['auto_draft'],
            'activities' => $formData['activities'],
            'can_edit' => $formData['can_edit'],
            'logout_pending' => Session::get('logout_pending', false),
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
        ]);

        Response::html($content);
    }

    public function submitReport(Request $request): void
    {
        $userId = (int) Auth::id();

        try {
            $this->attendance->submitDailyReport($userId, $request->all());
            Session::flash('success', 'Daily work report submitted successfully.');

            if (Session::pull('logout_pending', false)) {
                redirect('/logout');
            }

            redirect('/attendance');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/attendance/report');
        }
    }

    public function adminReports(): void
    {
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $staffId = (int) ($_GET['staff_id'] ?? 0) ?: null;
        $status = trim((string) ($_GET['status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $reports = $this->attendance->getAdminReports($date, $staffId, $status, $page);
        $staffMembers = $this->attendance->getActiveStaffMembers();

        $content = View::render(base_path('modules/Attendance/views/admin_reports.php'), [
            'title' => 'Staff Daily Reports',
            'activeMenu' => 'attendance',
            'reports' => $reports,
            'staff_members' => $staffMembers,
            'filters' => ['date' => $date, 'staff_id' => $staffId, 'status' => $status],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function showReport(Request $request): void
    {
        $reportId = (int) $request->input('id', 0);
        $currentUserId = (int) Auth::id();

        try {
            $detail = $this->attendance->getReportDetail($reportId);
            $report = $detail['report'];
            $activities = $detail['activities'];

            if ((int) $report['user_id'] !== $currentUserId && !$this->attendance->hasTodaysReport($currentUserId) && !Auth::canAny('attendance.report.review')) {
                Response::abort(403, 'Access denied.');
            }

            $content = View::render(base_path('modules/Attendance/views/show_report.php'), [
                'title' => 'Daily Work Report',
                'activeMenu' => 'attendance',
                'report' => $report,
                'activities' => $activities,
                'can_review' => Auth::canAny('attendance.report.review') && (int) $report['user_id'] !== $currentUserId,
                'error' => Session::pullFlash('error'),
                'success' => Session::pullFlash('success'),
            ]);

            Response::html($content);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/attendance');
        }
    }

    public function reviewReport(Request $request): void
    {
        $reportId = (int) $request->input('report_id', 0);
        $remarks = trim((string) $request->input('admin_remarks', ''));
        $action = trim((string) $request->input('action', 'review'));

        try {
            if ($action === 'reopen') {
                $this->attendance->reopenReport($reportId, $remarks);
                Session::flash('success', 'Report reopened successfully.');
            } else {
                $this->attendance->reviewReport($reportId, (int) Auth::id(), $remarks);
                Session::flash('success', 'Report reviewed successfully.');
            }
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/attendance/report/show?id=' . $reportId);
    }

    public function productivity(): void
    {
        $dateFrom = trim((string) ($_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'))));
        $dateTo = trim((string) ($_GET['date_to'] ?? date('Y-m-d')));
        $staffId = (int) ($_GET['staff_id'] ?? 0) ?: null;

        $data = $this->attendance->getProductivity($dateFrom, $dateTo, $staffId, null);
        $staffMembers = $this->attendance->getActiveStaffMembers();

        $content = View::render(base_path('modules/Attendance/views/productivity.php'), [
            'title' => 'Staff Productivity',
            'activeMenu' => 'attendance',
            'staff_summary' => $data['staff_summary'],
            'so_summary' => $data['so_summary'],
            'staff_members' => $staffMembers,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo, 'staff_id' => $staffId],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function emergencyLogout(Request $request): void
    {
        $userId = (int) Auth::id();
        $sessionId = (int) \App\Core\Session::get('attendance_session_id', 0);

        if ($sessionId > 0) {
            $this->attendance->closeAttendanceSession($sessionId);
        }

        \App\Core\Logger::info('attendance.emergency_logout', ['user_id' => $userId]);

        \App\Core\Session::put('emergency_logout', true);
        Auth::logout();
        \App\Core\Session::flash('success', 'Emergency logout completed. Please submit your daily report on next login.');
        redirect('/');
    }
}
