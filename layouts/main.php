<?php
use App\Core\Auth;

$appName = config('app.name', 'Compliance Management System');
$activeMenu = $activeMenu ?? 'dashboard';
$currentUser = Auth::user();

$hasUniversalSearch = Auth::canAny('search.view', 'search.quick');
$notificationLink = Auth::isPortalUser() ? '/client-portal/account#portal-notifications' : '/dashboard#workspace-notifications';
$profileLink = Auth::isPortalUser() ? '/client-portal/account' : '/change-password';

$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'path' => '/dashboard', 'permissions' => []],
    ['key' => 'attendance', 'label' => 'Staff Monitor', 'path' => '/attendance', 'permissions' => ['attendance.view', 'attendance.report.review', 'attendance.productivity.view']],
    ['key' => 'clients', 'label' => 'Clients', 'path' => '/clients', 'permissions' => ['clients.view']],
    ['key' => 'service_orders', 'label' => 'Service Orders', 'path' => '/service-orders', 'permissions' => ['service_orders.view']],
    ['key' => 'client_portal', 'label' => 'Client Portal', 'path' => Auth::isPortalUser() ? '/client-portal/account' : '/client-portal/pso', 'permissions' => ['portal.self_access', 'portal.pso.create', 'portal.pso.review', 'portal.pso.approve', 'portal.pso.reject']],
    ['key' => 'billing', 'label' => 'Billing', 'path' => '/billing', 'permissions' => ['billing.view']],
    ['key' => 'consultants', 'label' => 'Consultants', 'path' => '/consultants', 'permissions' => ['consultants.view']],
    ['key' => 'reports', 'label' => 'Reports', 'path' => '/reports', 'permissions' => ['reports.view', 'reports.financial']],
    ['key' => 'support', 'label' => 'Support', 'path' => '/client-portal/support', 'permissions' => ['portal.self_access']],
    ['key' => 'users', 'label' => 'Users', 'path' => '/users', 'permissions' => ['users.manage.portal', 'users.manage.internal']],
];

