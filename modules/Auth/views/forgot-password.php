<?php $audience = ($audience ?? 'portal') === 'portal' ? 'portal' : 'portal'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | e-Pani</title>
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
        .pw-container {
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
            min-height: 36px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 0.84rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            font-family: inherit;
            transition: transform .15s ease;
        }
        .pub-btn:hover { transform: translateY(-1px); }
        .pub-btn-ghost {
            color: var(--text);
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        /* Main */
        .pw-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        /* Card */
        .pw-card {
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
        .pw-left {
            padding: 44px;
            background: linear-gradient(160deg, #102d24 0%, #17624d 60%, #16352e 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .pw-left .eyebrow {
            color: #ffd5c3;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .pw-left h1 {
            margin: 12px 0 16px;
            font-size: clamp(1.8rem, 3.5vw, 2.2rem);
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.04em;
        }
        .pw-left .copy-text {
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 0.92rem;
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
            font-size: 0.88rem;
            font-weight: 500;
        }
        .feature-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            flex: 0 0 auto;
        }

        .pw-right {
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .pw-right .eyebrow {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.72rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .pw-right h2 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .pw-right .hint {
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
        input:focus {
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
            margin-bottom: 16px;
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

        /* Trust */
        .trust-strip {
            max-width: 960px;
            margin: 24px auto 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 500;
        }
        .trust-sep { color: var(--line); margin: 0 4px; }

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
        .pub-footer-brand strong { color: var(--text); font-weight: 700; }
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
            .pub-header-inner { flex-wrap: wrap; justify-content: center; gap: 10px; padding: 12px 0; }
            .pub-brand { width: 100%; justify-content: center; }
            .pub-nav { width: 100%; justify-content: center; flex-wrap: wrap; gap: 6px 14px; }
            .pub-actions { width: 100%; justify-content: center; flex-wrap: wrap; gap: 6px; }
        }
        @media (max-width: 860px) {
            .pw-card { grid-template-columns: 1fr; }
            .pw-left { padding: 28px; }
            .pw-right { padding: 28px; }
        }
        @media (max-width: 768px) {
            .pw-container { width: min(1400px, calc(100vw - 28px)); }
            .pw-main { padding: 24px 0; }
            .pw-left { padding: 24px; }
            .pw-left h1 { font-size: 1.6rem; }
            .pw-right { padding: 24px; }
            .pw-right h2 { font-size: 1.3rem; }
            .pub-footer-inner { flex-direction: column; align-items: center; text-align: center; }
            .pub-footer-links { justify-content: center; }
        }
        @media (max-width: 576px) {
            .pw-container { width: min(1400px, calc(100vw - 22px)); }
            .pub-brand-text { font-size: 1.2rem; }
            .pub-nav { gap: 4px 10px; }
            .pub-nav a { font-size: 0.78rem; }
            .pub-btn { min-height: 32px; padding: 0 10px; font-size: 0.78rem; }
            .pw-left { padding: 20px; }
            .pw-left h1 { font-size: 1.4rem; }
            .pw-right { padding: 20px; }
            .feature-item { font-size: 0.82rem; padding: 10px 12px; }
        }
        @media (max-width: 430px) {
            .pub-header-inner { padding: 8px 0; }
            .pub-brand-text { font-size: 1.1rem; }
            .pub-nav { gap: 3px 8px; }
            .pub-nav a { font-size: 0.72rem; }
            .pub-actions { flex-direction: column; align-items: stretch; gap: 4px; }
            .pub-btn { width: 100%; justify-content: center; min-height: 34px; }
            .pw-left { padding: 18px; }
            .pw-left h1 { font-size: 1.25rem; }
            .pw-right { padding: 18px; }
            .pw-right h2 { font-size: 1.15rem; }
        }
    </style>
</head>
<body>

<header class="pub-header">
    <div class="pw-container pub-header-inner">
        <a href="<?= e(url('/')) ?>" class="pub-brand">
            <div class="pub-brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 8.5 12 4l8 4.5v7c0 3.7-2.5 6.97-6 7.9-3.5-.93-6-4.2-6-7.9v-7Z"/><path d="M9.5 12.5 11.5 14.5 15.5 10.5"/></svg>
            </div>
            <div class="pub-brand-text"><span class="e">e-</span><span class="pani">Pani</span></div>
        </a>
        <nav class="pub-nav">
            <a href="<?= e(url('/')) ?>#features">Features</a>
            <a href="<?= e(url('/')) ?>#security">Security</a>
            <a href="<?= e(url('/')) ?>#contact">Contact</a>
        </nav>
        <div class="pub-actions">
            <a href="<?= e(url('/login?audience=internal')) ?>" class="pub-btn pub-btn-ghost">Staff Login</a>
            <a href="<?= e(url('/login?audience=portal')) ?>" class="pub-btn pub-btn-ghost">Client Login</a>
        </div>
    </div>
</header>

<main class="pw-main">
    <div class="pw-container">
        <div class="pw-card">
            <section class="pw-left">
                <div class="eyebrow">e-Pani Password Recovery</div>
                <h1>Recover your client portal access</h1>
                <p class="copy-text">Enter your PAN, TAN, or Aadhaar-based portal username along with the registered email or mobile number. Once verified, you can set a new password immediately.</p>
                <div class="feature-list">
                    <div class="feature-item"><span class="feature-dot"></span> Secure password recovery</div>
                    <div class="feature-item"><span class="feature-dot"></span> Protected client documents</div>
                    <div class="feature-item"><span class="feature-dot"></span> Portal access recovery</div>
                    <div class="feature-item"><span class="feature-dot"></span> Audit-ready account activity</div>
                </div>
            </section>

            <section class="pw-right">
                <div class="eyebrow">PASSWORD RECOVERY</div>
                <h2>Forgot your password?</h2>
                <p class="hint">Verify your details to continue to a secure password reset.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('/forgot-password')) ?>" novalidate>
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="audience" value="<?= e($audience) ?>">
                    <label>
                        Portal Username
                        <input type="text" name="username" value="<?= e($old_username ?? '') ?>" autocomplete="username" required>
                    </label>
                    <label>
                        Registered Email or Mobile
                        <input type="text" name="verification" value="<?= e($old_verification ?? '') ?>" autocomplete="email" required>
                    </label>
                    <button type="submit" class="form-submit">Verify and Continue</button>
                </form>

                <div class="form-links">
                    <div class="form-link">Remembered password? <a href="<?= e(url('/login?audience=portal')) ?>">Client Login</a></div>
                    <div class="form-link"><a href="<?= e(url('/')) ?>">Back to Home</a></div>
                </div>
            </section>
        </div>

        <div class="trust-strip">
            <span>Secure password recovery</span>
            <span class="trust-sep">&middot;</span>
            <span>Protected client records</span>
            <span class="trust-sep">&middot;</span>
            <span>Verified portal access</span>
            <span class="trust-sep">&middot;</span>
            <span>Audit-ready records</span>
        </div>
    </div>
</main>

<footer class="pub-footer">
    <div class="pw-container">
        <div class="pub-footer-inner">
            <div class="pub-footer-brand">
                <strong>e-Pani</strong> &mdash; Office Automation &amp; Management Suite<br>
                Built for tax, legal and compliance professional offices.
            </div>
            <div class="pub-footer-links">
                <a href="<?= e(url('/')) ?>">Home</a>
                <a href="<?= e(url('/')) ?>#features">Features</a>
                <a href="<?= e(url('/')) ?>#security">Security</a>
                <a href="<?= e(url('/')) ?>#contact">Contact</a>
                <a href="<?= e(url('/login?audience=internal')) ?>">Staff Login</a>
                <a href="<?= e(url('/login?audience=portal')) ?>">Client Login</a>
                <a href="<?= e(url('/register-client')) ?>">Register Client</a>
            </div>
            <div class="pub-footer-copy">&copy; E Tax Advisors Private Limited</div>
        </div>
    </div>
</footer>

</body>
</html>
