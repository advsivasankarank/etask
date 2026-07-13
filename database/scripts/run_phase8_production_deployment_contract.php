<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$read = static function (string $path) use ($root): string {
    $content = file_get_contents($root . '/' . $path);
    if ($content === false) {
        fwrite(STDERR, 'Unable to read Phase 8 contract source: ' . $path . PHP_EOL);
        exit(1);
    }
    return $content;
};

$htaccess = $read('public/.htaccess');
$securityHeaders = $read('app/Middleware/SecurityHeadersMiddleware.php');

$checks = [
    'public rewrite configuration is environment portable' => !str_contains($htaccess, 'RewriteBase'),
    'public directory resolves index.php as its entry point' => str_contains($htaccess, 'DirectoryIndex index.php'),
    'unknown non-file routes continue through the front controller' => str_contains($htaccess, 'RewriteCond %{REQUEST_FILENAME} !-f') && str_contains($htaccess, 'RewriteCond %{REQUEST_FILENAME} !-d') && str_contains($htaccess, 'RewriteRule ^ index.php [QSA,L]'),
    'dotfiles remain denied by the public web layer' => str_contains($htaccess, 'RewriteRule "(^|/)\." - [F]'),
    'directory indexes remain disabled' => str_contains($htaccess, 'Options -Indexes'),
    'baseline browser security headers remain configured' => str_contains($securityHeaders, 'X-Content-Type-Options: nosniff') && str_contains($securityHeaders, 'X-Frame-Options: SAMEORIGIN') && str_contains($securityHeaders, 'Referrer-Policy: strict-origin-when-cross-origin'),
    'production HSTS remains enabled' => str_contains($securityHeaders, "config('app.env', 'local') === 'production'") && str_contains($securityHeaders, 'Strict-Transport-Security: max-age=31536000; includeSubDomains'),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
