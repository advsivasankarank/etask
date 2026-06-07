<?php

declare(strict_types=1);

use App\Services\MigrationService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

try {
    $service = new MigrationService();
    $rows = $service->status();

    if ($rows === []) {
        echo "No migration files found.\n";
        exit(0);
    }

    echo "Migration status:\n";
    foreach ($rows as $row) {
        $line = '- ' . $row['file_name'] . ' | ' . $row['status'];
        if ($row['applied_at'] !== null) {
            $line .= ' | applied at ' . $row['applied_at'];
        }
        if ($row['checksum_matches'] === false) {
            $line .= ' | checksum mismatch';
        }
        echo $line . "\n";
    }
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
