<?php

declare(strict_types=1);

use App\Services\EnvironmentDoctorService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

try {
    $report = (new EnvironmentDoctorService())->report();

    echo "Environment doctor summary:\n";
    echo 'OK: ' . $report['summary']['ok'] . "\n";
    echo 'WARNING: ' . $report['summary']['warning'] . "\n";
    echo 'ERROR: ' . $report['summary']['error'] . "\n\n";

    foreach ($report['checks'] as $check) {
        echo '[' . $check['status'] . '] ' . $check['name'] . ' - ' . $check['message'] . "\n";
    }
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
