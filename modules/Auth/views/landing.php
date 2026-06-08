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
            --surface-accent: #ECFDF5;
            --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 12px 32px rgba(15, 23, 42, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
            --container: 1180px;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            color: var(--text);
            background:
                linear-gradient(180deg, #F8FBFC 0%, #FFFFFF 22%, #FFFFFF 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            min-height: 100vh;
        }

        .container {
            width: min(var(--container), calc(100vw - 32px));
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
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 24px;
            min-height: 82px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
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

        .brand-mark svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .brand-copy {
            display: grid;
            gap: 3px;
        }

        .brand-title {
            font-size: 1.95rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .brand-title .e { color: var(--accent); }
        .brand-title .pani { color: var(--primary-dark); }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 500;
        }

        .nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .nav a {
            font-size: 0.94rem;
            font-weight: 600;
            color: #334155;
        }

        .nav a:hover {
            color: var(--primary);
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-size: 0.95rem;
            font-weight: 700;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }

        .button:hover {
            transform: translateY(-1px);
        }

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

        .hero {
            padding: 54px 0 34px;
            background:
                linear-gradient(180deg, #F8FBFC 0%, #FFFFFF 100%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(320px, 0.98fr);
            gap: 42px;
            align-items: center;
        }

        .eyebrow {
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

        .hero h1 {
            margin: 0;
            font-size: clamp(2.75rem, 5vw, 4.4rem);
            line-height: 1.02;
            letter-spacing: -0.06em;
            max-width: 760px;
        }

        .hero h1 .accent {
            color: var(--accent);
        }

        .hero-subtitle {
            margin: 20px 0 0;
            font-size: clamp(1.12rem, 1.8vw, 1.34rem);
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

        .hero-panel {
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            background: var(--surface);
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

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .metric-card {
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background: var(--surface-soft);
            padding: 14px;
        }

        .metric-card strong {
            display: block;
            font-size: 1.5rem;
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .metric-card span {
            color: var(--muted);
            font-size: 0.86rem;
        }

        .workspace-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
        }

        .workspace-card {
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background: #FFFFFF;
            padding: 16px;
        }

        .workspace-card h3 {
            margin: 0 0 12px;
            font-size: 1.02rem;
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
            background: var(--surface-soft);
            color: #1E293B;
            font-size: 0.92rem;
        }

        .list-item span {
            color: var(--muted);
            font-weight: 600;
        }

        .stats-strip {
            padding: 18px 0 30px;
        }

        .stats-shell {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card {
            padding: 24px 22px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: #FFFFFF;
            box-shadow: var(--shadow-sm);
        }

        .stat-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.05em;
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
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

        .section-head p {
            margin: 14px 0 0;
            font-size: 1rem;
            line-height: 1.8;
            color: var(--muted);
        }

        .label {
            margin-bottom: 10px;
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .grid-3,
        .grid-4,
        .grid-2 {
            display: grid;
            gap: 18px;
        }

        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .card {
            padding: 24px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: #FFFFFF;
            box-shadow: var(--shadow-sm);
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
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.75;
        }

        .workflow-shell {
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            background: #FFFFFF;
            box-shadow: var(--shadow-sm);
            padding: 24px;
        }

        .workflow-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            align-items: stretch;
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
            font-size: 0.98rem;
        }

        .workflow-step span {
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.65;
        }

        .usecase-card {
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
            color: rgba(255,255,255,0.72);
        }

        .cta-primary h2 {
            margin: 0;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.08;
            letter-spacing: -0.05em;
        }

        .cta-primary p {
            margin: 14px 0 0;
            color: rgba(255,255,255,0.82);
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

        .contact-card {
            display: grid;
            gap: 16px;
            align-content: start;
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
            fill: none;
            stroke: var(--primary);
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .contact-row strong {
            display: block;
            color: var(--text);
            margin-bottom: 2px;
        }

        .flash {
            margin: 0 0 24px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #BBF7D0;
            background: #F0FDF4;
            color: #166534;
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

        @media (max-width: 1120px) {
            .hero-grid,
            .cta-shell {
                grid-template-columns: 1fr;
            }

            .grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .grid-3,
            .workflow-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .workflow-step:not(:last-child)::after {
                display: none;
            }
        }

        @media (max-width: 920px) {
            .topbar-shell {
                grid-template-columns: 1fr;
                align-items: stretch;
                padding: 18px 0;
            }

            .nav,
            .actions {
                justify-content: flex-start;
            }

            .hero {
                padding-top: 32px;
            }
        }

        @media (max-width: 720px) {
            .container {
                width: min(var(--container), calc(100vw - 22px));
            }

            .grid-4,
            .grid-3,
            .grid-2,
            .stats-shell,
            .metrics-grid,
            .workspace-grid,
            .workflow-grid {
                grid-template-columns: 1fr;
            }

            .nav {
                gap: 14px 18px;
            }

            .hero h1 {
                font-size: clamp(2.2rem, 11vw, 3rem);
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .card,
            .workflow-shell,
            .cta-primary {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="page">
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
                <a href="#workflow">Workflow</a>
                <a href="#use-cases">Use Cases</a>
                <a href="#security">Security</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="actions">
                <a class="button button-primary" href="#contact">Request a Demo</a>
                <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Internal User</a>
                <a class="button button-ghost" href="<?= e(url('/login?audience=portal')) ?>">Portal User</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <?php if (!empty($success)): ?>
                    <div class="flash"><?= e($success) ?></div>
                <?php endif; ?>
                <div class="hero-grid">
                    <div>
                        <div class="eyebrow">All-in-one practice management</div>
                        <h1>Run your compliance practice <span class="accent">with clarity, control, and trust.</span></h1>
                        <p class="hero-subtitle">e-Pani is a professional SaaS platform for Tax Practitioners, Chartered Accountants, GST Consultants, Advocates, and Compliance Teams managing client work, assignments, billing, records, and follow-up.</p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#features">Explore Features</a>
                            <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Internal User Login</a>
                            <a class="button button-ghost" href="<?= e(url('/login?audience=portal')) ?>">Portal User Login</a>
                        </div>
                        <p class="hero-note">Derived from the Tamil word “பணி” (Pani), meaning work, duty, assignment, or task, e-Pani gives professional work a disciplined digital operating model.</p>
                    </div>
                    <div class="hero-panel">
                        <div class="hero-panel-head">
                            <div class="hero-panel-brand"><span class="e">e-</span><span class="pani">Pani</span></div>
                            <div class="hero-panel-tools">
                                <div class="panel-dot"></div>
                                <div class="panel-dot"></div>
                                <div class="panel-dot"></div>
                                <span>Live workspace</span>
                            </div>
                        </div>
                        <div class="hero-panel-body">
                            <div class="metrics-grid">
                                <div class="metric-card"><strong>1,248</strong><span>Managed clients</span></div>
                                <div class="metric-card"><strong>856</strong><span>Service orders</span></div>
                                <div class="metric-card"><strong>27</strong><span>Critical reminders</span></div>
                            </div>
                            <div class="workspace-grid">
                                <div class="workspace-card">
                                    <h3>Assignment control</h3>
                                    <div class="list">
                                        <div class="list-item">ITR e-Verification<span>Due today</span></div>
                                        <div class="list-item">GST filing review<span>12 pending</span></div>
                                        <div class="list-item">Consultant settlement<span>Accounts action</span></div>
                                    </div>
                                </div>
                            <div class="workspace-card">
                                <h3>Operational visibility</h3>
                                <div class="list">
                                    <div class="list-item">Invoices raised<span>321</span></div>
                                    <div class="list-item">Receipts posted<span>210</span></div>
                                    <div class="list-item">Client queries<span>18 open</span></div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                            <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Internal User Access</a>
                            <a class="button button-primary" href="<?= e(url('/login?audience=portal')) ?>">Portal User Access</a>
                            <a class="button button-ghost" href="<?= e(url('/register-client')) ?>">Register as Client</a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <section class="stats-strip">
            <div class="container">
                <div class="stats-shell">
                    <div class="stat-card">
                        <div class="stat-value">360°</div>
                        <div class="stat-label">Coverage across client master, assignment workflow, billing, reminders, and document control.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">Role-Based</div>
                        <div class="stat-label">Structured permissions for internal teams, consultants, accounts, and portal users.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">Audit-Ready</div>
                        <div class="stat-label">Traceable actions for downloads, workflow movement, reminders, and billing events.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">Multi-Entity</div>
                        <div class="stat-label">Built to support multi-company operations with controlled numbering and assignment flow.</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="why-epani">
            <div class="container">
                <div class="section-head">
                    <div class="label">Why e-Pani</div>
                    <h2>Professional work deserves a platform that treats every assignment as accountable, billable, and reviewable.</h2>
                    <p>e-Pani is designed to replace scattered operational habits with a structured practice-management model that supports service execution, client visibility, billing discipline, and long-term trust.</p>
                </div>
                <div class="grid-3">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg></div>
                        <h3>From assignment to closure</h3>
                        <p>Every client matter moves through a defined operational path instead of depending on memory, verbal updates, or fragmented notes.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 2 4 6v6c0 5.3 3.4 10 8 11 4.6-1 8-5.7 8-11V6l-8-4Z"/><path d="M9 12.5 11 14.5l4-4"/></svg></div>
                        <h3>Built for trust and control</h3>
                        <p>Client records, document access, follow-ups, workflow changes, and billing movement stay permissioned and traceable.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></div>
                        <h3>Operational clarity for leadership</h3>
                        <p>Partners and managers get a clean view of execution, risk, collections, consultant exposure, and pending actions across the office.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-head">
                    <div class="label">Platform Features</div>
                    <h2>One platform for client intake, assignment execution, billing discipline, and practice control.</h2>
                    <p>e-Pani brings together the operational, commercial, and governance layers of a modern compliance practice in one enterprise-style environment.</p>
                </div>
                <div class="grid-3">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z"/><path d="M4 21c0-3.3 3.6-6 8-6s8 2.7 8 6"/></svg></div>
                        <h3>Client master with practice intelligence</h3>
                        <p>Maintain PAN-led client records with GST, TAN, Aadhaar, CRM ownership, portal credentials, and linked assignment history.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v6h6"/></svg></div>
                        <h3>Service order and milestone governance</h3>
                        <p>Create structured service orders with workflow stages for documents, preparation, review, filing, acknowledgement, e-verification, and closure.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h4"/></svg></div>
                        <h3>Billing and collection control</h3>
                        <p>Handle invoices, advances, receipts, disbursements, and outstanding follow-up within the same assignment context.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Z"/><path d="M18 16V11a6 6 0 1 0-12 0v5l-2 2h16l-2-2Z"/></svg></div>
                        <h3>Reminder-led execution</h3>
                        <p>Drive action through dashboard and email reminders for pending documents, invoices, consultant deliverables, clarifications, and due dates.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 2 4 6v6c0 5.3 3.4 10 8 11 4.6-1 8-5.7 8-11V6l-8-4Z"/><path d="M9 12.5 11 14.5l4-4"/></svg></div>
                        <h3>Secure records and document controls</h3>
                        <p>Protect client records with controlled downloads, document versioning, access logging, and non-public storage architecture.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6"/><path d="m20 20-4.35-4.35"/></svg></div>
                        <h3>Search and management reporting</h3>
                        <p>Search by PAN, TAN, client, service order, invoice, consultant, or document and convert activity into meaningful operational reports.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="workflow">
            <div class="container">
                <div class="section-head">
                    <div class="label">Workflow Model</div>
                    <h2>A clear operating path from client request to governed completion.</h2>
                    <p>e-Pani gives every office a repeatable process framework that reduces ambiguity, improves ownership, and supports review at every stage.</p>
                </div>
                <div class="workflow-shell">
                    <div class="workflow-grid">
                        <div class="workflow-step">
                            <strong>1. Client Intake</strong>
                            <span>Capture client master, compliance profile, portal credentials, and request details.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>2. Assignment Creation</strong>
                            <span>Create PSO or service order with service type, entity, period, and responsible users.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>3. Execution Tracking</strong>
                            <span>Move through document collection, preparation, review, filing, acknowledgement, and follow-up stages.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>4. Billing & Controls</strong>
                            <span>Issue invoices, post receipts, capture disbursements, and manage consultant actions where applicable.</span>
                        </div>
                        <div class="workflow-step">
                            <strong>5. Closure & Audit</strong>
                            <span>Lock closure states, retain evidence, and preserve an auditable trail of service and commercial events.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="use-cases">
            <div class="container">
                <div class="section-head">
                    <div class="label">Industry Use Cases</div>
                    <h2>Purpose-built for the professionals who manage recurring compliance, client coordination, and controlled execution.</h2>
                    <p>Each use case benefits from the same platform discipline, while retaining the flexibility to support different service models and office structures.</p>
                </div>
                <div class="grid-4">
                    <div class="card usecase-card">
                        <h3>Chartered Accountants</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Client master and annual compliance tracking</div>
                            <div class="usecase-point">Service-order driven filing and review control</div>
                            <div class="usecase-point">Billing, receipts, and outstanding visibility</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Tax Practitioners</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">ITR, TDS, and response management in one workflow</div>
                            <div class="usecase-point">Reminder-led follow-up and e-verification visibility</div>
                            <div class="usecase-point">Secure document and acknowledgement handling</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>GST Consultants</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Monthly, quarterly, and annual compliance cycles</div>
                            <div class="usecase-point">Document pending, filing pending, and ARN capture stages</div>
                            <div class="usecase-point">Client coordination and billing continuity</div>
                        </div>
                    </div>
                    <div class="card usecase-card">
                        <h3>Advocates & Compliance Teams</h3>
                        <div class="usecase-list">
                            <div class="usecase-point">Assignment ownership with role-based review</div>
                            <div class="usecase-point">Consultant and internal team coordination</div>
                            <div class="usecase-point">Traceable records for evidence-led practice work</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="security">
            <div class="container">
                <div class="section-head">
                    <div class="label">Security & Governance</div>
                    <h2>Professional credibility is strengthened by secure access, traceable records, and controlled data exposure.</h2>
                    <p>For client-facing firms, trust depends not only on execution quality but also on how securely and transparently work is handled.</p>
                </div>
                <div class="grid-2">
                    <div class="card">
                        <h3>Role-based responsibility separation</h3>
                        <p>Internal teams, CRM, accounts, consultants, and portal users operate through controlled permissions aligned with real office responsibilities.</p>
                    </div>
                    <div class="card">
                        <h3>Protected client document access</h3>
                        <p>Documents are stored outside the public web root and delivered only through authenticated, logged, permission-aware endpoints.</p>
                    </div>
                    <div class="card">
                        <h3>Traceable office actions</h3>
                        <p>Workflow movement, follow-up logs, billing events, and downloads can be reviewed whenever proof or internal clarity is required.</p>
                    </div>
                    <div class="card">
                        <h3>Commercial deployment readiness</h3>
                        <p>Secure uploads, centralized error handling, locked closures, and retention-aware architecture support dependable long-term deployment.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section" id="contact">
            <div class="container">
                <div class="cta-shell">
                    <div class="cta-primary">
                        <div class="label">Request a Demo</div>
                        <h2>See how e-Pani can organize your practice operations end to end.</h2>
                        <p>Request a guided walkthrough to understand how e-Pani can support client onboarding, assignment execution, billing control, reminders, consultant coordination, and reporting in your firm.</p>
                        <div class="cta-actions">
                            <a class="button button-light" href="mailto:hello@etaxadv.com?subject=e-Pani%20Demo%20Request">Request Demo</a>
                            <a class="button button-secondary" href="<?= e(url('/login?audience=internal')) ?>">Internal Login</a>
                            <a class="button button-ghost" href="<?= e(url('/login?audience=portal')) ?>">Portal Login</a>
                        </div>
                    </div>
                    <div class="card contact-card">
                        <div class="label">Contact</div>
                        <h3 style="margin:0;font-size:1.5rem;line-height:1.25;">Talk to E Tax Advisors Private Limited</h3>
                        <p style="margin:0;color:var(--muted);line-height:1.8;">Get in touch for a guided demo, implementation discussion, or production deployment support.</p>
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
            <div>e-pani : Office Management Suite from E Tax Advisors Private Limited</div>
            <div>Practice management for tax, compliance, advisory, billing, follow-up, and controlled client operations.</div>
        </div>
    </footer>
</div>
</body>
</html>
