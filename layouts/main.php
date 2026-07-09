<?php
use App\Core\Auth;

$appName = config('app.name', 'e-Pani');
$activeMenu = $activeMenu ?? 'dashboard';
$currentUser = Auth::user();
$isPortalUser = Auth::isPortalUser();
$hasUniversalSearch = Auth::canAny('search.view', 'search.quick');

$notificationLink = $isPortalUser ? '/client-portal/account#portal-notifications' : '/dashboard#workspace-notifications';
$profileLink = $isPortalUser ? '/client-portal/account' : '/change-password';

// Active module detection
$activeModule = 'dashboard';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (str_starts_with($requestUri, '/clients')) { $activeModule = 'clients'; }
elseif (str_starts_with($requestUri, '/service-orders') || str_starts_with($requestUri, '/workflow')) { $activeModule = 'service_orders'; }
elseif (str_starts_with($requestUri, '/documents') || $requestUri === '/reports/document-access') { $activeModule = 'documents'; }
elseif (str_starts_with($requestUri, '/dsc')) { $activeModule = 'dsc'; }
elseif (str_starts_with($requestUri, '/attendance') || str_starts_with($requestUri, '/consultants') || ($requestUri === '/users' && Auth::canAny('users.manage.internal', 'users.manage.portal'))) { $activeModule = 'workforce'; }
elseif (str_starts_with($requestUri, '/billing') || str_starts_with($requestUri, '/accounts')) { $activeModule = 'accounts'; }
elseif (str_starts_with($requestUri, '/reports')) { $activeModule = 'reports'; }
elseif (str_starts_with($requestUri, '/reminders/templates') || str_starts_with($requestUri, '/reminders/escalations') || str_starts_with($requestUri, '/users/rights') || $activeMenu === 'settings') { $activeModule = 'settings'; }
elseif (str_starts_with($requestUri, '/client-portal')) { $activeModule = 'client_portal'; }

