<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$layoutPath = $root . '/layouts/main.php';
$clientIndexPath = $root . '/modules/Clients/views/index.php';

$layout = file_get_contents($layoutPath);
$clientIndex = file_get_contents($clientIndexPath);

if ($layout === false || $clientIndex === false) {
    fwrite(STDERR, 'Unable to read Phase 2 responsive source files.' . PHP_EOL);
    exit(1);
}

$checks = [
    'app shell uses a shrink-safe content track' => str_contains($layout, 'grid-template-columns: 76px minmax(0, 1fr)'),
    'main content can shrink inside the app shell' => preg_match('/\.main-area\s*\{[^}]*min-width:\s*0;/s', $layout) === 1,
    'content area is width constrained' => preg_match('/\.content-area\s*\{[^}]*max-width:\s*100%;/s', $layout) === 1,
    'phone grids collapse to one shrink-safe column' => str_contains($layout, '.content-area [style*="minmax("]'),
    'wide tables own their horizontal scrolling' => preg_match('/\.table-wrap\s*\{[^}]*max-width:\s*100%;[^}]*overflow-x:\s*auto;/s', $layout) === 1,
    'mobile header hides only the visible profile name' => str_contains($layout, '.topbar-profile-name { display: none; }'),
    'profile link retains an accessible full name' => str_contains($layout, 'aria-label="Profile:'),
    'client register opts into mobile cards' => str_contains($clientIndex, 'class="mobile-card-table"'),
    'client fields expose mobile labels' => substr_count($clientIndex, 'data-label=') >= 6,
    'client actions have a fixed mobile action row' => str_contains($clientIndex, 'class="mobile-card-actions"'),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
