<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$binary = PHP_OS_FAMILY === 'Windows'
    ? (string) env('MYSQLDUMP_BINARY', 'C:\\xampp\\mysql\\bin\\mysqldump.exe')
    : (string) env('MYSQLDUMP_BINARY', 'mysqldump');

$backupDirectory = base_path('storage/backups');
if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) {
    fwrite(STDERR, "ERROR: Unable to create backup directory.\n");
    exit(1);
}

$timestamp = date('Ymd_His');
$database = (string) config('database.database', '');
$host = (string) config('database.host', '127.0.0.1');
$port = (int) config('database.port', 3306);
$username = (string) config('database.username', '');
$password = (string) config('database.password', '');
$outputPath = $backupDirectory . DIRECTORY_SEPARATOR . $database . '_backup_' . $timestamp . '.sql';

$command = escapeshellarg($binary)
    . ' --host=' . escapeshellarg($host)
    . ' --port=' . escapeshellarg((string) $port)
    . ' --user=' . escapeshellarg($username);

if ($password !== '') {
    $command .= ' --password=' . escapeshellarg($password);
}

$command .= ' --single-transaction --routines --triggers ' . escapeshellarg($database)
    . ' > ' . escapeshellarg($outputPath) . ' 2>&1';

try {
    exec($command, $outputLines, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, 'ERROR: Backup failed: ' . implode(PHP_EOL, $outputLines) . PHP_EOL);
        exit(1);
    }

    echo "Database backup created:\n";
    echo $outputPath . "\n";
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
