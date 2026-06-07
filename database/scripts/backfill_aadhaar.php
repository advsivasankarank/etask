<?php

declare(strict_types=1);

use App\Services\AadhaarBackfillService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$limit = 100;
$execute = false;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--execute') {
        $execute = true;
        continue;
    }

    if (str_starts_with($argument, '--limit=')) {
        $limit = max(1, (int) substr($argument, 8));
    }
}

try {
    $service = new AadhaarBackfillService();

    if ($execute) {
        $result = $service->execute($limit);

        echo "Aadhaar backfill completed.\n";
        echo 'Processed: ' . $result['processed'] . "\n";
        echo 'Remaining legacy rows: ' . $result['remaining'] . "\n";
        exit(0);
    }

    $preview = $service->preview(min($limit, 20));

    echo "Aadhaar backfill preview mode.\n";
    echo 'Legacy plaintext rows: ' . $preview['legacy_count'] . "\n";
    echo "Sample rows:\n";

    foreach ($preview['sample'] as $row) {
        echo '- ID ' . $row['id']
            . ' | ' . $row['client_code']
            . ' | ' . $row['legal_name']
            . ' | ' . $row['aadhaar_masked']
            . "\n";
    }

    echo "\nRun with --execute to encrypt the next batch without deleting the old plaintext values.\n";
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