$utilityItems = [
    ['label' => 'Notifications', 'path' => $notificationLink, 'permissions' => []],
    ['label' => 'Reminders', 'path' => '/reminders', 'permissions' => ['reminders.view', 'reminders.report']],
    ['label' => 'Profile', 'path' => $profileLink, 'permissions' => []],
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
            --surface-soft: #eef9fb;
            --border: rgba(17, 96, 122, 0.12);
            --text: #15313b;
            --muted: #607b86;
            --primary: #1499a8;
            --primary-dark: #0d7987;
            --accent: #ef8b2c;
            --accent-soft: #fff3e5;
            --header-1: #daf5f8;
            --header-2: #fff3e3;
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
            padding: 0 18px 24px;
        }

        .content {
            width: min(1360px, 100%);
            margin: 0 auto;
            padding: 18px 0 24px;
            display: flex;
            flex-direction: column;
        }

        .app-header {
            position: sticky;
            top: 12px;
            z-index: 40;
            width: 100%;
            margin: 0 0 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 18px 22px 18px;
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(247,252,253,0.94) 100%);
            box-shadow: 0 10px 30px rgba(20, 113, 135, 0.10);
            border: 1px solid rgba(20, 113, 135, 0.10);
            backdrop-filter: blur(18px);
        }

        .header-top {
            display: grid;
            grid-template-columns: minmax(220px, 280px) minmax(420px, 1fr) minmax(280px, 340px);
            align-items: center;
            gap: 18px;
        }

        .app-brand {
            display: grid;
            gap: 4px;
        }

        .brand-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 30px;
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
            font-size: 0.98rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #0F4C5C;
            line-height: 1;
        }

        .brand-subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 500;
        }

        .header-command {
            display: grid;
            gap: 8px;
            min-width: 0;
        }

        .command-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .command-label {
            font-size: 0.78rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--primary-dark);
            font-weight: 800;
        }

        .command-helper {
            font-size: 0.84rem;
            color: var(--muted);
            font-weight: 600;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 14px;
            border-top: 1px solid rgba(20, 113, 135, 0.08);
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            width: 100%;
        }

        .menu a {
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.78);
            border: 1px solid rgba(20,113,135,0.10);
            transition: transform .18s ease, background .18s ease, border-color .18s ease;
            font-weight: 600;
            font-size: 0.92rem;
            letter-spacing: 0.01em;
            color: var(--text);
            white-space: nowrap;
        }

        .menu a.active,
        .menu a:hover {
            background: linear-gradient(135deg, rgba(20,153,168,0.16), rgba(239,139,44,0.10));
            border-color: rgba(20,113,135,0.18);
            transform: translateY(-1px);
        }

        .utility-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            min-width: 0;
        }

        .utility-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 14px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(20,113,135,0.10);
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text);
            white-space: nowrap;
        }

        .utility-link:hover {
            background: linear-gradient(135deg, rgba(20,153,168,0.12), rgba(239,139,44,0.08));
            border-color: rgba(20,113,135,0.16);
        }

        .utility-link--profile {
            display: grid;
            gap: 3px;
            min-width: 132px;
        }

        .utility-link-label {
            font-size: 0.68rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 800;
        }

        .utility-link-value {
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--text);
        }

        .logout-form { margin: 0; }

        .logout-button {
            min-height: 44px;
        }

        .universal-search {
            position: relative;
            width: 100%;
        }

        .header-search {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            min-height: 60px;
            border-radius: 18px;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(20, 113, 135, 0.14);
            box-shadow: 0 14px 28px rgba(20, 113, 135, 0.10);
        }

        .header-search input {
            border: 0;
            padding: 0;
            background: transparent;
            box-shadow: none;
            font-size: 1rem;
            font-weight: 600;
        }

        .header-search input:focus {
            box-shadow: none;
        }

        .header-search:focus-within {
            border-color: rgba(20, 113, 135, 0.24);
            box-shadow: 0 18px 34px rgba(20, 113, 135, 0.14);
        }

        .search-icon {
            width: 20px;
            height: 20px;
            color: var(--primary-dark);
            flex: 0 0 auto;
        }

        .search-prefix {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: 0.02em;
            flex: 0 0 auto;
        }

        .search-shortcut {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 54px;
            padding: 7px 10px;
            border-radius: 10px;
            background: #f3fbfc;
            border: 1px solid rgba(20, 113, 135, 0.10);
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .search-palette {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            padding: 14px;
            border-radius: 18px;
            background: rgba(255,255,255,0.98);
            border: 1px solid rgba(20, 113, 135, 0.10);
            box-shadow: 0 24px 60px rgba(20, 113, 135, 0.16);
            display: none;
            z-index: 70;
        }

        .search-palette.open {
            display: grid;
            gap: 14px;
        }

        .palette-section {
            display: grid;
            gap: 10px;
        }

        .palette-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            font-weight: 700;
        }

        .palette-list {
            display: grid;
            gap: 10px;
        }

        .palette-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(20, 113, 135, 0.08);
            background: linear-gradient(180deg, rgba(255,255,255,1), rgba(244,248,245,0.92));
            cursor: pointer;
        }

        .palette-item:hover,
        .palette-item:focus {
            outline: none;
            border-color: rgba(20, 113, 135, 0.18);
            transform: translateY(-1px);
        }

        .palette-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .subtle { color: var(--muted); font-size: 0.95rem; }

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
            padding: 11px 18px;
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

        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .result-card {
            padding: 18px;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, rgba(255,255,255,0.98), #f6fbfb 100%);
            border: 1px solid rgba(20, 113, 135, 0.08);
            display: grid;
            gap: 14px;
        }

        .result-type {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e8f6f8;
            color: var(--primary-dark);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .result-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff8ee;
            color: #8a4b15;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .result-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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

        @media (max-width: 1200px) {
            .header-top {
                grid-template-columns: minmax(180px, 240px) minmax(300px, 1fr) minmax(220px, 280px);
                gap: 14px;
            }
            .menu { gap: 8px; }
            .menu a { padding: 8px 12px; font-size: 0.88rem; }
        }

        @media (max-width: 1024px) {
            .header-top {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .header-command { grid-column: 1 / -1; }
            .utility-bar { grid-column: 1 / -1; }
            .menu { gap: 6px; }
            .menu a { padding: 7px 11px; font-size: 0.85rem; }
        }

        @media (max-width: 900px) {
            .shell { padding: 0 12px 18px; }
            .content { padding-top: 14px; }
            .app-header {
                top: 12px;
                width: 100%;
            }
            .header-top {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
            .search-bar { grid-template-columns: 1fr; }
            .utility-bar { width: 100%; justify-content: flex-start; }
            .header-search { width: 100%; }
            .search-palette { left: 0; right: 0; }
            .header-nav { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .menu { flex-wrap: nowrap; width: auto; }
        }

        @media (max-width: 768px) {
            .content { padding-top: 12px; }
            .app-header { padding: 14px 16px; }
            .menu {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }
            .menu a {
                width: 100%;
                text-align: center;
                white-space: normal;
                font-size: 0.82rem;
                padding: 8px 6px;
            }
            .utility-bar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }
            .utility-link,
            .logout-button {
                width: 100%;
                justify-content: center;
            }
            .header-search { min-height: 50px; padding: 10px 14px; }
            .search-shortcut { display: none; }
            .hero-card { padding: 20px; }
            .panel { padding: 18px; }
            .card-grid { grid-template-columns: 1fr; }
            .grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        }

        @media (max-width: 560px) {
            .content { padding-top: 10px; }
            .menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .menu a { font-size: 0.78rem; padding: 7px 4px; }
            .utility-bar { grid-template-columns: 1fr; }
            .utility-link--profile { min-width: auto; }
            .brand-row { flex-direction: column; gap: 2px; }
            .app-brand-title { font-size: 0.85rem; }
            .logo { font-size: 24px; }
            .hero-card { padding: 16px; border-radius: 18px; }
            .panel { padding: 14px; border-radius: 16px; }
            .data-card { padding: 14px; }
        }

        /* Table responsiveness */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }
        .table-wrap table { min-width: 600px; }

        /* Global table responsiveness for panels */
        .panel table { width: 100%; }
        .panel > div > table,
        .panel > table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Responsive card grids */
        .responsive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        /* Responsive form groups */
        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .form-row > div { flex: 1; min-width: 160px; }

        /* Form responsiveness */
        @media (max-width: 768px) {
            form { gap: 14px; }
            input, select, textarea { padding: 12px 14px; font-size: 0.95rem; }
            .form-row { flex-direction: column; }
            .form-row > div { min-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <main class="content">
            <div class="app-header">
                <div class="header-top">
                    <div class="app-brand">
                        <div class="brand-row">
                            <div class="logo"><span class="e">e-</span><span class="pani">Pani</span></div>
                            <div class="app-brand-title">: Office Management Suite</div>
                        </div>
                        <div class="brand-subtitle">Compliance, billing, workflow, and client operations</div>
                    </div>
                    <div class="header-command">
                        <?php if ($hasUniversalSearch): ?>
                            <div class="command-label-row">
                                <div class="command-label">Go To Work</div>
                                <div class="command-helper">Search first, then open the right workspace instantly.</div>
                            </div>
                            <div class="universal-search" data-universal-search>
                                <form method="get" action="<?= e(url('/search')) ?>" class="header-search" data-search-form>
                                    <span class="search-prefix">
                                        <span class="search-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="7"></circle>
                                                <path d="M20 20l-3.5-3.5"></path>
                                            </svg>
                                        </span>
                                    </span>
                                    <input
                                        type="text"
                                        name="q"
                                        value=""
                                        autocomplete="off"
                                        placeholder="Search clients, service orders, invoices, PAN, GSTIN, mobile, documents..."
                                        data-search-input
                                        data-endpoint="<?= e(url('/search/quick?format=json')) ?>"
                                    >
                                    <span class="search-shortcut">Ctrl + K</span>
                                </form>
                                <div class="search-palette" data-search-palette></div>
                            </div>
                        <?php else: ?>
                            <div class="command-label-row">
                                <div class="command-label">Workspace</div>
                                <div class="command-helper">Navigate your account, services, billing, and support from one place.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="utility-bar">
                        <?php foreach ($utilityItems as $item): ?>
                            <?php
                            $allowed = $item['permissions'] === [] || Auth::canAny(...$item['permissions']);
                            if (!$allowed) {
                                continue;
                            }
                            $isProfile = $item['label'] === 'Profile';
                            ?>
                            <a href="<?= e(url($item['path'])) ?>" class="utility-link<?= $isProfile ? ' utility-link--profile' : '' ?>">
                                <?php if ($isProfile): ?>
                                    <span class="utility-link-label">Welcome</span>
                                    <span class="utility-link-value"><?= e($currentUser['full_name'] ?? 'Workspace User') ?></span>
                                <?php else: ?>
                                    <span><?= e($item['label']) ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                        <form method="post" action="<?= e(url('/logout')) ?>" class="logout-form">
                            <?= \App\Core\Csrf::inputField() ?>
                            <button type="submit" class="button button-secondary logout-button">Logout</button>
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
                </div>
            </div>
            <?= $content ?>
            <footer class="app-footer">e-pani : Office Management Suite from E Tax Advisors Private Limited</footer>
        </main>
    </div>
    <script>
        (function () {
            const wrapper = document.querySelector('[data-universal-search]');
            if (!wrapper) {
                return;
            }

            const form = wrapper.querySelector('[data-search-form]');
            const input = wrapper.querySelector('[data-search-input]');
            const palette = wrapper.querySelector('[data-search-palette]');
            const endpoint = input ? input.getAttribute('data-endpoint') : '';
            let debounceTimer = null;

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderMeta = (items) => {
                if (!Array.isArray(items) || items.length === 0) {
                    return '';
                }

                return '<div class="palette-meta">' + items.map((item) => '<span class="chip">' + escapeHtml(item) + '</span>').join('') + '</div>';
            };

            const resultItem = (item) => {
                return `
                    <a href="${escapeHtml(item.url || '#')}" class="palette-item">
                        <div style="min-width:0;">
                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <span class="result-type">${escapeHtml(item.type || 'RESULT')}</span>
                                ${item.badge ? `<span class="result-badge">${escapeHtml(item.badge)}</span>` : ''}
                            </div>
                            <div style="margin-top:8px;font-weight:700;">${escapeHtml(item.title || 'Result')}</div>
                            ${item.subtitle ? `<div class="subtle" style="margin-top:4px;">${escapeHtml(item.subtitle)}</div>` : ''}
                            ${renderMeta(item.meta)}
                        </div>
                        <span class="chip chip-strong">${escapeHtml(item.action_label || 'Open')}</span>
                    </a>
                `;
            };

            const simpleItem = (label, description, url) => {
                return `
                    <a href="${escapeHtml(url || '#')}" class="palette-item">
                        <div>
                            <div style="font-weight:700;">${escapeHtml(label || 'Open')}</div>
                            ${description ? `<div class="subtle" style="margin-top:4px;">${escapeHtml(description)}</div>` : ''}
                        </div>
                        <span class="chip chip-strong">Open</span>
                    </a>
                `;
            };

            const searchHistoryItem = (entry) => {
                const query = entry.query_text || '';
                const url = '<?= e(url('/search?q=')) ?>' + encodeURIComponent(query);
                return simpleItem(query, entry.searched_at || 'Recent search', url);
            };

            const renderPalette = (payload) => {
                const sections = [];
                const items = Array.isArray(payload.items) ? payload.items : [];
                const recentSearches = Array.isArray(payload.recent_searches) ? payload.recent_searches : [];
                const recentRecords = Array.isArray(payload.recent_records) ? payload.recent_records : [];
                const quickAccess = Array.isArray(payload.quick_access) ? payload.quick_access : [];

                if (items.length > 0) {
                    sections.push(`
                        <section class="palette-section">
                            <div class="palette-section-title">Results</div>
                            <div class="palette-list">${items.slice(0, 8).map(resultItem).join('')}</div>
                        </section>
                    `);
                }

                if (items.length === 0 && payload.query) {
                    sections.push(`
                        <section class="palette-section">
                            <div class="palette-section-title">No Results</div>
                            <article class="data-card" style="padding:14px 16px;">
                                <span class="subtle">No matching workspaces were found. Press Enter to open the detailed search page.</span>
                            </article>
                        </section>
                    `);
                }

                if (recentSearches.length > 0) {
                    sections.push(`
                        <section class="palette-section">
                            <div class="palette-section-title">Recent Searches</div>
                            <div class="palette-list">${recentSearches.map(searchHistoryItem).join('')}</div>
                        </section>
                    `);
                }

                if (recentRecords.length > 0) {
                    sections.push(`
                        <section class="palette-section">
                            <div class="palette-section-title">Recent Records</div>
                            <div class="palette-list">${recentRecords.slice(0, 5).map(resultItem).join('')}</div>
                        </section>
                    `);
                }

                if (quickAccess.length > 0) {
                    sections.push(`
                        <section class="palette-section">
                            <div class="palette-section-title">Quick Access</div>
                            <div class="palette-list">${quickAccess.map((item) => simpleItem(item.label, item.description, item.url)).join('')}</div>
                        </section>
                    `);
                }

                palette.innerHTML = sections.join('');
                palette.classList.add('open');
            };

            const loadPalette = () => {
                if (!endpoint) {
                    return;
                }

                const query = input.value.trim();
                fetch(endpoint + '&q=' + encodeURIComponent(query), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then((response) => response.json())
                    .then((payload) => renderPalette(payload))
                    .catch(() => {
                        palette.innerHTML = '<article class="data-card" style="padding:14px 16px;"><span class="subtle">Search suggestions are temporarily unavailable.</span></article>';
                        palette.classList.add('open');
                    });
            };

            input.addEventListener('focus', loadPalette);
            input.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(loadPalette, 180);
            });

            form.addEventListener('submit', (event) => {
                if (input.value.trim() === '') {
                    event.preventDefault();
                    loadPalette();
                }
            });

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) {
                    palette.classList.remove('open');
                }
            });

            document.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    input.focus();
                    input.select();
                    loadPalette();
                }

                if (event.key === 'Escape') {
                    palette.classList.remove('open');
                }
            });
        })();
    </script>
</body>
</html>
