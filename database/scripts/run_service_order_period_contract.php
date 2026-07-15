<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controller = file_get_contents($root . '/modules/ServiceOrders/ServiceOrderController.php');
$view = file_get_contents($root . '/modules/ServiceOrders/views/create.php');
$service = file_get_contents($root . '/app/Services/ServiceOrderService.php');
$repository = file_get_contents($root . '/app/Repositories/FinancialYearRepository.php');
$migration = file_get_contents($root . '/database/migrations/step-34-financial-year-selection.sql');

if ($controller === false || $view === false || $service === false || $repository === false || $migration === false) {
    fwrite(STDERR, 'Unable to read service-order period source files.' . PHP_EOL);
    exit(1);
}

$checks = [
    'service-order form receives all active financial years' => str_contains($controller, "'financialYears' => \$financialYears")
        && str_contains($repository, 'function allActive()'),
    'selected financial year is submitted and required' => str_contains($view, 'name="financial_year_id"')
        && str_contains($view, 'id="financial_year_id" required')
        && str_contains($controller, "'financial_year_id' => (int) \$request->input('financial_year_id', 0)"),
    'service-order creation uses the selected active financial year' => str_contains($service, 'findActiveById($financialYearId)'),
    'ITR filing frequency is fixed to annual' => str_contains($view, "workBasisSelect.value = 'ANNUAL'")
        && str_contains($service, "if (\$serviceCode === 'ITR')"),
    'ITR assessment year is derived from financial year' => str_contains($service, 'assessmentYearForFinancialYear($financialYear)'),
    'monthly options follow April through March' => strpos($controller, "4 => 'April'") < strpos($controller, "1 => 'January'"),
    'quarter options describe Indian financial-year quarters' => str_contains($view, "'Q1' => 'Q1 (April - June)'")
        && str_contains($view, "'Q4' => 'Q4 (January - March)'"),
    'monthly and quarterly calendar years are derived server-side' => str_contains($service, "\$periodMonth >= 4")
        && str_contains($service, "\$quarterLabels = ["),
    'obsolete manual period-year input is removed' => !str_contains($view, 'name="period_year"'),
    'financial-year migration includes the current FY' => str_contains($migration, "('2026-27', 'FY 2026-27'")
        && str_contains($migration, 'ON DUPLICATE KEY UPDATE'),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
