<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Welcome') . ' | ' . config('app.name', 'Compliance Management System')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6fbfd;
            --surface: rgba(255, 255, 255, 0.90);
            --surface-strong: #ffffff;
            --text: #102a43;
            --muted: #5f7387;
            --primary: #1596a6;
            --primary-dark: #0f7180;
            --accent: #ff8a1f;
            --accent-soft: rgba(255, 138, 31, 0.12);
            --border: rgba(16, 95, 118, 0.12);
            --shadow: 0 22px 60px rgba(16, 95, 118, 0.14);
            --radius-xl: 32px;
            --radius-lg: 24px;
            --radius-md: 18px;
            --container: 1220px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at left top, rgba(21, 150, 166, 0.15), transparent 28%),
                radial-gradient(circle at right 18%, rgba(255, 138, 31, 0.12), transparent 20%),
                linear-gradient(180deg, #fafdff 0%, #f2fbfd 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        .page::before,
        .page::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .page::before {
            background:
                linear-gradient(115deg, rgba(255,255,255,0.84), rgba(255,255,255,0.52)),
                radial-gradient(circle at 24% 34%, rgba(255,255,255,0.40), transparent 12%),
                radial-gradient(circle at 78% 58%, rgba(255,255,255,0.34), transparent 14%);
            backdrop-filter: blur(2px);
        }

        .page::after {
            background-image:
                radial-gradient(circle at 10% 22%, rgba(177, 201, 213, 0.55) 0 1px, transparent 1px),
                linear-gradient(53deg, transparent 49.4%, rgba(150, 175, 188, 0.22) 49.6%, rgba(150, 175, 188, 0.22) 50.4%, transparent 50.6%),
                linear-gradient(-18deg, transparent 49.4%, rgba(150, 175, 188, 0.18) 49.6%, rgba(150, 175, 188, 0.18) 50.4%, transparent 50.6%);
            background-size: 220px 220px, 320px 320px, 280px 280px;
            opacity: 0.34;
        }

        .container {
            width: min(var(--container), calc(100vw - 32px));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .topbar {
            position: sticky;
            top: 14px;
            z-index: 10;
            padding-top: 14px;
        }

        .topbar-shell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 22px;
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.60);
            box-shadow: 0 18px 44px rgba(16, 95, 118, 0.12);
            backdrop-filter: blur(18px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(21, 150, 166, 0.16), rgba(255, 138, 31, 0.20));
            border: 1px solid rgba(16, 95, 118, 0.10);
            color: var(--primary-dark);
        }

        .brand-mark svg {
            width: 28px;
            height: 28px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .brand-copy {
            display: grid;
            gap: 2px;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .brand-title .e { color: var(--accent); }
        .brand-title .pani { color: #0f4c5c; }

        .brand-subtitle {
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 500;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 28px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav a {
            font-size: 0.95rem;
            font-weight: 700;
            color: #13293d;
            transition: color .18s ease;
        }

        .nav a:hover {
            color: var(--primary);
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 20px;
            border-radius: 14px;
            font-size: 0.96rem;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            border: 1px solid transparent;
        }

        .button:hover { transform: translateY(-1px); }

        .button-primary {
            background: linear-gradient(135deg, #2bd25d, #18b24a);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(24, 178, 74, 0.20);
        }

        .button-secondary {
            background: rgba(255, 255, 255, 0.86);
            color: var(--primary-dark);
            border-color: rgba(21, 150, 166, 0.18);
            box-shadow: 0 10px 22px rgba(16, 95, 118, 0.08);
        }

        .button-outline {
            background: transparent;
            color: var(--primary-dark);
            border-color: rgba(21, 150, 166, 0.22);
        }

        .hero {
            padding: 44px 0 24px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(340px, 0.92fr);
            gap: 38px;
            align-items: center;
        }

        .hero-copy {
            padding: 28px 0 18px;
        }

        .hero-kicker {
            color: var(--primary-dark);
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .hero-heading {
            margin: 0;
            font-size: clamp(3rem, 6vw, 5.2rem);
            line-height: 0.96;
            letter-spacing: -0.055em;
            color: #1b2b3f;
        }

        .hero-heading .accent {
            color: #ff62ac;
        }

        .hero-subheading {
            margin: 22px 0 0;
            font-size: clamp(1.35rem, 2vw, 2.2rem);
            line-height: 1.22;
            font-weight: 700;
            color: #101828;
            max-width: 860px;
        }

        .hero-description {
            margin: 18px 0 0;
            font-size: 1.02rem;
            line-height: 1.8;
            color: var(--muted);
            max-width: 720px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .hero-proof {
            display: flex;
            gap: 26px;
            flex-wrap: wrap;
            margin-top: 30px;
            color: #344054;
        }

        .proof-block strong {
            display: block;
            font-size: 1.4rem;
            color: #101828;
            margin-bottom: 4px;
        }

        .proof-block span {
            font-size: 0.92rem;
            color: var(--muted);
        }

        .hero-showcase {
            position: relative;
        }

        .device-shell {
            position: relative;
            padding: 18px;
            border-radius: 30px;
            background: rgba(255,255,255,0.62);
            border: 1px solid rgba(255,255,255,0.68);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .device-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(244,250,252,0.92));
            border: 1px solid rgba(16, 95, 118, 0.12);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 44px rgba(16, 95, 118, 0.12);
        }

        .device-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: linear-gradient(135deg, rgba(21,150,166,0.08), rgba(255,138,31,0.08));
            border-bottom: 1px solid rgba(16, 95, 118, 0.08);
        }

        .device-dots {
            display: flex;
            gap: 6px;
        }

        .device-dots span {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: rgba(16, 95, 118, 0.20);
        }

        .device-status {
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .device-body {
            padding: 20px;
            display: grid;
            gap: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(245,251,253,0.96));
        }

        .device-hero {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 14px;
        }

        .mock-card {
            background: #ffffff;
            border: 1px solid rgba(16, 95, 118, 0.08);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 12px 28px rgba(16, 95, 118, 0.08);
        }

        .mock-card h4,
        .mock-card p {
            margin: 0;
        }

        .mock-card h4 {
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .mock-card p {
            font-size: 0.88rem;
            color: var(--muted);
        }

        .metric-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .metric-chip {
            padding: 12px;
            border-radius: 16px;
            background: linear-gradient(180deg, #f8fdff, #eef9fb);
            border: 1px solid rgba(16, 95, 118, 0.08);
        }

        .metric-chip strong {
            display: block;
            font-size: 1.12rem;
            margin-bottom: 4px;
        }

        .metric-chip span {
            color: var(--muted);
            font-size: 0.82rem;
        }

        .module-list {
            display: grid;
            gap: 10px;
        }

        .module-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(21,150,166,0.06);
            border: 1px solid rgba(21,150,166,0.08);
            font-size: 0.9rem;
        }

        .module-item strong {
            font-size: 0.94rem;
        }

        .module-item span {
            color: var(--muted);
            font-weight: 600;
        }

        .section {
            padding: 54px 0;
        }

        .section-heading {
            max-width: 820px;
            margin-bottom: 26px;
        }

        .section-label {
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: clamp(2rem, 3.2vw, 3.2rem);
            line-height: 1.06;
            letter-spacing: -0.04em;
        }

        .section-heading p {
            margin: 14px 0 0;
            color: var(--muted);
            line-height: 1.8;
            font-size: 1rem;
        }

        .problems-grid,
        .feature-grid,
        .pricing-grid,
        .security-grid {
            display: grid;
            gap: 18px;
        }

        .problems-grid,
        .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .security-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pricing-grid {
            grid-template-columns: 1.05fr 0.95fr;
            align-items: stretch;
        }

        .card {
            position: relative;
            padding: 24px;
            border-radius: 24px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(255,255,255,0.56);
            box-shadow: 0 18px 42px rgba(16, 95, 118, 0.10);
            backdrop-filter: blur(14px);
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 1.18rem;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
            font-size: 0.96rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            background: linear-gradient(135deg, rgba(21,150,166,0.14), rgba(255,138,31,0.18));
            color: var(--primary-dark);
        }

        .card-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feature-list {
            display: grid;
            gap: 14px;
            margin-top: 18px;
        }

        .feature-line {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1f2937;
            font-weight: 600;
        }

        .feature-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            flex: 0 0 auto;
        }

        .price-card {
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(160deg, rgba(16,42,67,0.96), rgba(15,113,128,0.96));
            color: #f7fbfc;
            box-shadow: 0 26px 54px rgba(16, 42, 67, 0.22);
        }

        .price-card .section-label {
            color: rgba(255,255,255,0.70);
        }

        .price {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin: 18px 0 12px;
        }

        .price strong {
            font-size: 3.2rem;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .price span {
            color: rgba(255,255,255,0.72);
            font-weight: 600;
        }

        .contact-card {
            display: grid;
            gap: 18px;
            align-content: start;
        }

        .contact-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: var(--muted);
            font-size: 0.96rem;
            line-height: 1.6;
        }

        .contact-row strong {
            color: #101828;
            display: block;
            margin-bottom: 2px;
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

        .flash {
            margin: 22px 0 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(236, 253, 243, 0.96);
            border: 1px solid rgba(171, 239, 198, 0.98);
            color: #047857;
            box-shadow: 0 12px 24px rgba(4, 120, 87, 0.08);
        }

        .footer {
            padding: 26px 0 36px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .footer-shell {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding-top: 22px;
            border-top: 1px solid rgba(16, 95, 118, 0.08);
        }

        @media (max-width: 1100px) {
            .hero-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .feature-grid,
            .problems-grid,
            .security-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .topbar-shell {
                flex-direction: column;
                align-items: stretch;
            }

            .nav,
            .actions {
                justify-content: flex-start;
            }

            .hero {
                padding-top: 30px;
            }

            .hero-heading {
                font-size: clamp(2.5rem, 9vw, 4rem);
            }

            .hero-subheading {
                font-size: 1.45rem;
            }
        }

        @media (max-width: 720px) {
            .feature-grid,
            .problems-grid,
            .security-grid,
            .metric-row,
            .device-hero {
                grid-template-columns: 1fr;
            }

            .container {
                width: min(var(--container), calc(100vw - 22px));
            }

            .topbar-shell,
            .card,
            .price-card {
                padding: 20px;
            }

            .hero-proof {
                gap: 16px;
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
                <a href="#problems">Practice Challenges</a>
                <a href="#why-epani">Why e-Pani</a>
                <a href="#features">Features</a>
                <a href="#price">Price</a>
                <a href="#security">Security</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="actions">
                <a class="button button-primary" href="#contact">Request a Demo</a>
                <a class="button button-secondary" href="<?= e(url('/login')) ?>">Login</a>
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
                    <div class="hero-copy">
                        <div class="hero-kicker">e-Pani comes from the Tamil word “பணி” (Pani) — work, duty, assignment, or task.</div>
                        <h1 class="hero-heading">The professional <span class="accent">practice management</span> platform for compliance-driven firms.</h1>
                        <p class="hero-subheading">Built for Tax Practitioners, Chartered Accountants, GST Consultants, Advocates, and Compliance Teams that need structured execution, client visibility, billing discipline, and audit-ready control.</p>
                        <p class="hero-description">e-Pani transforms everyday professional work into a governed digital operating model — from client onboarding and assignment tracking to filing milestones, portal coordination, consultant management, billing, document control, and closure.</p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#contact">Request a Demo</a>
                            <a class="button button-secondary" href="<?= e(url('/login')) ?>">Client / Staff Login</a>
                            <a class="button button-outline" href="#features">Explore Platform</a>
                        </div>
                        <div class="hero-proof">
                            <div class="proof-block">
                                <strong>Practice-ready</strong>
                                <span>Designed for tax, GST, litigation, and compliance offices</span>
                            </div>
                            <div class="proof-block">
                                <strong>Control-led</strong>
                                <span>Approvals, reminders, billing, and records in one system</span>
                            </div>
                            <div class="proof-block">
                                <strong>Commercially deployable</strong>
                                <span>Built for teams, clients, consultants, and management</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-showcase">
                        <div class="device-shell">
                            <div class="device-card">
                                <div class="device-topbar">
                                    <div class="device-dots"><span></span><span></span><span></span></div>
                                    <div class="device-status">Unified practice command centre</div>
                                </div>
                                <div class="device-body">
                                    <div class="device-hero">
                                        <div class="mock-card">
                                            <h4>Today's practice overview</h4>
                                            <p>Monitor pending filings, partner approvals, consultant deliverables, billing queues, and client responses from one accountable workspace.</p>
                                            <div class="metric-row">
                                                <div class="metric-chip"><strong>128</strong><span>Active assignments</span></div>
                                                <div class="metric-chip"><strong>42</strong><span>Client requests</span></div>
                                                <div class="metric-chip"><strong>19</strong><span>Collections pending</span></div>
                                            </div>
                                        </div>
                                        <div class="mock-card">
                                            <h4>Critical follow-up signals</h4>
                                            <div class="module-list">
                                                <div class="module-item"><strong>ITR e-Verification</strong><span>Escalation due</span></div>
                                                <div class="module-item"><strong>Consultant Settlement</strong><span>Accounts action pending</span></div>
                                                <div class="module-item"><strong>Client Clarification</strong><span>3 open responses</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mock-card">
                                        <h4 style="margin-bottom:12px;">Platform coverage</h4>
                                        <div class="module-list">
                                            <div class="module-item"><strong>Client Master + Portal Access</strong><span>PAN-led client control</span></div>
                                            <div class="module-item"><strong>Assignment Workflow + Compliance Stages</strong><span>ITR, GST, TDS, advisory execution</span></div>
                                            <div class="module-item"><strong>Billing + Receipts + Disbursements</strong><span>Commercial and collection discipline</span></div>
                                            <div class="module-item"><strong>Documents + Search + Reminders</strong><span>Secure records and operational continuity</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="problems">
            <div class="container">
                <div class="section-heading">
                    <div class="section-label">Practice Challenges</div>
                    <h2>Professional firms do high-value work, but often manage it through fragmented follow-up, spreadsheets, and personal memory.</h2>
                    <p>That creates service slippage, billing delays, partner blind spots, weak client communication, and operational stress that grows with every new client and filing cycle.</p>
                </div>
                <div class="problems-grid">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 8v5l3 3"/><circle cx="12" cy="12" r="9"/></svg></div>
                        <h3>Missed follow-up and due dates</h3>
                        <p>ITR, GST, TDS, appeal, consultant, and payment-linked milestones slip when the next accountable step is not system-driven.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h10M4 17h8"/></svg></div>
                        <h3>No single source of practice status</h3>
                        <p>Partners, CRM, backend, accounts, consultants, and clients often see different versions of what is pending, billed, filed, or closed.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 19V9h10v10"/><path d="M9 9V5h6v4"/></svg></div>
                        <h3>Weak control and traceability</h3>
                        <p>Documents, approvals, billing actions, consultant movement, and follow-up evidence become difficult to produce when management or clients ask for proof.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="why-epani">
            <div class="container">
                <div class="section-heading">
                    <div class="section-label">Why e-Pani</div>
                    <h2>“Pani” means work, duty, assignment, or task. e-Pani gives professional work a disciplined digital operating model.</h2>
                    <p>The name reflects the platform’s purpose: every assignment should move through the office with clarity, ownership, accountability, and commercial visibility.</p>
                </div>
                <div class="security-grid">
                    <div class="card">
                        <h3>From work to governed practice process</h3>
                        <p>e-Pani does not just record activity. It organizes professional assignments into stages, approvals, reminders, billing checkpoints, and closure logic.</p>
                    </div>
                    <div class="card">
                        <h3>Made for real compliance offices</h3>
                        <p>The platform reflects how tax practitioners, Chartered Accountants, GST consultants, advocates, and compliance teams actually coordinate client work.</p>
                    </div>
                    <div class="card">
                        <h3>Trust-building by design</h3>
                        <p>Clients see status, teams know responsibilities, and leadership gets operational visibility without depending on fragmented verbal updates.</p>
                    </div>
                    <div class="card">
                        <h3>Commercial deployment credibility</h3>
                        <p>With role control, secure documents, workflow records, reminders, and billing visibility, e-Pani is positioned as a deployable SaaS platform, not a basic office utility.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-heading">
                    <div class="section-label">Features</div>
                    <h2>One professional platform for client intake, assignment execution, billing discipline, and practice control.</h2>
                    <p>e-Pani connects the front office, execution team, consultants, accounts, and clients into one role-aware practice-management environment.</p>
                </div>
                <div class="feature-grid">
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div>
                        <h3>Client master with practice intelligence</h3>
                        <p>Maintain PAN-led client records with GST, TAN, Aadhaar, CRM ownership, portal access, and a linked history of assignments and billing.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v6h6"/></svg></div>
                        <h3>Assignment and service-order governance</h3>
                        <p>Create structured service orders with milestone-led execution for documents, preparation, review, filing, acknowledgement, e-verification, and closure.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M3 7h18"/><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15h4"/></svg></div>
                        <h3>Billing and commercial follow-through</h3>
                        <p>Issue invoices, account for advances, generate receipts, manage disbursements, and track collection status without losing assignment context.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Z"/><path d="M18 16V11a6 6 0 1 0-12 0v5l-2 2h16l-2-2Z"/></svg></div>
                        <h3>Reminder-led execution and escalation</h3>
                        <p>Use dashboard and email reminders for pending documents, client clarifications, invoice dues, consultant deliverables, and compliance deadlines.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 2 4 6v6c0 5.3 3.4 10 8 11 4.6-1 8-5.7 8-11V6l-8-4Z"/><path d="M9 12.5 11 14.5l4-4"/></svg></div>
                        <h3>Secure records and audit confidence</h3>
                        <p>Protect sensitive client records through controlled downloads, document versioning, and access logs that strengthen internal discipline and external confidence.</p>
                    </div>
                    <div class="card">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6"/><path d="m20 20-4.35-4.35"/></svg></div>
                        <h3>Search, reporting, and leadership visibility</h3>
                        <p>Find work instantly through PAN, TAN, client, service order, invoice, consultant, or document references and convert activity into meaningful management reports.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-heading">
                    <div class="section-label">Value Proposition</div>
                    <h2>e-Pani helps firms operate with enterprise-grade discipline while preserving the flexibility of professional practice.</h2>
                    <p>It is not just software for storing records. It is a commercial and operational control layer for firms that want predictable execution, stronger client trust, and scalable internal coordination.</p>
                </div>
                <div class="security-grid">
                    <div class="card">
                        <h3>Better client confidence</h3>
                        <p>Clients can see status, receive timely communication, and experience a more organized professional office.</p>
                    </div>
                    <div class="card">
                        <h3>Better internal accountability</h3>
                        <p>Teams know what is pending, who owns the next action, and what requires approval or follow-up.</p>
                    </div>
                    <div class="card">
                        <h3>Better commercial control</h3>
                        <p>Billing, receipts, advances, disbursements, and collections stay linked to actual assignment progress.</p>
                    </div>
                    <div class="card">
                        <h3>Better management visibility</h3>
                        <p>Leadership gains a reliable view of execution, risk, collections, consultant exposure, and pending deadlines.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="price">
            <div class="container">
                <div class="pricing-grid">
                    <div class="price-card">
                        <div class="section-label">Price</div>
                        <h2 style="margin:0;font-size:2.5rem;line-height:1.04;letter-spacing:-0.04em;">Priced for serious professional deployment, tailored to the scale of your practice.</h2>
                        <div class="price">
                            <strong>Custom</strong>
                            <span>deployment plans</span>
                        </div>
                        <p style="margin:0;color:rgba(255,255,255,0.78);line-height:1.8;">Deployment can be aligned to your office size, service mix, user roles, client portal needs, workflow depth, reminder automation, and hosting requirements.</p>
                        <div class="feature-list">
                            <div class="feature-line"><span class="feature-dot"></span><span>Role-based practice control and approvals</span></div>
                            <div class="feature-line"><span class="feature-dot"></span><span>Client, assignment, billing, and closure stack</span></div>
                            <div class="feature-line"><span class="feature-dot"></span><span>Secure documents, search, reminders, and reports</span></div>
                        </div>
                    </div>
                    <div class="card contact-card" id="contact">
                        <div class="section-label">Contact</div>
                        <h2 style="margin:0;font-size:2.35rem;line-height:1.06;letter-spacing:-0.04em;">See how e-Pani can organize your practice operations end to end.</h2>
                        <p style="margin:0;color:var(--muted);line-height:1.8;">Request a guided walkthrough to understand how e-Pani can support client onboarding, assignment execution, billing control, reminders, consultant management, and reporting in your firm.</p>
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
                        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                            <a class="button button-primary" href="mailto:hello@etaxadv.com?subject=e-Pani%20Demo%20Request">Request Demo</a>
                            <a class="button button-secondary" href="<?= e(url('/login')) ?>">Open Platform Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="security">
            <div class="container">
                <div class="section-heading">
                    <div class="section-label">Security</div>
                    <h2>Professional credibility is strengthened by secure access, traceable records, and controlled operational exposure.</h2>
                    <p>For client-facing professional firms, trust depends not only on execution quality but also on how securely and transparently work is handled.</p>
                </div>
                <div class="security-grid">
                    <div class="card">
                        <h3>Role-based responsibility separation</h3>
                        <p>Internal users, CRM teams, accounts, consultants, and portal clients operate through controlled permissions aligned with real office responsibilities.</p>
                    </div>
                    <div class="card">
                        <h3>Protected client document access</h3>
                        <p>Documents are stored outside the public web root and delivered only through authenticated, logged, permission-aware endpoints.</p>
                    </div>
                    <div class="card">
                        <h3>Traceable office actions</h3>
                        <p>Follow-up logs, billing movement, reminders, downloads, and service-order progression can be reviewed whenever proof or internal clarity is required.</p>
                    </div>
                    <div class="card">
                        <h3>Commercial deployment readiness</h3>
                        <p>Environment validation, centralized error handling, secure upload rules, locked closures, and archival-friendly retention support dependable long-term deployment.</p>
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
