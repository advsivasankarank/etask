<?php $appName = config('app.name', 'Compliance Management System'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Login') . ' | ' . $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F766E;
            --primary-dark: #115E59;
            --accent: #F97316;
            --text: #0F172A;
            --muted: #475569;
            --line: #E2E8F0;
            --surface: #FFFFFF;
            --surface-soft: #F8FAFC;
            --danger: #b42318;
            --success: #047857;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background:
                radial-gradient(circle at top left, rgba(15,118,110,0.08), transparent 35%),
                radial-gradient(circle at bottom right, rgba(249,115,22,0.06), transparent 35%),
                linear-gradient(180deg, #F8FBFC 0%, #FFFFFF 100%);
        }

        a { color: inherit; text-decoration: none; }

        .login-container {
            width: min(1400px, calc(100vw - 48px));
            margin: 0 auto;
        }

        /* Header */
        .pub-header {
            background: rgba(255,255,255,0.96);
            border-bottom: 1px solid rgba(226,232,240,0.88);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .pub-header-inner {
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 64px;
        }

        .pub-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .pub-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: var(--primary);
        }

        .pub-brand-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .pub-brand-text {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            white-space: nowrap;
        }

        .pub-brand-text .e { color: var(--accent); }
        .pub-brand-text .pani { color: var(--primary-dark); }

        .pub-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex: 1 1 auto;
            min-width: 0;
            flex-wrap: nowrap;
        }

        .pub-nav a {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }

        .pub-nav a:hover { color: var(--primary); }

        .pub-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
            flex-wrap: nowrap;
        }

        .pub-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 0.84rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: transform .15s ease, background .15s ease;
            font-family: inherit;
        }

        .pub-btn:hover { transform: translateY(-1px); }

        .pub-btn-primary {
            color: #fff;
            background: var(--primary);
        }

        .pub-btn-secondary {
            color: var(--primary-dark);
            background: #fff;
            border-color: #CBD5E1;
        }

        .pub-btn-ghost {
            color: var(--text);
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        .pub-btn.active {
            background: linear-gradient(135deg, rgba(15,118,110,0.12), rgba(249,115,22,0.08));
            border-color: rgba(15,118,110,0.2);
        }

        /* Main content */
        .login-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        /* Auth card */
        .auth-card {
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: var(--surface);
            border: 1px solid rgba(226,232,240,0.8);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(15,23,42,0.10);
        }

        .auth-left {
            padding: 44px;
            background: linear-gradient(160deg, #102d24 0%, #17624d 60%, #16352e 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-left .eyebrow {
            color: #ffd5c3;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .auth-left h1 {
            margin: 12px 0 16px;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .auth-left .copy-text {
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .feature-list {
            display: grid;
            gap: 10px;
            margin-top: 24px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.07);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .feature-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            flex: 0 0 auto;
        }

        .auth-right {
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-right .eyebrow {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.72rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .auth-right h2 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .auth-right .hint {
            margin: 0 0 24px;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        form { display: grid; gap: 16px; }

        label {
            display: grid;
            gap: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid var(--line);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: var(--surface);
            color: var(--text);
            transition: border-color .15s, box-shadow .15s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.12);
        }

        .form-submit {
            width: 100%;
            padding: 13px 18px;
            border: 0;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .15s;
        }

        .form-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .form-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 16px;
        }

        .form-link {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        .form-link a {
            color: var(--primary);
            font-weight: 600;
        }

        .form-link a:hover { text-decoration: underline; }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fef3f2;
            color: var(--danger);
            border: 1px solid #fecdca;
        }

        .alert-success {
            background: #ecfdf3;
            color: var(--success);
            border: 1px solid #abefc6;
        }

        /* Trust strip */
        .trust-strip {
            text-align: center;
            padding: 20px 0;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        .trust-strip span {
            display: inline-block;
            margin: 0 6px;
        }

        .trust-sep {
            color: var(--line);
        }

        /* Footer */
        .pub-footer {
            border-top: 1px solid var(--line);
            padding: 28px 0 36px;
            color: var(--muted);
        }

        .pub-footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .pub-footer-brand {
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .pub-footer-brand strong {
            color: var(--text);
            font-weight: 700;
        }

        .pub-footer-links {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .pub-footer-links a:hover { color: var(--primary); }

        .pub-footer-copy {
            width: 100%;
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            font-size: 0.82rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .pub-nav { gap: 14px; }
            .pub-actions { gap: 6px; }
            .pub-btn { min-height: 34px; padding: 0 12px; font-size: 0.8rem; }
        }

        @media (max-width: 920px) {
            .pub-header-inner {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
                padding: 12px 0;
            }
            .pub-brand { width: 100%; justify-content: center; }
            .pub-nav { width: 100%; justify-content: center; flex-wrap: wrap; gap: 6px 14px; }
            .pub-actions { width: 100%; justify-content: center; flex-wrap: wrap; gap: 6px; }
        }

        @media (max-width: 860px) {
            .auth-card { grid-template-columns: 1fr; }
            .auth-left { padding: 28px; }
            .auth-right { padding: 28px; }
        }

        @media (max-width: 768px) {
            .login-container { width: min(1400px, calc(100vw - 28px)); }
            .login-main { padding: 24px 0; }
            .auth-left { padding: 24px; }
            .auth-left h1 { font-size: 1.6rem; }
            .auth-right { padding: 24px; }
            .auth-right h2 { font-size: 1.3rem; }
            .pub-footer-inner { flex-direction: column; align-items: center; text-align: center; }
            .pub-footer-links { justify-content: center; }
        }

        @media (max-width: 576px) {
            .login-container { width: min(1400px, calc(100vw - 22px)); }
            .pub-brand-text { font-size: 1.2rem; }
            .pub-nav { gap: 4px 10px; }
            .pub-nav a { font-size: 0.78rem; }
            .pub-btn { min-height: 32px; padding: 0 10px; font-size: 0.78rem; }
            .auth-left { padding: 20px; }
            .auth-left h1 { font-size: 1.4rem; }
            .auth-right { padding: 20px; }
            .feature-item { font-size: 0.82rem; padding: 10px 12px; }
        }

        @media (max-width: 430px) {
            .pub-header-inner { padding: 8px 0; }
            .pub-brand-text { font-size: 1.1rem; }
            .pub-nav { gap: 3px 8px; }
            .pub-nav a { font-size: 0.72rem; }
            .pub-actions { flex-direction: column; align-items: stretch; gap: 4px; }
            .pub-btn { width: 100%; justify-content: center; min-height: 34px; }
            .auth-left { padding: 18px; }
            .auth-left h1 { font-size: 1.25rem; }
            .auth-right { padding: 18px; }
            .auth-right h2 { font-size: 1.15rem; }
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>
