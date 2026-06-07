<?php

declare(strict_types=1);

namespace App\Testing;

final class RegressionReportRenderer
{
    public function renderHtml(array $summary, array $results, array $metadata): string
    {
        $rows = '';
        foreach ($results as $result) {
            $statusClass = strtolower((string) $result['status']);
            $checks = '';
            foreach ($result['checks'] as $check) {
                $checks .= '<li>' . htmlspecialchars((string) $check, ENT_QUOTES, 'UTF-8') . '</li>';
            }

            $details = '';
            foreach ($result['details'] as $key => $value) {
                $details .= '<div><strong>' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') . ':</strong> '
                    . htmlspecialchars($this->stringify($value), ENT_QUOTES, 'UTF-8') . '</div>';
            }

            $error = $result['error'] !== null
                ? '<div class="error"><strong>Error:</strong> ' . htmlspecialchars((string) $result['error'], ENT_QUOTES, 'UTF-8') . '</div>'
                : '';

            $rows .= '<tr>'
                . '<td>' . htmlspecialchars((string) $result['name'], ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td><span class="badge ' . $statusClass . '">' . htmlspecialchars((string) $result['status'], ENT_QUOTES, 'UTF-8') . '</span></td>'
                . '<td>' . htmlspecialchars((string) $result['duration_ms'] . ' ms', ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td><ul>' . $checks . '</ul>' . $details . $error . '</td>'
                . '</tr>';
        }

        $coverageRows = '';
        foreach ($metadata['coverage'] as $label => $value) {
            $coverageRows .= '<tr><th>' . htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') . '</th><td>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>e-Task Regression Suite Report</title>
    <style>
        body{font-family:Segoe UI,Arial,sans-serif;background:#f4f8fb;color:#15324b;margin:0;padding:32px;}
        .shell{max-width:1200px;margin:0 auto;}
        .hero{background:#ffffff;border-radius:18px;padding:28px 32px;box-shadow:0 18px 50px rgba(15,76,92,.10);margin-bottom:24px;}
        h1,h2{margin:0 0 12px;}
        .meta{display:flex;gap:18px;flex-wrap:wrap;color:#516579;font-size:14px;}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin:24px 0;}
        .card{background:#fff;border-radius:16px;padding:18px 20px;box-shadow:0 10px 30px rgba(15,76,92,.08);}
        .label{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#6a7b8c;margin-bottom:6px;}
        .value{font-size:28px;font-weight:700;color:#0f4c5c;}
        .accent{color:#f47a20;}
        table{width:100%;border-collapse:collapse;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(15,76,92,.08);}
        th,td{padding:16px 18px;border-bottom:1px solid #e5edf4;vertical-align:top;text-align:left;}
        th{background:#edf7f8;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#35556c;}
        tr:last-child td{border-bottom:none;}
        .badge{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.04em;}
        .badge.pass{background:#dff6e5;color:#196b3f;}
        .badge.fail{background:#fde4e1;color:#b42318;}
        .badge.skip{background:#eef2f6;color:#45576b;}
        ul{margin:0 0 10px 18px;padding:0;}
        li{margin-bottom:4px;}
        .error{margin-top:10px;color:#b42318;}
        .grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px;}
        .panel{background:#fff;border-radius:18px;padding:22px 24px;box-shadow:0 10px 30px rgba(15,76,92,.08);}
        @media (max-width:900px){body{padding:18px}.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="shell">
    <div class="hero">
        <h1>e-Task Regression Suite</h1>
        <div class="meta">
            <span>Started: ' . htmlspecialchars((string) $metadata['started_at'], ENT_QUOTES, 'UTF-8') . '</span>
            <span>Finished: ' . htmlspecialchars((string) $metadata['finished_at'], ENT_QUOTES, 'UTF-8') . '</span>
            <span>Execution Mode: Transaction rollback smoke tests</span>
            <span>Report: ' . htmlspecialchars((string) $metadata['report_file'], ENT_QUOTES, 'UTF-8') . '</span>
        </div>
        <div class="cards">
            <div class="card"><div class="label">Total Tests</div><div class="value">' . (int) $summary['total'] . '</div></div>
            <div class="card"><div class="label">Passed</div><div class="value">' . (int) $summary['passed'] . '</div></div>
            <div class="card"><div class="label">Failed</div><div class="value accent">' . (int) $summary['failed'] . '</div></div>
            <div class="card"><div class="label">Skipped</div><div class="value">' . (int) $summary['skipped'] . '</div></div>
            <div class="card"><div class="label">Duration</div><div class="value">' . htmlspecialchars((string) $summary['duration_seconds'] . ' s', ENT_QUOTES, 'UTF-8') . '</div></div>
        </div>
    </div>

    <div class="grid">
        <div class="panel">
            <h2>Execution Results</h2>
            <table>
                <thead>
                    <tr><th>Test</th><th>Status</th><th>Time</th><th>Observations</th></tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
        </div>
        <div class="panel">
            <h2>Coverage Summary</h2>
            <table>
                <tbody>' . $coverageRows . '</tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>';
    }

    public function renderCli(array $summary, array $results, array $metadata): string
    {
        $lines = [];
        $lines[] = 'e-Task Regression Suite';
        $lines[] = 'Started : ' . $metadata['started_at'];
        $lines[] = 'Finished: ' . $metadata['finished_at'];
        $lines[] = str_repeat('-', 72);

        foreach ($results as $result) {
            $lines[] = sprintf(
                '[%s] %s (%d ms)',
                str_pad((string) $result['status'], 4, ' ', STR_PAD_RIGHT),
                $result['name'],
                (int) $result['duration_ms']
            );

            foreach ($result['checks'] as $check) {
                $lines[] = '  - ' . $check;
            }

            foreach ($result['details'] as $key => $value) {
                $lines[] = '    ' . $key . ': ' . $this->stringify($value);
            }

            if ($result['error'] !== null) {
                $lines[] = '    error: ' . $result['error'];
            }
        }

        $lines[] = str_repeat('-', 72);
        $lines[] = sprintf(
            'Summary: %d total | %d passed | %d failed | %d skipped | %ss',
            (int) $summary['total'],
            (int) $summary['passed'],
            (int) $summary['failed'],
            (int) $summary['skipped'],
            $summary['duration_seconds']
        );
        $lines[] = 'HTML report: ' . $metadata['report_file'];

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return $encoded === false ? '[array]' : $encoded;
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}
