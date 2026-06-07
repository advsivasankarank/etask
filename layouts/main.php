<?php
use App\Core\Auth;
$appName = config('app.name', 'Compliance Management System');
$activeMenu = $activeMenu ?? 'dashboard';
$currentUser = Auth::user();
if (!function_exists('renderNavIcon')) {
    function renderNavIcon(string $key): string
    {
        $icons = [
            'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h7V4H4v9Zm9 7h7v-9h-7v9Zm0-16v5h7V4h-7ZM4 20h7v-5H4v5Z"/></svg>',
            'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4Zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4Zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.95 1.97 3.45V20h6v-3.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>',
            'clients' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>',
            'service_orders' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1.5V8h4.5"/><path d="M8 12h8M8 16h8M8 20h5"/></svg>',
            'client_portal' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 3 7v5c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm0 8c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08C16.71 18.72 14.5 20 12 20Z"/></svg>',
            'billing' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5v-11Zm2 1.5h14M7 15h4m4 0h2"/></svg>',
            'consultants' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0Zm12.5-10.5 1.5 1.5 3-3"/></svg>',
            'reports' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19V5m0 14h14M9 16V9m5 7V6m5 10v-4"/></svg>',
            'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"/><path d="m20 20-4.35-4.35"/></svg>',
            'reminders' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Z"/><path d="M18 16V11a6 6 0 1 0-12 0v5l-2 2h16l-2-2Z"/></svg>',
        ];

        return $icons[$key] ?? '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/></svg>';
    }
}
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'path' => '/dashboard', 'short' => 'DB', 'permissions' => []],
    ['key' => 'users', 'label' => 'Users', 'path' => '/users', 'short' => 'US', 'permissions' => ['users.manage.portal', 'users.manage.internal']],
    ['key' => 'clients', 'label' => 'Clients', 'path' => '/clients', 'short' => 'CL', 'permissions' => ['clients.view']],
    ['key' => 'service_orders', 'label' => 'Service Orders', 'path' => '/service-orders', 'short' => 'SO', 'permissions' => ['service_orders.view']],
    ['key' => 'client_portal', 'label' => 'Client Portal', 'path' => '/client-portal/pso', 'short' => 'CP', 'permissions' => ['portal.self_access', 'portal.pso.create', 'portal.pso.review', 'portal.pso.approve', 'portal.pso.reject']],
    ['key' => 'billing', 'label' => 'Billing', 'path' => '/billing', 'short' => 'BL', 'permissions' => ['billing.view']],
    ['key' => 'consultants', 'label' => 'Consultants', 'path' => '/consultants', 'short' => 'CN', 'permissions' => ['consultants.view']],
    ['key' => 'reports', 'label' => 'Reports', 'path' => '/reports', 'short' => 'RP', 'permissions' => ['reports.view', 'reports.financial']],
    ['key' => 'search', 'label' => 'Search', 'path' => '/search', 'short' => 'SR', 'permissions' => ['search.view', 'search.quick', 'search.advanced', 'search.history']],
    ['key' => 'reminders', 'label' => 'Reminders', 'path' => '/reminders', 'short' => 'RM', 'permissions' => ['reminders.view', 'reminders.report']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Dashboard') . ' | ' . $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eaf7fb;
            --surface: rgba(255, 255, 255, 0.95);
            --surface-strong: #ffffff;
            --surface-soft: #eef9fb;
            --surface-deep: #d8eff6;
            --border: rgba(17, 96, 122, 0.12);
            --text: #15313b;
            --muted: #607b86;
            --primary: #1499a8;
            --primary-dark: #0d7987;
            --accent: #ef8b2c;
            --accent-soft: #fff3e5;
            --ink: #10233d;
            --header-1: #daf5f8;
            --header-2: #fff3e3;
            --sidebar-1: #0f8d99;
            --sidebar-2: #0e7282;
            --danger: #b42318;
            --success: #047857;
            --shadow: 0 24px 60px rgba(20, 113, 135, 0.14);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI Variable", "Segoe UI", "Aptos", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(20, 153, 168, 0.15), transparent 26%),
                radial-gradient(circle at bottom right, rgba(239, 139, 44, 0.12), transparent 24%),
                linear-gradient(180deg, #edf9fc 0%, #f7fcfd 50%, #ecf7fb 100%);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .shell {
            min-height: 100vh;
            padding: 14px;
            display: grid;
            grid-template-columns: var(--rail-width, 88px) minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }
        .shell[data-collapsed="false"] {
            --rail-width: 248px;
        }
        .shell[data-collapsed="true"] {
            --rail-width: 88px;
        }
        .sidebar {
            position: sticky;
            top: 14px;
            min-height: calc(100vh - 28px);
            padding: 14px 12px;
            border-radius: 28px;
            background:
                radial-gradient(circle at left top, rgba(239, 139, 44, 0.18), transparent 28%),
                linear-gradient(180deg, #0f8d99 0%, #1499a8 58%, #0d7987 100%);
            box-shadow: 0 24px 56px rgba(20, 113, 135, 0.24);
            color: #f3fcff;
            overflow: hidden;
        }
        .sidebar-inner {
            display: flex;
            flex-direction: column;
            height: 100%;
            gap: 12px;
        }
        .sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 14px;
            border: 0;
            border-radius: 16px;
            background: rgba(255,255,255,0.18);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .sidebar-toggle svg,
        .rail-link svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex: 0 0 auto;
        }
        .sidebar-label,
        .rail-text {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .16s ease, width .16s ease, margin .16s ease;
        }
        .rail-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 8px;
        }
        .rail-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 46px;
            padding: 12px 14px;
            border-radius: 18px;
            color: rgba(255,255,255,0.94);
            transition: background .18s ease, transform .18s ease;
        }
        .rail-link:hover,
        .rail-link.active {
            background: rgba(255,255,255,0.16);
            transform: translateX(2px);
        }
        .rail-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.14);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
        }
        .rail-text {
            font-weight: 600;
            font-size: 0.96rem;
            letter-spacing: 0.01em;
        }
        .sidebar-footer {
            margin-top: auto;
            padding-top: 10px;
        }
        .sidebar-mini {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
            border-radius: 16px;
            background: rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.86);
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .shell[data-collapsed="true"] .sidebar-label,
        .shell[data-collapsed="true"] .rail-text {
            width: 0;
            opacity: 0;
            margin: 0;
        }
        .shell[data-collapsed="true"] .sidebar-toggle,
        .shell[data-collapsed="true"] .rail-link,
        .shell[data-collapsed="true"] .sidebar-mini {
            justify-content: center;
        }
        .brand {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 6px;
        }
        .subtle { color: var(--muted); font-size: 0.95rem; }
        .content {
            padding: 0 8px 24px;
            display: flex;
            flex-direction: column;
        }
        .app-header {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            padding: 18px 24px;
            margin-bottom: 18px;
            border-radius: var(--radius-xl);
            background:
                radial-gradient(circle at right top, rgba(239, 139, 44, 0.20), transparent 26%),
                linear-gradient(135deg, var(--header-1) 0%, #ffffff 54%, var(--header-2) 100%);
            color: var(--text);
            box-shadow: 0 24px 58px rgba(20, 113, 135, 0.12);
            border: 1px solid rgba(20, 113, 135, 0.08);
        }
        .app-brand {
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }
        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 600;
            letter-spacing: 0.5px;
            line-height: 1;
        }
        .logo .e {
            color: #FF7A00;
            font-weight: 700;
        }
        .logo .pani {
            color: #0F4C5C;
        }
        .app-brand-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #0F4C5C;
            line-height: 1;
        }
        .header-tools {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .welcome-block {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-left: auto;
        }
        .welcome-text {
            display: grid;
            gap: 2px;
            text-align: right;
        }
        .welcome-label {
            font-size: 0.74rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
        }
        .welcome-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }
        .header-search {
            min-width: 280px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.84);
            border: 1px solid rgba(20, 113, 135, 0.10);
        }
        .header-search input {
            border: 0;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }
        .header-search input:focus {
            box-shadow: none;
        }
        .tool-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.84);
            border: 1px solid rgba(20, 113, 135, 0.10);
            color: var(--text);
            font-weight: 700;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            padding-top: 10px;
            border-top: 1px solid rgba(20, 113, 135, 0.08);
        }
        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .menu a {
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,0.86);
            border: 1px solid rgba(20,113,135,0.10);
            transition: transform .18s ease, background .18s ease, border-color .18s ease;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.01em;
            color: var(--text);
        }
        .menu a.active, .menu a:hover {
            background: linear-gradient(135deg, rgba(20,153,168,0.14), rgba(239,139,44,0.12));
            border-color: rgba(20,113,135,0.16);
            transform: translateY(-1px);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 24px 26px;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(237,249,251,0.92));
            border: 1px solid rgba(20, 113, 135, 0.08);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
            backdrop-filter: blur(12px);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }
        .metric {
            background: linear-gradient(180deg, #ffffff 0%, var(--surface-soft) 100%);
            border: 1px solid rgba(23, 98, 77, 0.08);
            border-radius: var(--radius-md);
            padding: 20px;
            min-height: 132px;
            display: grid;
            align-content: space-between;
        }
        .flash {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 18px;
        }
        .flash-success {
            background: #ecfdf3;
            color: var(--success);
            border: 1px solid #abefc6;
        }
        .button {
            border: 0;
            border-radius: 14px;
            padding: 12px 18px;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.01em;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 14px 24px rgba(20, 113, 135, 0.18);
        }
        .button:hover { transform: translateY(-1px); }
        .button-secondary {
            background: #f3fbfc;
            color: var(--text);
            box-shadow: none;
            border: 1px solid rgba(20, 113, 135, 0.10);
        }
        .logout-form { margin: 0; }
        h2, h3, h4 {
            font-family: "Aptos Display", "Segoe UI Variable Display", "Trebuchet MS", sans-serif;
            letter-spacing: -0.02em;
        }
        input, select, textarea {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid rgba(16, 35, 28, 0.10);
            border-radius: 14px;
            background: rgba(255,255,255,0.95);
            color: var(--text);
            font: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: rgba(23, 98, 77, 0.40);
            box-shadow: 0 0 0 4px rgba(23, 98, 77, 0.10);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid rgba(16, 35, 28, 0.08);
            vertical-align: top;
        }
        thead tr {
            text-align: left;
            background: #f7faf8;
        }
        .hero-card {
            padding: 28px;
            border-radius: var(--radius-xl);
            background:
                radial-gradient(circle at top right, rgba(239, 139, 44, 0.18), transparent 26%),
                linear-gradient(135deg, #0f8d99 0%, #1499a8 58%, #0d7987 100%);
            color: #f4fbf7;
            box-shadow: 0 28px 64px rgba(20, 113, 135, 0.24);
        }
        .hero-card .subtle {
            color: rgba(244, 251, 247, 0.72);
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .search-bar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            margin-bottom: 20px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }
        .data-card {
            padding: 20px;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, rgba(255,255,255,0.98), #f4f8f5 100%);
            border: 1px solid rgba(20, 113, 135, 0.08);
            box-shadow: 0 12px 30px rgba(20, 113, 135, 0.06);
            display: grid;
            gap: 10px;
        }
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 0.73rem;
            font-weight: 700;
            color: var(--accent);
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef4f0;
            border: 1px solid rgba(16, 35, 28, 0.08);
            font-size: 0.84rem;
            color: var(--muted);
        }
        .chip-strong {
            background: var(--accent-soft);
            color: #7e3f1e;
            border-color: rgba(201, 109, 66, 0.16);
        }
        .stat-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 0.92rem;
        }
        .section-title {
            margin: 0 0 14px;
            font-size: 1.05rem;
        }
        .app-footer {
            margin-top: 22px;
            padding: 16px 22px;
            border-radius: 18px;
            background: rgba(255,255,255,0.78);
            border: 1px solid rgba(20, 113, 135, 0.08);
            color: var(--muted);
            font-size: 0.92rem;
            text-align: center;
        }
        @media (max-width: 900px) {
            .shell { padding: 12px; }
            .sidebar {
                position: static;
                min-height: auto;
            }
            .content { padding: 18px; }
            .topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
            .search-bar { grid-template-columns: 1fr; }
            .app-header { align-items: stretch; }
            .header-tools { width: 100%; justify-content: flex-start; }
            .header-search { min-width: 0; width: 100%; }
            .header-nav { flex-direction: column; align-items: stretch; }
            .welcome-block { width: 100%; justify-content: space-between; }
            .welcome-text { text-align: left; }
        }
        @media (max-width: 760px) {
            .shell {
                grid-template-columns: 1fr;
            }
            .sidebar {
                min-height: auto;
            }
            .rail-nav {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
            .shell[data-collapsed="true"] .sidebar-label,
            .shell[data-collapsed="true"] .rail-text {
                width: auto;
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="shell" data-collapsed="true" id="app-shell">
        <aside class="sidebar">
            <div class="sidebar-inner">
                <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-expanded="false" aria-controls="side-navigation">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <span class="sidebar-label">Menu</span>
                </button>
                <nav class="rail-nav" id="side-navigation">
                    <?php foreach ($navItems as $item): ?>
                        <?php
                        $allowed = $item['permissions'] === [] || Auth::canAny(...$item['permissions']);
                        if (!$allowed) {
                            continue;
                        }
                        ?>
                        <a href="<?= e(url($item['path'])) ?>" class="rail-link <?= $activeMenu === $item['key'] ? 'active' : '' ?>">
                            <span class="rail-icon"><?= renderNavIcon($item['key']) ?></span>
                            <span class="rail-text"><?= e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="sidebar-footer">
                    <div class="sidebar-mini">e-Pani</div>
                </div>
            </div>
        </aside>
        <main class="content">
            <div class="app-header">
                <div class="header-top">
                    <div class="app-brand">
                        <div class="logo"><span class="e">e-</span><span class="pani">Pani</span></div>
                        <div class="app-brand-title">: Office Management Suite</div>
                    </div>
                    <div class="welcome-block">
                        <div class="welcome-text">
                            <div class="welcome-label">Welcome</div>
                            <div class="welcome-name"><?= e($currentUser['full_name'] ?? 'Workspace User') ?></div>
                        </div>
                        <form method="post" action="<?= e(url('/logout')) ?>" class="logout-form">
                            <?= \App\Core\Csrf::inputField() ?>
                            <button type="submit" class="button button-secondary">Logout</button>
                        </form>
                    </div>
                </div>
                <div class="header-nav">
                    <nav class="menu">
                        <?php foreach ($navItems as $item): ?>
                            <?php
                            $allowed = $item['permissions'] === [] || Auth::canAny(...$item['permissions']);
                            if (!$allowed) {
                                continue;
                            }
                            ?>
                            <a href="<?= e(url($item['path'])) ?>" class="<?= $activeMenu === $item['key'] ? 'active' : '' ?>"><?= e($item['label']) ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="header-tools">
                        <?php if (Auth::canAny('search.view', 'search.quick')): ?>
                            <form method="get" action="<?= e(url('/search/quick')) ?>" class="header-search">
                                <span style="font-weight:800;color:var(--primary-dark);">GO</span>
                                <input type="text" name="q" value="" placeholder="Client name, PAN, SO no, invoice, receipt or document">
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?= $content ?>
            <footer class="app-footer">e-pani : Office Management Suite from E Tax Advisors Private Limited</footer>
        </main>
    </div>
    <script>
        (function () {
            const shell = document.getElementById('app-shell');
            const toggle = document.getElementById('sidebar-toggle');
            if (!shell || !toggle) {
                return;
            }

            const storageKey = 'epani.sidebar.collapsed';
            const applyState = function (collapsed) {
                shell.setAttribute('data-collapsed', collapsed ? 'true' : 'false');
                toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            };

            const stored = window.localStorage ? localStorage.getItem(storageKey) : null;
            applyState(stored === null ? true : stored === 'true');

            toggle.addEventListener('click', function () {
                const collapsed = shell.getAttribute('data-collapsed') !== 'true';
                applyState(collapsed);
                if (window.localStorage) {
                    localStorage.setItem(storageKey, collapsed ? 'true' : 'false');
                }
            });
        }());
    </script>
</body>
</html>
