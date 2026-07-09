<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Welcome') . ' | ' . config('app.name', 'Compliance Management System')) ?></title>
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
            --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 12px 32px rgba(15, 23, 42, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
            --container: 1400px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #F8FBFC 0%, #FFFFFF 22%, #FFFFFF 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page { min-height: 100vh; }

        .container {
            width: min(var(--container), calc(100vw - 48px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(226, 232, 240, 0.88);
            backdrop-filter: blur(8px);
        }

        .topbar-shell {
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 72px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: var(--primary);
        }

        .brand-mark svg,
        .card-icon svg,
        .contact-row svg {
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .brand-mark svg {
            width: 20px;
            height: 20px;
            stroke-width: 1.9;
        }

        .brand-copy {
            display: grid;
            gap: 3px;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
            white-space: nowrap;
        }

        .brand-title .e { color: var(--accent); }
        .brand-title .pani { color: var(--primary-dark); }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 500;
            display: none;
        }

        .nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex: 1 1 auto;
            min-width: 0;
            flex-wrap: nowrap;
        }

        .nav a {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }

        .nav a:hover { color: var(--primary); }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end;
            flex: 0 0 auto;
            flex-wrap: nowrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 0.88rem;
            font-weight: 700;
            white-space: nowrap;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }

        .button:hover { transform: translateY(-1px); }

        .button-primary {
            color: #FFFFFF;
            background: var(--primary);
            box-shadow: 0 10px 22px rgba(15, 118, 110, 0.16);
        }

        .button-secondary {
            color: var(--primary-dark);
            background: #FFFFFF;
            border-color: #CBD5E1;
        }

        .button-ghost {
            color: var(--text);
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        .toast {
            position: fixed;
            top: 96px;
            right: 20px;
            z-index: 90;
            min-width: 280px;
            max-width: min(380px, calc(100vw - 32px));
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #BBF7D0;
            background: #F0FDF4;
            color: #166534;
            box-shadow: var(--shadow-md);
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: opacity .24s ease, transform .24s ease;
        }

        .toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .hero {
            padding: 54px 0 34px;
            background: linear-gradient(180deg, #F8FBFC 0%, #FFFFFF 100%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            gap: 48px;
            align-items: center;
        }

        .eyebrow,
        .label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #EFF6FF;
            color: var(--primary-dark);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .label {
            padding: 0;
            border-radius: 0;
            background: transparent;
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            margin-bottom: 10px;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(2.5rem, 4.5vw, 3.6rem);
            line-height: 1.08;
            letter-spacing: -0.05em;
            max-width: 720px;
        }

        .hero h1 .accent { color: var(--accent); }

        .hero-subtitle {
            margin: 20px 0 0;
            font-size: clamp(1.05rem, 1.8vw, 1.25rem);
            line-height: 1.68;
            color: var(--muted);
            max-width: 760px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .hero-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
            max-width: 720px;
        }

        .hero-panel,
        .workflow-shell,
        .card {
            border: 1px solid var(--line);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
        }

        .hero-panel {
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .hero-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: #FCFDFE;
        }

        .hero-panel-brand {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .hero-panel-brand .e { color: var(--accent); }
        .hero-panel-brand .pani { color: var(--primary-dark); }

        .hero-panel-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .panel-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #CBD5E1;
        }

        .hero-panel-body {
            padding: 18px;
            background: #FFFFFF;
        }

        .metrics-grid,
        .workspace-grid,
        .stats-shell,
        .grid-2,
        .grid-3,
        .grid-4,
        .workflow-grid {
            display: grid;
            gap: 16px;
        }

        .metrics-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .workspace-grid { grid-template-columns: 1fr 1fr; margin-top: 16px; }
        .stats-shell { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .workflow-grid { grid-template-columns: repeat(7, minmax(0, 1fr)); }

        .metric-card,
        .workspace-card,
        .stat-card {
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background: var(--surface-soft);
        }

        .metric-card,
        .workspace-card { padding: 16px; }

        .metric-card strong {
            display: block;
            font-size: 1.04rem;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .metric-card span,
        .stat-label {
            color: var(--muted);
            font-size: 0.89rem;
            line-height: 1.65;
        }

        .workspace-card h3,
        .card h3 {
            margin: 0 0 10px;
            font-size: 1.04rem;
        }

        .list {
            display: grid;
            gap: 10px;
        }

        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #FFFFFF;
            color: #1E293B;
            font-size: 0.92rem;
        }

        .list-item span {
            color: var(--muted);
            font-weight: 600;
            text-align: right;
        }

        .stats-strip {
            padding: 18px 0 22px;
        }

        .stat-card {
            padding: 24px 22px;
            border-radius: var(--radius-lg);
            background: #FFFFFF;
        }

        .section {
            padding: 56px 0;
        }

        .section-head {
            max-width: 760px;
            margin-bottom: 26px;
        }

        .section-head h2 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.08;
            letter-spacing: -0.05em;
        }

        .section-head p,
        .card p {
            margin: 14px 0 0;
            font-size: 0.98rem;
            line-height: 1.75;
            color: var(--muted);
        }

        .card {
            padding: 24px;
            border-radius: var(--radius-lg);
        }

        .card-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            color: var(--primary-dark);
            margin-bottom: 16px;
        }

        .card-icon svg {
            width: 22px;
            height: 22px;
        }

        .workflow-shell {
            border-radius: var(--radius-xl);
            padding: 24px;
        }

        .workflow-step {
            position: relative;
            padding: 18px;
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            background: var(--surface-soft);
        }

        .workflow-step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 50%;
            right: -14px;
            width: 14px;
            height: 2px;
            background: #CBD5E1;
            transform: translateY(-50%);
        }

        .workflow-step strong {
            display: block;
            margin-bottom: 8px;
            font-size: 0.96rem;
        }

        .workflow-step span {
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.6;
        }

        .usecase-card,
        .contact-card {
            display: grid;
            gap: 14px;
        }

        .usecase-list {
            display: grid;
            gap: 10px;
        }

        .usecase-point {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #1E293B;
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .usecase-point::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--accent);
            margin-top: 7px;
            flex: 0 0 auto;
        }

        .cta-section {
            padding: 10px 0 56px;
        }

        .cta-shell {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 18px;
        }

        .cta-primary {
            padding: 30px;
            border-radius: var(--radius-xl);
            background: var(--primary-dark);
            color: #FFFFFF;
            box-shadow: var(--shadow-md);
        }

        .cta-primary .label {
            color: rgba(255, 255, 255, 0.72);
        }

        .cta-primary h2 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.08;
            letter-spacing: -0.05em;
        }

        .cta-primary p {
            margin: 14px 0 0;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.8;
        }

        .cta-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .button-light {
            background: #FFFFFF;
            color: var(--primary-dark);
        }

        .contact-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .contact-row svg {
            width: 20px;
            height: 20px;
            color: var(--primary);
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .contact-row strong {
            display: block;
            color: var(--text);
            margin-bottom: 2px;
        }

        .footer {
            padding: 26px 0 36px;
        }

        .footer-shell {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding-top: 22px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.92rem;
        }

        .footer-nav {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-weight: 600;
        }

        .about-note {
            margin-top: 24px;
            padding: 16px 20px;
            border-radius: 12px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.7;
            max-width: 640px;
        }

        .about-note strong { color: var(--text); }

        .dashboard-metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            border-radius: 10px;
            background: #FFFFFF;
            border: 1px solid var(--line);
        }

        .dashboard-metric-label {
            font-size: 0.84rem;
            color: var(--muted);
            font-weight: 500;
        }

        .dashboard-metric-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text);
        }

        .dashboard-metric-value.accent { color: var(--accent); }
        .dashboard-metric-value.primary { color: var(--primary); }

        .capability-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            background: #F0FDF9;
            border: 1px solid rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .capability-chip::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary);
        }

        @media (max-width: 1200px) {
            .topbar-shell { gap: 14px; }
            .nav { gap: 14px; }
            .actions { gap: 8px; }
            .button { min-height: 38px; padding: 0 12px; font-size: 0.84rem; }
        }

        @media (max-width: 1120px) {
            .hero-grid,
            .cta-shell {
                grid-template-columns: 1fr;
            }

            .hero h1 { max-width: 100%; }
            .hero-subtitle { max-width: 100%; }
            .about-note { max-width: 100%; }

            .grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-3,
            .stats-shell,
            .workflow-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .workflow-step:not(:last-child)::after {
                display: none;
            }
        }

        @media (max-width: 920px) {
            .topbar-shell {
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
                min-height: auto;
                padding: 14px 0;
            }

            .brand {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .brand-copy { align-items: center; }
            .brand-subtitle { display: block; font-size: 0.82rem; }

            .nav {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 8px 16px;
            }

            .actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 8px;
            }

            .hero { padding-top: 32px; }
            .hero-panel { max-width: 100%; }
        }

        @media (max-width: 768px) {
            .container {
                width: min(var(--container), calc(100vw - 28px));
            }

            .topbar { padding: 0; border: none; backdrop-filter: none; background: transparent; }
            .topbar-shell { padding: 10px 0; min-height: auto; gap: 10px; }

            .brand-title { font-size: 1.4rem; }
            .brand-subtitle { display: block; font-size: 0.78rem; }

            .nav { gap: 6px 12px; }
            .nav a { font-size: 0.82rem; }

            .actions { gap: 6px; }
            .button { min-height: 36px; padding: 0 10px; font-size: 0.8rem; border-radius: 8px; }

            .hero { padding: 20px 0 16px; }
            .hero h1 { font-size: clamp(1.6rem, 7vw, 2.4rem); }
            .hero-subtitle { font-size: 0.92rem; }
            .about-note { font-size: 0.82rem; padding: 12px 14px; }
            .hero-panel-body { padding: 12px; }
            .dashboard-metric { padding: 8px 12px; }
            .dashboard-metric-label { font-size: 0.78rem; }
            .dashboard-metric-value { font-size: 0.92rem; }
            .metrics-grid { grid-template-columns: 1fr; }
            .workspace-grid { grid-template-columns: 1fr; }

            .section { padding: 32px 0; }
            .section-head h2 { font-size: clamp(1.4rem, 5vw, 2rem); }

            .cta-shell { grid-template-columns: 1fr; }
            .cta-primary { padding: 20px; }
            .cta-primary h2 { font-size: clamp(1.4rem, 5vw, 2rem); }

            .footer-shell { flex-direction: column; align-items: center; text-align: center; }
            .footer-nav { justify-content: center; }
        }

        @media (max-width: 576px) {
            .container {
                width: min(var(--container), calc(100vw - 22px));
            }

            .brand-title { font-size: 1.2rem; }
            .brand-mark { width: 34px; height: 34px; }
            .brand-mark svg { width: 18px; height: 18px; }

            .nav { gap: 4px 10px; }
            .nav a { font-size: 0.78rem; }

            .button { min-height: 34px; padding: 0 8px; font-size: 0.78rem; }

            .hero h1 { font-size: clamp(1.3rem, 6vw, 2rem); letter-spacing: -0.04em; }
            .hero-subtitle { font-size: 0.88rem; }
            .about-note { font-size: 0.78rem; padding: 10px 12px; }
            .hero-actions { gap: 8px; }
            .hero-note { font-size: 0.85rem; }
            .capability-chip { font-size: 0.72rem; padding: 4px 8px; }

            .hero-panel-head { padding: 10px 12px; }
            .hero-panel-brand { font-size: 1.1rem; }
            .hero-panel-tools span { display: none; }

            .grid-4, .grid-3, .grid-2, .stats-shell, .metrics-grid, .workspace-grid, .workflow-grid {
                grid-template-columns: 1fr;
            }

            .card, .workflow-shell, .cta-primary { padding: 16px; }
            .stat-card { padding: 16px 14px; }

            .section { padding: 24px 0; }
        }

        @media (max-width: 430px) {
            .topbar-shell { padding: 8px 0; }
            .brand-title { font-size: 1.1rem; }
            .brand-subtitle { display: none; }
            .nav { gap: 3px 8px; }
            .nav a { font-size: 0.72rem; }
            .actions { flex-direction: column; align-items: stretch; gap: 6px; }
            .button { width: 100%; justify-content: center; min-height: 36px; }
            .hero { padding: 14px 0 12px; }
            .hero h1 { font-size: 1.3rem; }
            .hero-subtitle { font-size: 0.82rem; }
            .cta-primary { padding: 16px; }
            .cta-primary h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
<div class="page">
    <?php if (!empty($success)): ?>
        <div class="toast" id="logout-toast" role="status" aria-live="polite"><?= e($success) ?></div>
    <?php endif; ?>

    <header class="topbar">
        <div class="container topbar-shell">
            <div class="brand">
                <div class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 8.5 12 4l8 4.5v7c0 3.7-2.5 6.97-6 7.9-3.5-.93-6-4.2-6-7.9v-7Z"/><path d="M9.5 12.5 11.5 14.5 15.5 10.5"/></svg>
                </div>
                <div class="brand-copy">
                    <div class="brand-title"><span class="e">e-</span><span class="pani">Pani</span></div>
                    <div class="brand-subtitle">Practice Management Platform for Compliance Professionals</div>
                </div>
            </div>
            <nav class="nav">
                <a href="#why-epani">Why e-Pani</a>
                <a href="#features">Features</a>
                <a href="#workflow">How It Works</a>
                <a href="#use-cases">Use Cases</a>
                <a href="#security">Security</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="actions">
                <a class="button button-primary" href="#contact">Book a Live Demo</a>
                <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Staff Login</a>
                <a class="button button-ghost" href="<?= e(url('/login?audience=portal')) ?>">Client Login</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-grid">
                    <div>
                        <div class="eyebrow">Complete office automation for professional practices</div>
                        <h1>Office Automation for <span class="accent">Tax, Legal &amp; Compliance</span> Professionals</h1>
                        <p class="hero-subtitle">Manage daily work, staff productivity, service orders, documents, billing, collections, reminders and client communication from one secure platform.</p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#contact">Book a Live Demo</a>
                            <a class="button button-secondary" href="#features">Explore Features</a>
                        </div>
                        <div class="about-note">
                            <strong>About e-Pani:</strong> e-Pani comes from the Tamil word "பணி" (Pani), meaning work, duty, assignment, or task. Built for Tax Practitioners, Advocates, Chartered Accountants, Company Secretaries, and Compliance Professionals who need disciplined execution with visibility and control.
                        </div>
                    </div>
                    <div class="hero-panel">
                        <div class="hero-panel-head">
                            <div class="hero-panel-brand"><span class="e">e-</span><span class="pani">Pani</span></div>
                            <div class="hero-panel-tools">
                                <div class="panel-dot"></div>
                                <div class="panel-dot"></div>
                                <div class="panel-dot"></div>
                                <span>Today's Office Control</span>
                            </div>
                        </div>
                        <div class="hero-panel-body">
                            <div style="display:grid;gap:8px;">
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Pending Works</span>
                                    <span class="dashboard-metric-value accent">18</span>
                                </div>
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Due Today</span>
                                    <span class="dashboard-metric-value accent">7</span>
                                </div>
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Staff Online</span>
                                    <span class="dashboard-metric-value primary">5</span>
                                </div>
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Documents Pending</span>
                                    <span class="dashboard-metric-value">11</span>
                                </div>
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Outstanding Bills</span>
                                    <span class="dashboard-metric-value accent">₹2.45L</span>
                                </div>
                                <div class="dashboard-metric">
                                    <span class="dashboard-metric-label">Collections Due</span>
                                    <span class="dashboard-metric-value">9</span>
                                </div>
                            </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;">
                                <span class="capability-chip">Staff Monitor</span>
                                <span class="capability-chip">Service Orders</span>
                                <span class="capability-chip">Billing</span>
                                <span class="capability-chip">Client Portal</span>
                            </div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                                <span class="capability-chip">Document Repository</span>
                                <span class="capability-chip">Reminders</span>
                                <span class="capability-chip">Audit Trail</span>
                            </div>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                                <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Staff Login</a>
                                <a class="button button-primary" href="<?= e(url('/login?audience=portal')) ?>">Client Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats-strip">
            <div class="container">
                <div class="section-head" style="margin-bottom:18px;">
                    <div class="label">Core Capabilities</div>
                    <h2>One platform to run your entire professional practice with structure and control.</h2>
                </div>
                <div class="stats-shell">
                    <div class="stat-card">
                        <h3>Work Register</h3>
                        <div class="stat-label">Assign, track and close client work with ownership, deadlines, and review checkpoints.</div>
                    </div>
                    <div class="stat-card">
                        <h3>Staff Monitor</h3>
                        <div class="stat-label">Track login, work sessions, idle time and daily work reports for full team visibility.</div>
                    </div>
                    <div class="stat-card">
                        <h3>Client Management</h3>
                        <div class="stat-label">Maintain client profiles, services, contacts, tax identifiers and service history.</div>
                    </div>
                    <div class="stat-card">
                        <h3>Document Centre</h3>
                        <div class="stat-label">Store, version, search and control access to client and operational documents.</div>
                    </div>
                    <div class="stat-card">
                        <h3>Billing &amp; Collections</h3>
                        <div class="stat-label">Raise invoices, record payments, track dues and monitor collection follow-ups.</div>
                    </div>
                    <div class="stat-card">
                        <h3>Reminders</h3>
                        <div class="stat-label">Manage statutory, internal and client follow-up deadlines with escalation alerts.</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="why-epani">
            <div class="container">
                <div class="section-head">
                    <div class="label">Why e-Pani</div>
                    <h2>Designed for firms that need clear execution, trusted records, and stronger control over client work.</h2>
                    <p>e-Pani brings together client management, assignment handling, document control, billing discipline, and review visibility in a format suitable for commercial deployment.</p>
                </div>
                <div class="grid-3">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg></div>
                        <h3>Explainable workflow</h3>
                        <p>Every assignment follows a clear path from request to closure, reducing dependency on memory, calls, and scattered follow-up.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 2 4 6v6c0 5.3 3.4 10 8 11 4.6-1 8-5.7 8-11V6l-8-4Z"/><path d="M9 12.5 11 14.5l4-4"/></svg></div>
                        <h3>Professional trust</h3>
                        <p>Role-based access, secure document handling, and audit trails strengthen internal governance and client confidence.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></div>
                        <h3>Management visibility</h3>
                        <p>Partners and managers get a practical view of pending work, collections, review bottlenecks, and compliance exposure.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-head">
                    <div class="label">Features</div>
                    <h2>Compact, high-value modules built for professional service operations.</h2>
                    <p>Reuse one structured workspace for client intake, service execution, reminders, billing, and controlled records.</p>
                </div>
                <div class="grid-4">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z"/><path d="M4 21c0-3.3 3.6-6 8-6s8 2.7 8 6"/></svg></div>
                        <h3>Client Management</h3>
                        <p>Maintain client records, ownership, identifiers, and service history in one place.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v6h6"/></svg></div>
                        <h3>Service Orders</h3>
                        <p>Create governed assignments with period, company, service type, and numbering control.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg></div>
                        <h3>Workflow Engine</h3>
                        <p>Move work through milestones, review stages, pending statuses, and closure controls.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg></div>
                        <h3>Document Vault</h3>
                        <p>Securely organize proofs, acknowledgements, uploads, and version-aware records.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Z"/><path d="M18 16V11a6 6 0 1 0-12 0v5l-2 2h16l-2-2Z"/></svg></div>
                        <h3>Reminder Engine</h3>
                        <p>Run reminder schedules, escalations, and dashboard follow-up for due work.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h4"/></svg></div>
                        <h3>Invoice Management</h3>
                        <p>Handle invoices, receipts, advances, disbursements, and collection visibility.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 8v5l3 3"/><circle cx="12" cy="12" r="9"/></svg></div>
                        <h3>Audit Trail</h3>
                        <p>Preserve traceable logs for workflow, billing, document access, and user actions.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg></div>
                        <h3>Client Portal</h3>
                        <p>Allow client requests, document submission, query response, payments, and status view.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="workflow">
            <div class="container">
                <div class="section-head">
                    <div class="label">How e-Pani Works</div>
                    <h2>From client request to final closure, every step is tracked and controlled.</h2>
                    <p>Keep each engagement visible, reviewable, billable, and accountable through a single operating model.</p>
                </div>
                <div class="workflow-shell">
                    <div class="workflow-grid">
                        <div class="workflow-step">
                            <strong>Client Created</strong>
                            <span>Capture client profile, tax identifiers, and portal access.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Service Order</strong>
                            <span>Create the formal assignment with period and service type.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Staff Assigned</strong>
                            <span>Allocate work to the appropriate internal or consultant resource.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Work Tracked</strong>
                            <span>Track preparation, filing, client dependency, and progress.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Reviewed</strong>
                            <span>Control checks, approvals, and query handling before completion.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Billed</strong>
                            <span>Raise invoices and connect collections to the assignment.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>Closed</strong>
                            <span>Complete procedural and financial closure with records.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="use-cases">
            <div class="container">
                <div class="section-head">
                    <div class="label">Use Cases</div>
                    <h2>Relevant for firms managing recurring compliance, governed assignments, and client-facing delivery.</h2>
                    <p>e-Pani adapts to different professional service models while maintaining a common operational discipline.</p>
                </div>
                <div class="grid-3">
                    <div class="card usecase-card">
                        <h3>Tax Practice</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">ITR, TDS, GST, and response workflows</div>
                            <div class="usecase-point">Filing and e-verification visibility</div>
                            <div class="usecase-point">Client and collection continuity</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Legal Practice</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Matter-level assignment and review tracking</div>
                            <div class="usecase-point">Controlled document records and remarks</div>
                            <div class="usecase-point">Evidence-led operational discipline</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Accounting Firms</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Client records, recurring assignments, and billing flow</div>
                            <div class="usecase-point">Execution visibility across multiple staff roles</div>
                            <div class="usecase-point">Collections and reporting oversight</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Corporate Compliance</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Internal ownership for periodic compliance tasks</div>
                            <div class="usecase-point">Deadline tracking, review notes, and closure history</div>
                            <div class="usecase-point">Traceable office records for governance</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Professional Service Firms</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Client-centric work management in one controlled workspace</div>
                            <div class="usecase-point">Secure documents, reminders, and billing continuity</div>
                            <div class="usecase-point">Operational clarity without fragmented tools</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="security">
            <div class="container">
                <div class="section-head">
                    <div class="label">Security &amp; Control</div>
                    <h2>Designed to protect records, limit access appropriately, and preserve accountability.</h2>
                    <p>e-Pani helps firms operate with confidence by strengthening both data protection and operational traceability.</p>
                </div>
                <div class="grid-3">
                    <div class="card">
                        <h3>Role Based Access</h3>
                        <p>Control what internal users, consultants, accounts teams, and clients can view or act on.</p>
                    </div>
                    <div class="card">
                        <h3>Document Security</h3>
                        <p>Serve documents through authenticated endpoints instead of exposing file paths publicly.</p>
                    </div>
                    <div class="card">
                        <h3>Audit Logs</h3>
                        <p>Preserve traceable records for workflow actions, downloads, billing, and reminders.</p>
                    </div>
                    <div class="card">
                        <h3>Activity Tracking</h3>
                        <p>Track operational activity by user, assignment, module, and milestone for better oversight.</p>
                    </div>
                    <div class="card">
                        <h3>Permission Controls</h3>
                        <p>Support structured access decisions for modules, actions, reports, and workflow movement.</p>
                    </div>
                    <div class="card">
                        <h3>Secure Cloud Storage</h3>
                        <p>Use storage patterns suited for safer deployment, retention, and protected document handling.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section" id="contact">
            <div class="container">
                <div class="cta-shell">
                    <div class="cta-primary">
                        <div class="label">Book a Live Demo</div>
                        <h2>See how e-Pani can organize your practice operations end to end.</h2>
                        <p>Book a guided walkthrough to understand how e-Pani can support client management, workflow execution, secure records, compliance monitoring, and billing control in your firm.</p>
                        <div class="cta-actions">
                            <a class="button button-light" href="mailto:hello@etaxadv.com?subject=e-Pani%20Live%20Demo%20Request">Book a Live Demo</a>
                            <a class="button button-secondary" href="#features">Explore Features</a>
                            <a class="button button-ghost" href="<?= e(url('/login?audience=internal')) ?>">Staff Login</a>
                        </div>
                    </div>
                    <div class="card contact-card">
                        <div class="label">Contact</div>
                        <h3 style="margin:0;font-size:1.5rem;line-height:1.25;">Talk to E Tax Advisors Private Limited</h3>
                        <p style="margin:0;color:var(--muted);line-height:1.8;">Get in touch for a guided demo, implementation discussion, or deployment support.</p>
                        <div class="contact-row">
                            <svg viewBox="0 0 24 24"><path d="M22 16.92V19a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h2.09a2 2 0 0 1 2 1.72l.36 2.71a2 2 0 0 1-.57 1.72l-1.52 1.52a16 16 0 0 0 6 6l1.52-1.52a2 2 0 0 1 1.72-.57l2.71.36A2 2 0 0 1 22 16.92Z"/></svg>
                            <div><strong>Call</strong>+91 99446 26300</div>
                        </div>
                        <div class="contact-row">
                            <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg>
                            <div><strong>Email</strong>hello@etaxadv.com</div>
                        </div>
                        <div class="contact-row">
                            <svg viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            <div><strong>Office</strong>E Tax Advisors Private Limited</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-shell">
            <div class="footer-nav">
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a href="#use-cases">Use Cases</a>
                <a href="#security">Security</a>
                <a href="#contact">Contact</a>
                <a href="#contact">Privacy Policy</a>
                <a href="#contact">Terms of Use</a>
            </div>
            <div>© E Tax Advisors Private Limited</div>
        </div>
    </footer>
</div>
<?php if (!empty($success)): ?>
<script>
    (function () {
        const toast = document.getElementById('logout-toast');
        if (!toast) {
            return;
        }

        requestAnimationFrame(() => toast.classList.add('is-visible'));
        window.setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 3200);
    }());
</script>
<?php endif; ?>
</body>
</html>
