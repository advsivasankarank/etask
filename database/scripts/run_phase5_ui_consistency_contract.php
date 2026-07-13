<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/app/Helpers/helpers.php';

$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    if ($content === false) {
        fwrite(STDERR, 'Unable to read Phase 5 contract source: ' . $path . PHP_EOL);
        exit(1);
    }
    return $content;
};

$layout = $read('layouts/main.php');
$helpers = $read('app/Helpers/helpers.php');
$billingController = $read('modules/Billing/BillingController.php');
$consultantController = $read('modules/Consultants/ConsultantController.php');
$changePassword = $read('modules/Auth/views/change-password.php');
$settingsController = $read('modules/Settings/SettingsController.php');
$reportIndex = $read('modules/Reports/views/index.php');
$accountReport = $read('modules/Reports/views/accounts.php');

$internalViewH1 = [];
$genericEmptyStates = [];
$statusLabelUses = 0;
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($views as $view) {
    if (!$view->isFile() || $view->getExtension() !== 'php' || !str_contains(str_replace('\\', '/', $view->getPathname()), '/views/')) {
        continue;
    }

    $path = str_replace('\\', '/', $view->getPathname());
    $content = file_get_contents($path) ?: '';
    if (!str_contains($path, '/modules/Auth/views/') && str_contains($content, '<h1')) {
        $internalViewH1[] = $path;
    }
    if (str_contains($content, '>No results<') || str_contains($content, '>No Data<')) {
        $genericEmptyStates[] = $path;
    }
    $statusLabelUses += substr_count($content, 'label_case(');
}

$checks = [
    'internal app header exposes the page title as h1' => str_contains($layout, '<h1 class="topbar-title">'),
    'internal views do not introduce duplicate h1 headings' => $internalViewH1 === [],
    'status labels include e-Verification normalization' => label_case('E_VERIFICATION_PENDING') === 'e-Verification Pending',
    'status labels normalize multiword enum values' => label_case('CLIENT_CLARIFICATION_PENDING') === 'Client Clarification Pending' && label_case('FOLLOWED_UP') === 'Followed Up',
    'INR formatter always renders two decimal places' => money_inr(0) === 'INR 0.00' && money_inr(1234.5) === 'INR 1,234.50',
    'financial dashboards use the shared INR formatter' => str_contains($reportIndex, 'money_inr(') && substr_count($accountReport, 'money_inr(') === 3,
    'status presentation helper is applied across workflows' => $statusLabelUses >= 35,
    'generic empty-state headings are removed from module views' => $genericEmptyStates === [],
    'billing workspace is named by business purpose' => substr_count($billingController, "'title' => 'Service Order Billing'") === 2,
    'consultant delivery workspace is distinct from the master register' => substr_count($consultantController, "'title' => 'Consultant Delivery Workspace'") === 2,
    'password-change content distinguishes voluntary and forced updates' => substr_count($changePassword, '!empty($forcedChange)') >= 4,
    'read-only settings screens are named as references' => substr_count($settingsController, 'Reference') >= 6,
    'shared label and money helpers remain centrally defined' => str_contains($helpers, "if (!function_exists('label_case'))") && str_contains($helpers, "if (!function_exists('money_inr'))"),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
