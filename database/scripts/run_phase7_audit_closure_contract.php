<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    if ($content === false) {
        fwrite(STDERR, 'Unable to read Phase 7 contract source: ' . $path . PHP_EOL);
        exit(1);
    }
    return $content;
};

$handler = $read('app/Core/ExceptionHandler.php');
$errorView = $read('app/Views/errors/500.php');
$dashboard = $read('modules/Dashboard/views/index.php');

$checks = [
    'exceptions receive a user-safe incident reference' => str_contains($handler, "'ERR-' . date('Ymd-His')") && str_contains($handler, "substr(hash("),
    'incident reference is written to the application log' => str_contains($handler, "'incident_reference' => \$incidentReference"),
    'retry is limited to safe idempotent requests' => str_contains($handler, "in_array(\$requestMethod, ['GET', 'HEAD'], true)"),
    'retry destination excludes query-string data' => str_contains($handler, "REQUEST_URI") && str_contains($handler, 'PHP_URL_PATH'),
    'error view exposes support reference and timestamp' => str_contains($errorView, 'Support reference:') && str_contains($errorView, '<strong>Time:</strong>'),
    'error view offers retry, previous-page, and dashboard recovery' => str_contains($errorView, '>Try Again</a>') && str_contains($errorView, '>Previous Page</button>') && str_contains($errorView, '>Dashboard</a>'),
    'error view provides support guidance without technical details' => str_contains($errorView, 'contact your system administrator') && !str_contains($errorView, 'getTraceAsString'),
    'error view announces the failure and has visible focus treatment' => str_contains($errorView, 'role="alert"') && str_contains($errorView, '.action:focus-visible'),
    'dashboard separates active secondary metrics from clear checks' => str_contains($dashboard, '$activeRestMetrics = array_filter(') && str_contains($dashboard, '$clearMetricCount = count('),
    'dashboard collapses zero metrics into one no-exceptions card' => str_contains($dashboard, '>No Exceptions</div>') && str_contains($dashboard, '>checks are clear</div>'),
    'dashboard replaces an empty trend chart with guidance' => str_contains($dashboard, '$creationTrendTotal === 0') && str_contains($dashboard, 'The 14-day trend will appear after the first service order is created.'),
    'dashboard preserves a named chart when trend data exists' => str_contains($dashboard, 'aria-label="Service orders created each day during the last 14 days"'),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
