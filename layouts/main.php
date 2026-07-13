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
$scriptDir = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
if ($scriptDir !== '' && str_starts_with($requestUri, $scriptDir)) {
    $requestUri = substr($requestUri, strlen($scriptDir));
    $requestUri = $requestUri === '' ? '/' : $requestUri;
}
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
            max-width: 100%;
            overflow-x: clip;
        }
        a { color: inherit; text-decoration: none; }

        .portal-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
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
            min-width: 0;
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

        .portal-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .portal-mobile-toggle {
            display: none;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--primary-dark);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(20, 113, 135, 0.08);
        }

        .portal-mobile-toggle svg {
            width: 22px;
            height: 22px;
        }

        .portal-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 90;
            border: 0;
            background: rgba(13, 61, 74, 0.52);
            cursor: pointer;
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
                z-index: 110;
                transition: left 0.3s;
            }
            .portal-sidebar.open { left: 0; }
            .portal-sidebar-overlay.active { display: block; }
            .portal-mobile-toggle { display: inline-flex; }
            .portal-content { padding: 16px; }
            .portal-topbar { padding: 16px; }
            .portal-topbar h1 {
                overflow: hidden;
                font-size: 1.2rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <aside class="portal-sidebar" id="portalSidebar">
            <div class="portal-brand">
                <div class="portal-brand-logo"><span class="e">e-</span>Pani</div>
                <div class="portal-brand-sub">Client Portal</div>
            </div>
            <nav class="portal-nav">
                <a href="<?= e(url('/client-portal/account')) ?>" class="<?= $requestUri === '/client-portal/account' ? 'active' : '' ?>">
                    <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    My Account
                </a>
                <a href="<?= e(url('/client-portal/pso')) ?>" class="<?= (str_starts_with($requestUri, '/client-portal/pso') || str_starts_with($requestUri, '/service-orders')) ? 'active' : '' ?>">
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
        <button type="button" class="portal-sidebar-overlay" id="portalSidebarOverlay" aria-label="Close portal navigation" tabindex="-1"></button>
        <main class="portal-content">
            <div class="portal-topbar">
                <div class="portal-topbar-left">
                    <button type="button" class="portal-mobile-toggle" id="portalMobileToggle" aria-label="Open portal navigation" aria-controls="portalSidebar" aria-expanded="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
                    </button>
                    <h1><?= e($title ?? 'Dashboard') ?></h1>
                </div>
            </div>
            <?php if (!empty($success)): ?>
                <div class="flash flash-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?= $content ?>
            <footer class="app-footer">e-Pani : Office Management Suite from E Tax Advisors Private Limited</footer>
        </main>
    </div>
    <script>
        (() => {
            const toggle = document.getElementById('portalMobileToggle');
            const sidebar = document.getElementById('portalSidebar');
            const overlay = document.getElementById('portalSidebarOverlay');
            if (!toggle || !sidebar || !overlay) return;

            const closeNavigation = () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open portal navigation');
            };

            const openNavigation = () => {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                toggle.setAttribute('aria-expanded', 'true');
                toggle.setAttribute('aria-label', 'Close portal navigation');
            };

            toggle.addEventListener('click', () => {
                if (sidebar.classList.contains('open')) closeNavigation();
                else openNavigation();
            });
            overlay.addEventListener('click', closeNavigation);
            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNavigation));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeNavigation();
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth > 900) closeNavigation();
            });
        })();
    </script>
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg: #f4f6f7;
            --surface: rgba(255, 255, 255, 0.98);
            --border: rgba(17, 96, 122, 0.10);
            --text: #101828;
            --muted: #667085;
            --primary: #1499a8;
            --primary-dark: #0d7987;
            --dark-teal: #0d3d4a;
            --accent: #f0a202;
            --success: #047857;
            --danger: #dc2626;
            --warning: #d97706;
            --shadow-sm: 0 1px 2px rgba(16, 24, 40, 0.04);
            --shadow: 0 1px 2px rgba(16, 24, 40, 0.03), 0 4px 12px rgba(16, 24, 40, 0.05);
            --shadow-lg: 0 4px 8px rgba(16, 24, 40, 0.03), 0 12px 28px rgba(16, 24, 40, 0.07);
            --radius-xl: 16px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --font-display: "Poppins", "Segoe UI Variable", sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Inter", "Segoe UI Variable", "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            max-width: 100%;
            overflow-x: clip;
        }
        a { color: inherit; text-decoration: none; }

        /* KPI cards */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .kpi-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.15s, border-color 0.15s;
        }
        .kpi-card:hover { box-shadow: var(--shadow); border-color: rgba(20, 113, 135, 0.18); }
        .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: #e8f6f8;
            color: var(--primary-dark);
        }
        .kpi-icon svg { width: 18px; height: 18px; }
        .kpi-body { min-width: 0; }
        .kpi-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; color: var(--muted); }
        .kpi-value { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--text); margin-top: 4px; line-height: 1.2; }
        .kpi-card.severity-danger .kpi-icon { background: #fee2e2; color: var(--danger); }
        .kpi-card.severity-warning .kpi-icon { background: #fef3c7; color: var(--warning); }
        .kpi-card.severity-success .kpi-icon { background: #dcfce7; color: #16a34a; }
        .kpi-card.severity-neutral .kpi-icon { background: #e8f6f8; color: var(--primary-dark); }

        .app-shell {
            display: grid;
            grid-template-columns: 76px minmax(0, 1fr);
            min-height: 100vh;
            max-width: 100%;
        }

        /* Sidebar: 76px icon rail, expands to 264px as a fixed overlay on hover */
        .sidebar {
            position: sticky;
            top: 0;
            left: 0;
            height: 100vh;
            width: 76px;
            background: #ecf2f2;
            color: var(--dark-teal);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 200;
            transition: width 0.18s ease;
        }

        .sidebar:hover,
        .sidebar.open {
            position: fixed;
            width: 264px;
            box-shadow: 8px 0 30px rgba(13, 61, 74, 0.16);
        }

        .sidebar-brand {
            padding: 24px 0;
            border-bottom: 1px solid rgba(13, 61, 74, 0.10);
            white-space: nowrap;
            overflow: hidden;
            text-align: center;
        }

        .sidebar:hover .sidebar-brand,
        .sidebar.open .sidebar-brand {
            padding: 24px 22px;
            text-align: left;
        }

        .sidebar-brand-logo {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-teal);
        }

        .sidebar-brand-logo .e { color: var(--accent); }

        .sidebar-brand-sub {
            font-size: 11px;
            color: #5b7b83;
            margin-top: 4px;
            letter-spacing: 0.03em;
        }

        .sidebar-nav {
            flex: 1;
            padding: 14px 0;
        }

        .sidebar-group-label {
            padding: 8px 20px 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6b8b91;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0 9px 20px;
            color: #14636e;
            font-weight: 500;
            font-size: 13.5px;
            transition: background 0.12s, color 0.12s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }

        .sidebar-link:hover {
            color: var(--primary-dark);
            background: rgba(20, 153, 168, 0.06);
        }

        .sidebar-link.active {
            color: var(--primary-dark);
            background: rgba(20, 153, 168, 0.12);
            border-left-color: var(--accent);
            font-weight: 600;
        }

        .sidebar-link.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .sidebar-link-icon {
            width: 18px;
            height: 18px;
            opacity: 0.85;
            flex-shrink: 0;
        }

        .sidebar-link-label,
        .sidebar-brand-sub,
        .sidebar-group-label,
        .sidebar-user-info,
        .sidebar-logout {
            display: none;
            opacity: 0;
        }

        .sidebar:hover .sidebar-link-label,
        .sidebar.open .sidebar-link-label,
        .sidebar:hover .sidebar-brand-sub,
        .sidebar.open .sidebar-brand-sub,
        .sidebar:hover .sidebar-group-label,
        .sidebar.open .sidebar-group-label,
        .sidebar:hover .sidebar-user-info,
        .sidebar.open .sidebar-user-info,
        .sidebar:hover .sidebar-logout,
        .sidebar.open .sidebar-logout {
            display: block;
            opacity: 1;
            transition: opacity 0.15s ease 0.05s;
        }

        .sidebar:hover .sidebar-link-label,
        .sidebar.open .sidebar-link-label { display: inline; flex: 1; }

        .sidebar-divider {
            height: 1px;
            background: rgba(13, 61, 74, 0.08);
            margin: 8px 20px;
        }

        .sidebar-footer {
            padding: 16px 0;
            border-top: 1px solid rgba(13, 61, 74, 0.10);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar:hover .sidebar-footer,
        .sidebar.open .sidebar-footer {
            padding: 16px 20px;
            display: block;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .sidebar:hover .sidebar-user,
        .sidebar.open .sidebar-user {
            justify-content: flex-start;
        }

        .sidebar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(13, 61, 74, 0.10);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--dark-teal);
            flex-shrink: 0;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--dark-teal);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 11px;
            color: #5b7b83;
        }

        .sidebar-logout {
            width: 100%;
            padding: 9px;
            border: 1px solid rgba(13, 61, 74, 0.15);
            border-radius: 8px;
            background: rgba(13, 61, 74, 0.05);
            color: #14636e;
            font-weight: 600;
            font-size: 12.5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s;
            font-family: inherit;
        }

        .sidebar-logout:hover {
            background: rgba(13, 61, 74, 0.10);
        }

        /* Main content area */
        .main-area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            width: 100%;
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
            padding: 18px 32px;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid rgba(20, 113, 135, 0.08);
            backdrop-filter: blur(12px);
            min-width: 0;
            max-width: 100%;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 0;
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
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 600;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            display: inline-grid;
            place-items: center;
            font-weight: 600;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex-shrink: 0;
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
            padding: 28px 32px;
            display: grid;
            gap: 22px;
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .content-area > *,
        .content-area section,
        .content-area form,
        .content-area .grid > *,
        .content-area .responsive-grid > * {
            min-width: 0;
            max-width: 100%;
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
                left: -264px;
                width: 264px;
                z-index: 210;
                transition: left 0.3s;
            }
            .sidebar:hover { left: -264px; box-shadow: none; }
            .sidebar.open { left: 0; box-shadow: 8px 0 30px rgba(13, 61, 74, 0.16); }
            .mobile-toggle { display: flex; }
            .content-area { padding: 20px 16px; }
            .topbar { padding: 14px 16px; }
        }

        @media (max-width: 768px) {
            .topbar { gap: 8px; }
            .topbar-left { gap: 10px; }
            .topbar-title { font-size: 1rem; }
            .topbar-right { gap: 2px; }
            .topbar-search { display: none; }
            .topbar-link { padding: 8px; }
            .topbar-profile-name { display: none; }
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
            min-width: 0;
            max-width: 100%;
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
            position: relative;
            padding: 28px 30px;
            border-radius: var(--radius-xl);
            background: var(--dark-teal);
            color: #f2f8f8;
            overflow: hidden;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }
        .hero-card::before {
            content: "";
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 6px;
            background: var(--accent);
        }
        .hero-card::after {
            content: "";
            position: absolute;
            top: 0; right: 6px; bottom: 0;
            width: 58%;
            max-width: 464px;
            background: linear-gradient(#25c5b4, var(--dark-teal) 100%);
            clip-path: polygon(38% 0, 100% 0, 100% 100%, 0% 100%);
        }
        .hero-card-body, .hero-card-actions { position: relative; }
        .hero-card .eyebrow { color: #5fc4d1; }
        .hero-card .subtle { color: rgba(242, 248, 248, 0.68); }
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 9px;
            font-weight: 600;
            font-size: 14px;
        }
        .hero-btn-primary { background: var(--accent); color: #1a1206; }
        .hero-btn-secondary { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; }
        .button {
            border: 0;
            border-radius: var(--radius-md);
            padding: 11px 18px;
            cursor: pointer;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 2px 6px rgba(20, 113, 135, 0.20);
        }
        .button:hover { transform: translateY(-1px); }
        .button-secondary {
            background: #f3fbfc;
            color: var(--text);
            box-shadow: none;
            border: 1px solid rgba(20, 113, 135, 0.10);
        }
        .subtle { color: var(--muted); }
        h2, h3, h4 { font-family: var(--font-display); font-weight: 600; letter-spacing: -0.01em; }
        input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid rgba(16, 35, 28, 0.12);
            border-radius: var(--radius-md);
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
        .table-wrap { width: 100%; max-width: 100%; overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--border); -webkit-overflow-scrolling: touch; }
        .table-wrap table { min-width: 600px; }
        .panel table { width: 100%; }
        .panel > div > table, .panel > table { display: block; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .responsive-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .form-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
        .form-row > div { flex: 1; min-width: 160px; }
        .stat-line { display: flex; justify-content: space-between; gap: 12px; color: var(--muted); font-size: 0.92rem; }
        .section-title { margin: 0 0 14px; font-size: 1.05rem; }
        .result-type { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #e8f6f8; color: var(--primary-dark); font-size: 0.75rem; font-weight: 800; }
        .result-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #fff8ee; color: #8a4b15; font-size: 0.78rem; font-weight: 700; }
        .result-meta { display: flex; gap: 8px; flex-wrap: wrap; }

        /* Consistent badge/status colors */
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; white-space: nowrap; }
        .badge-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-info { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-neutral { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }

        /* Avatar chip for table cells */
        .cell-with-avatar { display: inline-flex; align-items: center; gap: 8px; }
        .avatar-chip { width: 28px; height: 28px; border-radius: 50%; background: #e8f6f8; color: var(--primary-dark); display: inline-grid; place-items: center; font-weight: 600; font-size: 0.75rem; flex-shrink: 0; }

        /* Hero stat cards */
        .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .stat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: box-shadow 0.15s, border-color 0.15s;
        }
        .stat-card:hover { box-shadow: var(--shadow); border-color: rgba(20, 113, 135, 0.18); }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e8f6f8;
            color: var(--primary-dark);
            flex-shrink: 0;
        }
        .stat-card-icon svg { width: 22px; height: 22px; }
        .stat-card-value { font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; color: var(--text); line-height: 1.15; }
        .stat-card-label { font-size: 0.82rem; color: var(--muted); margin-top: 2px; font-weight: 500; }
        .stat-card.severity-danger .stat-card-icon { background: #fee2e2; color: var(--danger); }
        .stat-card.severity-warning .stat-card-icon { background: #fef3c7; color: var(--warning); }
        .stat-card.severity-success .stat-card-icon { background: #dcfce7; color: #16a34a; }

        /* Stage breakdown bars */
        .stage-bar-track { height: 6px; border-radius: 999px; background: #eef2f4; overflow: hidden; }
        .stage-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--primary), var(--primary-dark)); }

        /* Quick access tiles */
        .quick-tile {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: #fff;
            transition: box-shadow 0.15s, border-color 0.15s;
        }
        .quick-tile > span:last-child { min-width: 0; }
        .quick-tile:hover { border-color: rgba(20, 113, 135, 0.25); box-shadow: var(--shadow-sm); }
        .quick-tile-icon { width: 36px; height: 36px; border-radius: 50%; background: #e8f6f8; color: var(--primary-dark); display: grid; place-items: center; flex-shrink: 0; }
        .quick-tile-icon svg { width: 16px; height: 16px; }
        .quick-tile-title { font-weight: 600; font-size: 0.9rem; overflow-wrap: anywhere; }
        .quick-tile-sub { font-size: 0.78rem; color: var(--muted); }

        /* Improved form styling */
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem; color: var(--text); }
        label .required { color: #dc2626; margin-left: 2px; }
        .help-text { font-size: 0.82rem; color: var(--muted); margin-top: 4px; }
        .form-section { margin-bottom: 20px; padding: 18px; border: 1px solid var(--border); border-radius: var(--radius-lg); background: #fff; }
        .form-section-title { font-size: 0.95rem; font-weight: 700; margin-bottom: 14px; color: var(--text); }

        /* Improved alert styling */
        .alert { padding: 14px 16px; border-radius: var(--radius-md); margin-bottom: 18px; display: flex; align-items: flex-start; gap: 10px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

        /* Improved empty state */
        .empty-state { text-align: center; padding: 40px 20px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: var(--radius-lg); }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state-title { font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .empty-state-text { color: var(--muted); font-size: 0.92rem; }

        /* Improved table styling */
        .table-header { background: #f8fafc; }
        .table-header th { font-weight: 700; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); padding: 12px 14px; border-bottom: 2px solid var(--border); }
        .table-body td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
        .table-body tr:hover { background: #f8fafc; }

        /* Improved button styling */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: var(--radius-md); font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; border: 1px solid var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #f8fafc; }
        .btn-danger { background: #dc2626; color: #fff; border: 1px solid #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { padding: 6px 10px; font-size: 0.82rem; }

        /* Improved focus states for accessibility */
        input:focus, select:focus, textarea:focus, button:focus, a:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* Improved card hover */
        .data-card:hover { border-color: rgba(20, 113, 135, 0.2); }

        /* Improved metric card */
        .metric-card { padding: 18px; border-radius: var(--radius-lg); background: #fff; border: 1px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .metric-card-label { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); font-weight: 700; margin-bottom: 6px; }
        .metric-card-value { font-size: 1.8rem; font-weight: 800; color: var(--text); line-height: 1.2; }
        .metric-card-subtitle { font-size: 0.85rem; color: var(--muted); margin-top: 4px; }

        @media (max-width: 768px) {
            form { gap: 14px; }
            input, select, textarea { padding: 12px 14px; font-size: 0.95rem; }
            .form-row { flex-direction: column; }
            .form-row > div { min-width: 100%; }
            .form-section, .panel { padding: 16px; }
            .grid, .responsive-grid, .card-grid, .kpi-grid, .stat-row,
            .content-area [style*="minmax("] {
                grid-template-columns: minmax(0, 1fr) !important;
            }
            .search-bar { grid-template-columns: minmax(0, 1fr); }
            .toolbar { align-items: stretch; }
            .toolbar > * { min-width: 0; }
            .hero-card { padding: 22px 20px; align-items: stretch; }
            .hero-card-body { min-width: 0; }
            .hero-card-actions {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }
            .hero-btn { justify-content: center; text-align: center; }
            table:not(.mobile-card-table) {
                display: block;
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 640px) {
            .mobile-card-wrap {
                overflow: visible;
                border: 0;
                background: transparent;
            }
            .mobile-card-table,
            .mobile-card-table tbody,
            .mobile-card-table tr,
            .mobile-card-table td {
                display: block;
                width: 100%;
                min-width: 0;
            }
            .mobile-card-table {
                min-width: 0 !important;
                overflow: visible;
            }
            .mobile-card-table thead { display: none; }
            .mobile-card-table tbody { display: grid; gap: 14px; }
            .mobile-card-table tr {
                padding: 6px 14px;
                border: 1px solid var(--border);
                border-radius: var(--radius-md);
                background: #fff;
                box-shadow: var(--shadow-sm);
            }
            .mobile-card-table td {
                display: grid;
                grid-template-columns: minmax(88px, 0.42fr) minmax(0, 1fr);
                gap: 12px;
                align-items: start;
                padding: 10px 0;
                overflow-wrap: anywhere;
            }
            .mobile-card-table td::before {
                content: attr(data-label);
                color: var(--muted);
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }
            .mobile-card-table td.mobile-card-actions {
                grid-template-columns: minmax(0, 1fr);
                padding-top: 12px;
            }
            .mobile-card-table td.mobile-card-actions::before { content: none; }
            .mobile-card-table td.mobile-card-actions > div {
                justify-content: stretch !important;
            }
            .mobile-card-table td.mobile-card-actions .btn {
                flex: 1;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-logo"><span class="e">e-</span><span class="sidebar-link-label">Pani</span></div>
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
                <div class="sidebar-group-label">Client Module</div>
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
                <?php endif; ?>

                <?php if (Auth::canAny('service_orders.view', 'service_orders.create', 'workflow.advance', 'workflow.followup.log', 'reminders.view', 'reminders.report', 'documents.view', 'documents.upload', 'documents.download', 'documents.request', 'documents.movement.view', 'documents.access_log.view', 'dsc.view', 'dsc.usage.log', 'dsc.renewal.view', 'dsc.reports.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-group-label">Service Delivery</div>
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
                <a href="<?= e(url('/reminders')) ?>" class="sidebar-link <?= $requestUri === '/reminders' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    <span class="sidebar-link-label">Compliance Tracker</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::canAny('documents.view', 'documents.download')): ?>
                <a href="<?= e(url('/documents')) ?>" class="sidebar-link <?= $activeModule === 'documents' && $requestUri === '/documents' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span class="sidebar-link-label">Document Register</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('documents.request')): ?>
                <a href="<?= e(url('/documents/requests')) ?>" class="sidebar-link <?= $requestUri === '/documents/requests' || str_starts_with($requestUri, '/documents/requests') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4-4 4"/><path d="M12 16V8"/></svg>
                    <span class="sidebar-link-label">Document Requests</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('documents.movement.view')): ?>
                <a href="<?= e(url('/documents/movement')) ?>" class="sidebar-link <?= $requestUri === '/documents/movement' || str_starts_with($requestUri, '/documents/movement') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    <span class="sidebar-link-label">Document Movement</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::canAny('documents.report', 'documents.access_log.view')): ?>
                <a href="<?= e(url('/reports/document-access')) ?>" class="sidebar-link <?= $requestUri === '/reports/document-access' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span class="sidebar-link-label">Document Access Log</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('dsc.view')): ?>
                <a href="<?= e(url('/dsc')) ?>" class="sidebar-link <?= $activeModule === 'dsc' && $requestUri === '/dsc' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="16" r="1"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="sidebar-link-label">DSC Register</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('dsc.movement.view')): ?>
                <a href="<?= e(url('/dsc/movement')) ?>" class="sidebar-link <?= $requestUri === '/dsc/movement' || str_starts_with($requestUri, '/dsc/movement') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17,1 21,5 17,9"/><path d="M3 11V9a4 4 0 014-4h14"/></svg>
                    <span class="sidebar-link-label">DSC Movement</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('dsc.usage.view')): ?>
                <a href="<?= e(url('/dsc/usage')) ?>" class="sidebar-link <?= $requestUri === '/dsc/usage' || str_starts_with($requestUri, '/dsc/usage') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <span class="sidebar-link-label">DSC Usage Log</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('dsc.renewal.view')): ?>
                <a href="<?= e(url('/dsc/renewals')) ?>" class="sidebar-link <?= $requestUri === '/dsc/renewals' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                    <span class="sidebar-link-label">DSC Renewals</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('dsc.reports.view')): ?>
                <a href="<?= e(url('/dsc/reports')) ?>" class="sidebar-link <?= $requestUri === '/dsc/reports' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span class="sidebar-link-label">DSC Reports</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (Auth::canAny('workforce.view', 'attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.productivity.view', 'workforce.consultants.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-group-label">Workforce</div>
                <?php if (Auth::can('workforce.view')): ?>
                <a href="<?= e(url('/workforce')) ?>" class="sidebar-link <?= $activeModule === 'workforce' && $requestUri === '/workforce' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <span class="sidebar-link-label">Workforce Dashboard</span>
                </a>
                <?php endif; ?>
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
                <?php if (Auth::can('workforce.consultants.view')): ?>
                <a href="<?= e(url('/workforce/consultants')) ?>" class="sidebar-link <?= $requestUri === '/workforce/consultants' || str_starts_with($requestUri, '/workforce/consultants') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <span class="sidebar-link-label">Consultant Register</span>
                </a>
                <a href="<?= e(url('/workforce/consultant-assignments')) ?>" class="sidebar-link <?= $requestUri === '/workforce/consultant-assignments' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                    <span class="sidebar-link-label">Consultant Assignments</span>
                </a>
                <a href="<?= e(url('/workforce/consultant-bills')) ?>" class="sidebar-link <?= $requestUri === '/workforce/consultant-bills' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span class="sidebar-link-label">Consultant Bills</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (Auth::canAny('billing.view', 'accounts.view', 'reports.view', 'reports.financial', 'reminders.report', 'documents.report', 'documents.access_log.view')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-group-label">Finance &amp; Reports</div>
                <?php if (Auth::canAny('accounts.view', 'billing.view')): ?>
                <a href="<?= e(url('/accounts')) ?>" class="sidebar-link <?= $activeModule === 'accounts' && $requestUri === '/accounts' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span class="sidebar-link-label">Accounts Dashboard</span>
                </a>
                <a href="<?= e(url('/accounts/invoices')) ?>" class="sidebar-link <?= $requestUri === '/accounts/invoices' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                    <span class="sidebar-link-label">Invoices</span>
                </a>
                <a href="<?= e(url('/accounts/receipts')) ?>" class="sidebar-link <?= $requestUri === '/accounts/receipts' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <span class="sidebar-link-label">Receipts</span>
                </a>
                <a href="<?= e(url('/accounts/payments')) ?>" class="sidebar-link <?= $requestUri === '/accounts/payments' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span class="sidebar-link-label">Payments</span>
                </a>
                <a href="<?= e(url('/accounts/outstanding')) ?>" class="sidebar-link <?= $requestUri === '/accounts/outstanding' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    <span class="sidebar-link-label">Outstanding</span>
                </a>
                <a href="<?= e(url('/accounts/ageing')) ?>" class="sidebar-link <?= $requestUri === '/accounts/ageing' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span class="sidebar-link-label">Collection Ageing</span>
                </a>
                <a href="<?= e(url('/accounts/followups')) ?>" class="sidebar-link <?= $requestUri === '/accounts/followups' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    <span class="sidebar-link-label">Follow-ups</span>
                </a>
                <a href="<?= e(url('/accounts/consultant-payables')) ?>" class="sidebar-link <?= $requestUri === '/accounts/consultant-payables' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <span class="sidebar-link-label">Consultant Payables</span>
                </a>
                <a href="<?= e(url('/accounts/unbilled-work')) ?>" class="sidebar-link <?= $requestUri === '/accounts/unbilled-work' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
                    <span class="sidebar-link-label">Unbilled Work</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::canAny('reports.view', 'reports.financial', 'reminders.report', 'documents.report', 'documents.access_log.view')): ?>
                <a href="<?= e(url('/reports')) ?>" class="sidebar-link <?= $activeModule === 'reports' && !str_contains($requestUri, '/reminders') ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span class="sidebar-link-label">Reports Dashboard</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (Auth::canAny('settings.view', 'users.manage.portal', 'users.manage.internal', 'users.manage.rights')): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-group-label">Administration</div>
                <?php if (Auth::can('settings.view')): ?>
                <a href="<?= e(url('/settings')) ?>" class="sidebar-link <?= $activeModule === 'settings' && $requestUri === '/settings' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    <span class="sidebar-link-label">Settings Dashboard</span>
                </a>
                <a href="<?= e(url('/settings/company')) ?>" class="sidebar-link <?= $requestUri === '/settings/company' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="sidebar-link-label">Company Settings</span>
                </a>
                <a href="<?= e(url('/settings/service-types')) ?>" class="sidebar-link <?= $requestUri === '/settings/service-types' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    <span class="sidebar-link-label">Service Types</span>
                </a>
                <a href="<?= e(url('/settings/workflow')) ?>" class="sidebar-link <?= $requestUri === '/settings/workflow' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <span class="sidebar-link-label">Workflow Settings</span>
                </a>
                <a href="<?= e(url('/settings/milestones')) ?>" class="sidebar-link <?= $requestUri === '/settings/milestones' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                    <span class="sidebar-link-label">Milestones</span>
                </a>
                <a href="<?= e(url('/settings/reminder-templates')) ?>" class="sidebar-link <?= $requestUri === '/settings/reminder-templates' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span class="sidebar-link-label">Reminder Templates</span>
                </a>
                <a href="<?= e(url('/settings/numbering')) ?>" class="sidebar-link <?= $requestUri === '/settings/numbering' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span class="sidebar-link-label">Numbering</span>
                </a>
                <a href="<?= e(url('/settings/notifications')) ?>" class="sidebar-link <?= $requestUri === '/settings/notifications' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span class="sidebar-link-label">Notifications</span>
                </a>
                <a href="<?= e(url('/settings/security')) ?>" class="sidebar-link <?= $requestUri === '/settings/security' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="sidebar-link-label">Security</span>
                </a>
                <a href="<?= e(url('/settings/maintenance')) ?>" class="sidebar-link <?= $requestUri === '/settings/maintenance' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                    <span class="sidebar-link-label">Maintenance</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::canAny('users.manage.portal', 'users.manage.internal')): ?>
                <a href="<?= e(url('/users')) ?>" class="sidebar-link <?= $requestUri === '/users' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="sidebar-link-label">User Accounts</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::can('users.manage.rights')): ?>
                <a href="<?= e(url('/users/rights')) ?>" class="sidebar-link <?= $requestUri === '/users/rights' ? 'active' : '' ?>">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="sidebar-link-label">Roles &amp; Permissions</span>
                </a>
                <?php endif; ?>
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
                    <a href="<?= e(url($profileLink)) ?>" class="topbar-link" style="gap:10px;" aria-label="Profile: <?= e($currentUser['full_name'] ?? 'User') ?>">
                        <span class="topbar-avatar"><?= e(strtoupper(substr((string) ($currentUser['full_name'] ?? 'U'), 0, 1))) ?></span>
                        <span class="topbar-profile-name"><?= e($currentUser['full_name'] ?? 'Profile') ?></span>
                    </a>
                </div>
            </header>
            <div class="content-area">
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
