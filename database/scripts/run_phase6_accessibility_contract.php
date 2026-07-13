<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    if ($content === false) {
        fwrite(STDERR, 'Unable to read Phase 6 contract source: ' . $path . PHP_EOL);
        exit(1);
    }
    return $content;
};

$layout = $read('layouts/main.php');
$dashboard = $read('modules/Dashboard/views/index.php');
$portalAccount = $read('modules/ClientPortal/views/account.php');
$portalPso = $read('modules/ClientPortal/views/show.php');
$missingImageAlternatives = [];
$unlabelledCanvases = [];

$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/modules'));
foreach ($views as $view) {
    if (!$view->isFile() || $view->getExtension() !== 'php' || !str_contains(str_replace('\\', '/', $view->getPathname()), '/views/')) {
        continue;
    }

    $path = str_replace('\\', '/', $view->getPathname());
    $content = file_get_contents($path) ?: '';
    if (preg_match_all('/^.*<img\b.*$/mi', $content, $images)) {
        foreach ($images[0] as $image) {
            if (!preg_match('/\balt\s*=/i', $image)) {
                $missingImageAlternatives[] = $path;
            }
        }
    }
    if (preg_match_all('/<canvas\b[^>]*>/i', $content, $canvases)) {
        foreach ($canvases[0] as $canvas) {
            if (!preg_match('/\b(role\s*=\s*["\']img["\']|aria-label\s*=|aria-labelledby\s*=)/i', $canvas)) {
                $unlabelledCanvases[] = $path;
            }
        }
    }
}

$checks = [
    'internal and portal shells provide skip links' => substr_count($layout, '>Skip to main content</a>') === 2,
    'internal and portal content use named main landmarks' => str_contains($layout, '<main class="portal-content" id="portalMainContent"') && str_contains($layout, '<main class="content-area" id="mainContent"'),
    'skip links transfer keyboard focus into main content' => substr_count($layout, 'mainContent.focus()') === 2 && substr_count($layout, 'mainContent.scrollIntoView()') === 2,
    'primary navigation regions have accessible names' => str_contains($layout, 'aria-label="Primary navigation"') && str_contains($layout, 'aria-label="Client portal"'),
    'mobile navigation exposes expanded state and controlled region' => substr_count($layout, 'aria-controls=') >= 2 && substr_count($layout, "setAttribute('aria-expanded'") >= 4,
    'mobile navigation supports Escape and restores focus' => substr_count($layout, "event.key === 'Escape'") === 2 && substr_count($layout, 'toggle.focus()') >= 2,
    'global search shortcut is implemented and named' => str_contains($layout, 'keyboard shortcut Control K') && str_contains($layout, 'event.ctrlKey || event.metaKey'),
    'active navigation exposes aria-current' => substr_count($layout, "setAttribute('aria-current', 'page')") === 2,
    'collapsed navigation links retain accessible names' => substr_count($layout, "link.setAttribute('aria-label', label.textContent.trim())") === 2,
    'focus-visible treatment is present in both shells' => substr_count($layout, ':focus-visible') >= 2,
    'reduced-motion support is present in both shells' => substr_count($layout, '@media (prefers-reduced-motion: reduce)') === 2,
    'forced-colors support is present for internal controls' => str_contains($layout, '@media (forced-colors: active)'),
    'status and error messages expose live-region roles' => substr_count($layout, "setAttribute('role', 'status')") >= 2 && substr_count($layout, "setAttribute('role', 'alert')") >= 2,
    'tables receive accessible names and column-header scope' => str_contains($layout, "table.setAttribute('aria-label'") && substr_count($layout, "setAttribute('scope', 'col')") === 2,
    'unlabelled form controls receive an accessible fallback name' => str_contains($layout, "control.setAttribute('aria-label', label)"),
    'portal payment and review controls have explicit accessible names' => substr_count($portalAccount, 'aria-label=') >= 4 && substr_count($portalPso, 'aria-label=') >= 3,
    'decorative empty-state imagery is hidden from assistive technology' => substr_count($layout, ".empty-state-icon')") === 2,
    'all module images include alt attributes' => $missingImageAlternatives === [],
    'all module charts have accessible names' => $unlabelledCanvases === [],
    'dashboard section headings follow the page h1 with h2 sections' => !str_contains($dashboard, '<h4') && substr_count($dashboard, '<h2') >= 8,
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
