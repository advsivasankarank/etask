<?php

declare(strict_types=1);

use App\Core\Database;
use App\Repositories\ReportRepository;
use App\Services\AttendanceService;
use App\Services\SearchService;
use App\Services\UserService;
require dirname(__DIR__, 2) . '/bootstrap/app.php';

$reports = new ReportRepository();
$attendance = new AttendanceService();
$search = new SearchService();
$users = new UserService();

$internalUserId = (int) Database::connection()->query(
    "SELECT id FROM users WHERE client_contact_id IS NULL ORDER BY id ASC LIMIT 1"
)->fetchColumn();

$checks = [
    'reports.overview' => static fn (): array => $reports->overviewCards(),
    'reports.filters' => static fn (): array => $reports->filterOptions(),
    'reports.clients' => static fn (): array => $reports->clientRegister([], 1, 5),
    'reports.service_orders' => static fn (): array => $reports->serviceOrderRegister([], 1, 5),
    'reports.pso' => static fn (): array => $reports->psoRegister([], 1, 5),
    'reports.invoices' => static fn (): array => $reports->invoiceRegister([], 1, 5),
    'reports.receipts' => static fn (): array => $reports->receiptRegister([], 1, 5),
    'reports.outstanding' => static fn (): array => $reports->outstandingReport([], 1, 5),
    'reports.gst' => static fn (): array => $reports->gstSummary([]),
    'reports.revenue' => static fn (): array => $reports->revenueReport([]),
    'reports.consultants' => static fn (): array => $reports->consultantReport([], 1, 5),
    'search.options' => static fn (): array => $search->options(),
    'search.recent' => static fn (): array => $search->recentSearches(5),
    'attendance.admin' => static fn (): array => $attendance->getAdminReports(date('Y-m-d'), null, '', 1),
    'attendance.productivity' => static fn (): array => $attendance->getProductivity(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'), null, null),
    'users.rights' => static fn (): array => $users->rightsCatalogForUser($internalUserId),
];

$failed = 0;
foreach ($checks as $label => $check) {
    try {
        $check();
        echo "PASS {$label}" . PHP_EOL;
    } catch (Throwable $throwable) {
        $failed++;
        echo "FAIL {$label}: " . $throwable->getMessage() . PHP_EOL;
    }
}

exit($failed === 0 ? 0 : 1);
