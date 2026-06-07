<?php

declare(strict_types=1);

use App\Services\MigrationService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

try {
    $service = new MigrationService();
    $results = $service->migrate();

    if ($results === []) {
        echo "No pending migrations.\n";
        exit(0);
    }

    echo "Applied migrations:\n";
    foreach ($results as $result) {
        echo '- ' . $result['file_name'] . ' (' . $result['execution_ms'] . " ms)\n";
    }
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