// Portal user - use portal navigation
if ($isPortalUser):
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
            --border: rgba(17, 96, 122, 0.12);
            --text: #15313b;
            --muted: #607b86;
            --primary: #1499a8;
            --primary-dark: #0d7987;
            --accent: #ef8b2c;
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
            background: radial-gradient(circle at top left, rgba(20, 153, 168, 0.15), transparent 26%),
                        radial-gradient(circle at bottom right, rgba(239, 139, 44, 0.12), transparent 24%),
                        linear-gradient(180deg, #edf9fc 0%, #f7fcfd 50%, #ecf7fb 100%);
            color: var(--text);
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }

        .portal-shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .portal-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background: linear-gradient(180deg, #0f4c5c 0%, #0d3d4a 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .portal-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .portal-brand-logo {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
        }

        .portal-brand-logo .e { color: #ef8b2c; }

        .portal-brand-sub {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            margin-top: 4px;
        }

        .portal-nav {
            flex: 1;
            padding: 16px 0;
        }

        .portal-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }

        .portal-nav a:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
        }

        .portal-nav a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left-color: #ef8b2c;
        }

        .portal-nav-icon {
            width: 20px;
            height: 20px;
            opacity: 0.7;
        }

        .portal-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .portal-user {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 12px;
        }

        .portal-user strong {
            color: #fff;
            display: block;
            margin-bottom: 2px;
        }

        .portal-logout {
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s;
        }

        .portal-logout:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .portal-content {
            padding: 24px;
            overflow-y: auto;
        }

        .portal-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 20px 24px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(237,249,251,0.92));
            border: 1px solid rgba(20, 113, 135, 0.08);
            box-shadow: var(--shadow);
        }

        .portal-topbar h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
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
            .portal-shell { grid-template-columns: 1fr; }
            .portal-sidebar {
                position: fixed;
                left: -280px;
                width: 280px;
                z-index: 100;
                transition: left 0.3s;
            }
            .portal-sidebar.open { left: 0; }
            .portal-content { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <aside class="portal-sidebar">
            <div class="portal-brand">
                <div class="portal-brand-logo"><span class="e">e-</span>Pani</div>
                <div class="portal-brand-sub">Client Portal</div>
            </div>
            <nav class="portal-nav">
                <a href="<?= e(url('/client-portal/account')) ?>" class="<?= $activeMenu === 'client_portal' ? 'active' : '' ?>">
                    <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    My Account
                </a>
                <a href="<?= e(url('/client-portal/pso')) ?>" class="<?= $activeMenu === 'client_portal' && $requestUri === '/client-portal/pso' ? 'active' : '' ?>">
                    <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                    My Services
                </a>
                <a href="<?= e(url('/client-portal/documents')) ?>" class="<?= $requestUri === '/client-portal/documents' ? 'active' : '' ?>">
                    <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    My Documents
                </a>
                <a href="<?= e(url('/client-portal/support')) ?>" class="<?= $activeMenu === 'support' ? 'active' : '' ?>">
                    <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Support
                </a>
            </nav>
            <div class="portal-footer">
                <div class="portal-user">
                    <strong><?= e($currentUser['full_name'] ?? 'Client') ?></strong>
                    <?= e($currentUser['email'] ?? '') ?>
                </div>
                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= \App\Core\Csrf::inputField() ?>
                    <button type="submit" class="portal-logout">Logout</button>
                </form>
            </div>
        </aside>
        <main class="portal-content">
            <div class="portal-topbar">
                <h1><?= e($title ?? 'Dashboard') ?></h1>
            </div>
            <?php if (!empty($success)): ?>
                <div class="flash flash-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?= $content ?>
            <footer class="app-footer">e-Pani : Office Management Suite from E Tax Advisors Private Limited</footer>
        </main>
    </div>
</body>
</html>
<?php else: ?>
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
            --border: rgba(17, 96, 122, 0.12);
            --text: #15313b;
            --muted: #607b86;
            --primary: #1499a8;
            --primary-dark: #0d7987;
            --accent: #ef8b2c;
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
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }

        .app-shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background: linear-gradient(180deg, #0f4c5c 0%, #0a3a47 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 50;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-brand-logo {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-brand-logo .e { color: #ef8b2c; }

        .sidebar-brand-sub {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.55);
            margin-top: 4px;
            letter-spacing: 0.02em;
        }

        .sidebar-nav {
            flex: 1;
            padding: 12px 0;
        }

        .sidebar-module {
            margin-bottom: 4px;
        }

        .sidebar-module-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.6);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px 9px 28px;
            color: rgba(255,255,255,0.7);
            font-weight: 500;
            font-size: 0.88rem;
            transition: all 0.12s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }

        .sidebar-link.active {
            color: #fff;
            background: rgba(255,255,255,0.08);
            border-left-color: #ef8b2c;
        }

        .sidebar-link.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .sidebar-link-icon {
            width: 18px;
            height: 18px;
            opacity: 0.6;
            flex-shrink: 0;
        }

        .sidebar-link-label {
            flex: 1;
        }

        .sidebar-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(239,139,44,0.2);
            color: #ef8b2c;
            font-weight: 700;
        }

        .sidebar-section {
            padding: 8px 20px 4px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.35);
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 8px 20px;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .sidebar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.12);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #fff;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
        }

        .sidebar-logout {
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s;
            font-family: inherit;
        }

        .sidebar-logout:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        /* Main content area */
        .main-area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(180deg, #edf9fc 0%, #f7fcfd 50%, #ecf7fb 100%);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 28px;
            background: rgba(255,255,255,0.88);
            border-bottom: 1px solid rgba(20, 113, 135, 0.08);
            backdrop-filter: blur(12px);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-toggle {
            display: none;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
        }

        .topbar-title {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(20,113,135,0.10);
            font-size: 0.88rem;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.15s;
        }

        .topbar-search:hover {
            border-color: rgba(20,113,135,0.2);
        }

        .topbar-search kbd {
            font-size: 0.72rem;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-family: inherit;
        }

        .topbar-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            transition: all 0.15s;
        }

        .topbar-link:hover {
            background: rgba(20,153,168,0.08);
        }

        .content-area {
            flex: 1;
            padding: 24px 28px;
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

        /* Responsive */
        @media (max-width: 1024px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed;
                left: -280px;
                width: 280px;
                z-index: 100;
                transition: left 0.3s;
            }
            .sidebar.open { left: 0; }
            .mobile-toggle { display: flex; }
            .content-area { padding: 20px 16px; }
            .topbar { padding: 14px 16px; }
        }

        @media (max-width: 768px) {
            .topbar-right { gap: 6px; }
            .topbar-search { display: none; }
        }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        /* Existing component styles preserved */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; }
        .metric {
            background: linear-gradient(180deg, #ffffff 0%, #eef9fb 100%);
            border: 1px solid rgba(23, 98, 77, 0.08);
            border-radius: var(--radius-md);
            padding: 20px;
            min-height: 132px;
            display: grid;
            align-content: space-between;
        }
        .topbar-old {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding: 24px 26px;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(237,249,251,0.92));
            border: 1px solid rgba(20, 113, 135, 0.08);
            box-shadow: var(--shadow);
        }
        .hero-card {
            padding: 28px;
            border-radius: var(--radius-xl);
            background: radial-gradient(circle at top right, rgba(239, 139, 44, 0.18), transparent 26%),
                        linear-gradient(135deg, #0f8d99 0%, #1499a8 58%, #0d7987 100%);
            color: #f4fbf7;
            box-shadow: 0 28px 64px rgba(20, 113, 135, 0.24);
        }
        .hero-card .subtle { color: rgba(244, 251, 247, 0.72); }
        .button {
            border: 0;
            border-radius: 14px;
            padding: 11px 18px;
            cursor: pointer;
            font-weight: 700;
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
        .subtle { color: var(--muted); }
        h2, h3, h4 { font-family: "Aptos Display", "Segoe UI Variable Display", "Trebuchet MS", sans-serif; letter-spacing: -0.02em; }
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
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px; border-bottom: 1px solid rgba(16, 35, 28, 0.08); vertical-align: top; }
        thead tr { text-align: left; background: #f7faf8; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
        .search-bar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; margin-bottom: 20px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; }
        .data-card {
            padding: 20px;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, rgba(255,255,255,0.98), #f4f8f5 100%);
            border: 1px solid rgba(20, 113, 135, 0.08);
            box-shadow: 0 12px 30px rgba(20, 113, 135, 0.06);
            display: grid;
            gap: 10px;
        }
        .eyebrow { text-transform: uppercase; letter-spacing: 0.14em; font-size: 0.73rem; font-weight: 700; color: var(--accent); }
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
        .chip-strong { background: #fff3e5; color: #7e3f1e; border-color: rgba(201, 109, 66, 0.16); }
        .table-wrap { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--border); }
        .table-wrap table { min-width: 600px; }
        .panel table { width: 100%; }
        .panel > div > table, .panel > table { display: block; overflow-x: auto; }
        .responsive-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .form-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
        .form-row > div { flex: 1; min-width: 160px; }
        .stat-line { display: flex; justify-content: space-between; gap: 12px; color: var(--muted); font-size: 0.92rem; }
        .section-title { margin: 0 0 14px; font-size: 1.05rem; }
        .result-type { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #e8f6f8; color: var(--primary-dark); font-size: 0.75rem; font-weight: 800; }
        .result-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #fff8ee; color: #8a4b15; font-size: 0.78rem; font-weight: 700; }
        .result-meta { display: flex; gap: 8px; flex-wrap: wrap; }

        @media (max-width: 768px) {
            form { gap: 14px; }
            input, select, textarea { padding: 12px 14px; font-size: 0.95rem; }
            .form-row { flex-direction: column; }
            .form-row > div { min-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-logo"><span class="e">e-</span>Pani</div>
                <div class="sidebar-brand-sub">Office Management Suite</div>
            </div>
            <nav class="sidebar-nav">
                <?php if (Auth::canAny('dashboard.admin', 'dashboard.crm', 'dashboard.accounts', 'dashboard.consultant', 'dashboard.client')): ?>
                <a href="<?= e(url('/dashboard')) ?>" class="sidebar-link <?= $activeModule === 'dashboard' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    <span class="sidebar-link-label">Dashboard</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::canAny('clients.view', 'clients.create', 'clients.edit', 'clients.credentials.manage')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Client Module</div>
                    <a href="<?= e(url('/clients')) ?>" class="sidebar-link <?= $activeModule === 'clients' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        <span class="sidebar-link-label">Client Register</span>
                    </a>
                    <?php if (Auth::can('clients.create')): ?>
                    <a href="<?= e(url('/clients/create')) ?>" class="sidebar-link <?= $requestUri === '/clients/create' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        <span class="sidebar-link-label">Add Client</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('service_orders.view', 'service_orders.create', 'workflow.advance', 'workflow.followup.log')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Service Orders</div>
                    <a href="<?= e(url('/service-orders')) ?>" class="sidebar-link <?= $activeModule === 'service_orders' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <span class="sidebar-link-label">Service Order Register</span>
                    </a>
                    <?php if (Auth::can('service_orders.create')): ?>
                    <a href="<?= e(url('/service-orders/create')) ?>" class="sidebar-link <?= $requestUri === '/service-orders/create' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        <span class="sidebar-link-label">Create Service Order</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::canAny('reminders.view', 'reminders.report')): ?>
                    <a href="<?= e(url('/reminders')) ?>" class="sidebar-link <?= $activeModule === 'reports' && str_contains($requestUri, '/reminders') ? '' : '' ?> <?= $requestUri === '/reminders' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        <span class="sidebar-link-label">Reminders</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('documents.view', 'documents.upload', 'documents.download', 'documents.request', 'documents.movement.view', 'documents.access_log.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Document Module</div>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <span class="sidebar-link-label">Document Register</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4-4 4"/><path d="M12 16V8"/></svg>
                        <span class="sidebar-link-label">Document Requests</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                        <span class="sidebar-link-label">Document Movement</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <?php if (Auth::canAny('documents.report', 'documents.access_log.view')): ?>
                    <a href="<?= e(url('/reports/document-access')) ?>" class="sidebar-link <?= $requestUri === '/reports/document-access' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <span class="sidebar-link-label">Document Access Log</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('dsc.view', 'dsc.usage.log', 'dsc.renewal.view', 'dsc.reports.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">DSC Module</div>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="sidebar-link-label">DSC Register</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span class="sidebar-link-label">DSC Custody</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17,1 21,5 17,9"/><path d="M3 11V9a4 4 0 014-4h14"/></svg>
                        <span class="sidebar-link-label">DSC Movement</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                        <span class="sidebar-link-label">DSC Usage Log</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        <span class="sidebar-link-label">DSC Renewal</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('workforce.view', 'attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.productivity.view', 'consultants.view', 'users.manage.internal', 'users.manage.portal')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Workforce Module</div>
                    <?php if (Auth::canAny('attendance.view', 'attendance.report.review', 'attendance.productivity.view')): ?>
                    <a href="<?= e(url('/attendance')) ?>" class="sidebar-link <?= $activeModule === 'workforce' && str_contains($requestUri, '/attendance') ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span class="sidebar-link-label">Staff Monitor</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('attendance.view')): ?>
                    <a href="<?= e(url('/attendance/today')) ?>" class="sidebar-link <?= $requestUri === '/attendance/today' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        <span class="sidebar-link-label">My Work Day</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('attendance.report.submit')): ?>
                    <a href="<?= e(url('/attendance/report')) ?>" class="sidebar-link <?= $requestUri === '/attendance/report' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <span class="sidebar-link-label">Daily Work Report</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('attendance.report.review')): ?>
                    <a href="<?= e(url('/attendance/admin')) ?>" class="sidebar-link <?= $requestUri === '/attendance/admin' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span class="sidebar-link-label">Review Daily Reports</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('attendance.productivity.view')): ?>
                    <a href="<?= e(url('/attendance/productivity')) ?>" class="sidebar-link <?= $requestUri === '/attendance/productivity' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <span class="sidebar-link-label">Productivity Summary</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('consultants.view')): ?>
                    <a href="<?= e(url('/consultants')) ?>" class="sidebar-link <?= $requestUri === '/consultants' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <span class="sidebar-link-label">Consultants</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::canAny('users.manage.internal', 'users.manage.portal')): ?>
                    <a href="<?= e(url('/users')) ?>" class="sidebar-link <?= $requestUri === '/users' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="sidebar-link-label">User Accounts</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('users.manage.rights')): ?>
                    <a href="<?= e(url('/users/rights')) ?>" class="sidebar-link <?= $requestUri === '/users/rights' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="sidebar-link-label">Roles & Permissions</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('billing.view', 'accounts.view', 'accounts.collections.view', 'accounts.outstanding.view', 'accounts.ageing.view', 'accounts.unbilled.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Accounts Module</div>
                    <?php if (Auth::can('billing.view')): ?>
                    <a href="<?= e(url('/billing')) ?>" class="sidebar-link <?= $activeModule === 'accounts' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <span class="sidebar-link-label">Billing Dashboard</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('reports.financial')): ?>
                    <a href="<?= e(url('/reports/invoices')) ?>" class="sidebar-link <?= $requestUri === '/reports/invoices' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span class="sidebar-link-label">Invoices</span>
                    </a>
                    <a href="<?= e(url('/reports/receipts')) ?>" class="sidebar-link <?= $requestUri === '/reports/receipts' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                        <span class="sidebar-link-label">Receipts</span>
                    </a>
                    <a href="<?= e(url('/reports/outstanding')) ?>" class="sidebar-link <?= $requestUri === '/reports/outstanding' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        <span class="sidebar-link-label">Outstanding</span>
                    </a>
                    <?php endif; ?>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                        <span class="sidebar-link-label">Collections</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <span class="sidebar-link-label">Collection Ageing</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('reports.view', 'reports.financial', 'reminders.report', 'documents.report', 'documents.access_log.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Reports</div>
                    <a href="<?= e(url('/reports')) ?>" class="sidebar-link <?= $activeModule === 'reports' && !str_contains($requestUri, '/reminders') ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <span class="sidebar-link-label">Reports Home</span>
                    </a>
                    <?php if (Auth::can('reports.view')): ?>
                    <a href="<?= e(url('/reports/clients')) ?>" class="sidebar-link <?= $requestUri === '/reports/clients' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <span class="sidebar-link-label">Client Reports</span>
                    </a>
                    <a href="<?= e(url('/reports/service-orders')) ?>" class="sidebar-link <?= $requestUri === '/reports/service-orders' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <span class="sidebar-link-label">Service Order Reports</span>
                    </a>
                    <a href="<?= e(url('/reports/consultants')) ?>" class="sidebar-link <?= $requestUri === '/reports/consultants' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="sidebar-link-label">Consultant Reports</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::canAny('reminders.view', 'reminders.report')): ?>
                    <a href="<?= e(url('/reminders/register')) ?>" class="sidebar-link <?= $requestUri === '/reminders/register' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        <span class="sidebar-link-label">Reminder Reports</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Auth::canAny('settings.view', 'users.manage.portal', 'users.manage.internal', 'users.manage.rights', 'settings.company.manage', 'settings.service_types.manage', 'settings.workflow.manage', 'settings.security.manage')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-module">
                    <div class="sidebar-module-header">Settings</div>
                    <?php if (Auth::canAny('users.manage.portal', 'users.manage.internal')): ?>
                    <a href="<?= e(url('/users')) ?>" class="sidebar-link <?= $requestUri === '/users' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="sidebar-link-label">User Accounts</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::can('users.manage.rights')): ?>
                    <a href="<?= e(url('/users/rights')) ?>" class="sidebar-link <?= $requestUri === '/users/rights' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="sidebar-link-label">Roles & Permissions</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::canAny('reminders.view', 'reminders.create', 'reminders.edit')): ?>
                    <a href="<?= e(url('/reminders/templates')) ?>" class="sidebar-link <?= $requestUri === '/reminders/templates' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                        <span class="sidebar-link-label">Reminder Templates</span>
                    </a>
                    <a href="<?= e(url('/reminders/escalations')) ?>" class="sidebar-link <?= $requestUri === '/reminders/escalations' ? 'active' : '' ?>">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span class="sidebar-link-label">Escalation Rules</span>
                    </a>
                    <?php endif; ?>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                        <span class="sidebar-link-label">Company Settings</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                        <span class="sidebar-link-label">Service Types</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                        <span class="sidebar-link-label">Workflow Settings</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                    <span class="sidebar-link disabled">
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="sidebar-link-label">Security Settings</span>
                        <span class="sidebar-badge">Planned</span>
                    </span>
                </div>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar"><?= e(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?></div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?= e($currentUser['full_name'] ?? 'User') ?></div>
                        <div class="sidebar-user-role"><?= e(ucfirst(str_replace('_', ' ', $currentUser['role_code'] ?? 'User'))) ?></div>
                    </div>
                </div>
                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= \App\Core\Csrf::inputField() ?>
                    <button type="submit" class="sidebar-logout">Logout</button>
                </form>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <div class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <span class="topbar-title"><?= e($title ?? 'Dashboard') ?></span>
                </div>
                <div class="topbar-right">
                    <?php if ($hasUniversalSearch): ?>
                    <a href="<?= e(url('/search')) ?>" class="topbar-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                        Search...
                        <kbd>Ctrl+K</kbd>
                    </a>
                    <?php endif; ?>
                    <a href="<?= e(url($notificationLink)) ?>" class="topbar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    </a>
                    <a href="<?= e(url($profileLink)) ?>" class="topbar-link">
                        <?= e($currentUser['full_name'] ?? 'Profile') ?>
                    </a>
                </div>
            </header>
            <div class="content-area">
                <?php if (!empty($success)): ?>
                    <div class="flash flash-success"><?= e($success) ?></div>
                <?php endif; ?>
                <?= $content ?>
                <footer class="app-footer">e-Pani : Office Management Suite from E Tax Advisors Private Limited</footer>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var toggle = document.getElementById('mobileToggle');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (toggle && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('active');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });
        }
    })();
    </script>
</body>
</html>
<?php endif; ?>
