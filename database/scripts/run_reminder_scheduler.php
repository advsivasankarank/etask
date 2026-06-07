<?php

declare(strict_types=1);

use App\Services\ReminderSchedulerService;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

try {
    $result = (new ReminderSchedulerService())->run();
    echo "Reminder scheduler completed.\n";
    echo 'Created: ' . $result['created'] . "\n";
    echo 'Triggered: ' . $result['triggered'] . "\n";
    echo 'Escalated: ' . $result['escalated'] . "\n";
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
