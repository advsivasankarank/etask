<?php

declare(strict_types=1);

use App\Testing\RegressionReportRenderer;
use App\Testing\RegressionSuite;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$suite = new RegressionSuite();
$result = $suite->run();

$summary = $result['summary'];
$results = $result['results'];
$metadata = $result['metadata'];

$reportDirectory = base_path('storage/reports/regression');
if (!is_dir($reportDirectory) && !mkdir($reportDirectory, 0775, true) && !is_dir($reportDirectory)) {
    fwrite(STDERR, 'Unable to create regression report directory.' . PHP_EOL);
    exit(1);
}

$timestamp = date('Ymd_His');
$reportFile = $reportDirectory . DIRECTORY_SEPARATOR . 'regression_report_' . $timestamp . '.html';
$metadata['report_file'] = $reportFile;

$renderer = new RegressionReportRenderer();
$html = $renderer->renderHtml($summary, $results, $metadata);
file_put_contents($reportFile, $html);

echo $renderer->renderCli($summary, $results, $metadata);

exit(((int) $summary['failed']) > 0 ? 1 : 0);
