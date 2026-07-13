<?php
$userName = (\App\Core\Auth::user())['full_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | e-Pani</title>
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
        .cp-container {
            width: min(1400px, calc(100vw - 48px));
            margin: 0 auto;
        }

        /* Secure Header */
        .secure-header {
            background: rgba(255,255,255,0.96);
            border-bottom: 1px solid rgba(226,232,240,0.88);
            backdrop-filter: blur(8px);
        }
        .secure-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
        }
        .secure-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .secure-brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: var(--primary);
        }
        .secure-brand-icon svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .secure-brand-text {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.05em;
        }
        .secure-brand-text .e { color: var(--accent); }
        .secure-brand-text .pani { color: var(--primary-dark); }
        .secure-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.04em;
            padding: 4px 10px;
            border-radius: 6px;
            background: #F0FDF9;
            border: 1px solid rgba(15,118,110,0.12);
        }
        .secure-logout {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--muted);
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--surface);
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }
        .secure-logout:hover { background: #fef3f2; color: var(--danger); border-color: #fecdca; }

        /* Main */
        .cp-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        /* Card */
        .cp-card {
            width: 100%;
            max-width: 920px;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            background: var(--surface);
            border: 1px solid rgba(226,232,240,0.8);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(15,23,42,0.10);
        }
        .cp-left {
            padding: 40px;
            background: linear-gradient(160deg, #102d24 0%, #17624d 60%, #16352e 100%);
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .cp-left .eyebrow {
            color: #ffd5c3;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .cp-left h1 {
            margin: 12px 0 12px;
            font-size: clamp(1.6rem, 3vw, 2rem);
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.04em;
        }
        .cp-left .copy-text {
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 0.9rem;
        }
        .cp-left .welcome {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.07);
            font-size: 0.9rem;
        }
        .cp-left .welcome strong {
            color: #fff;
        }

        .policy-list {
            display: grid;
            gap: 8px;
            margin-top: 20px;
        }
        .policy-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.07);
            font-size: 0.84rem;
            font-weight: 500;
        }
        .policy-check {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(249,115,22,0.2);
            flex: 0 0 auto;
        }
        .policy-check svg {
            width: 12px;
            height: 12px;
            fill: none;
            stroke: var(--accent);
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .cp-right {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .cp-right .eyebrow {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.72rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .cp-right h2 {
            margin: 0 0 6px;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .cp-right .hint {
            margin: 0 0 20px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        form { display: grid; gap: 14px; }
        label {
            display: grid;
            gap: 5px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 0.92rem;
            font-family: inherit;
            background: var(--surface);
            color: var(--text);
            transition: border-color .15s, box-shadow .15s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.12);
        }
        .form-submit {
            width: 100%;
            padding: 12px 18px;
            border: 0;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .15s;
            margin-top: 4px;
        }
        .form-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .form-help {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.5;
            margin-top: 2px;
        }

        .alert {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 14px;
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
            max-width: 920px;
            margin: 20px auto 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 500;
        }
        .trust-sep { color: var(--line); margin: 0 4px; }

        /* Footer */
        .secure-footer {
            border-top: 1px solid var(--line);
            padding: 18px 0 24px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
        }

        /* Responsive */
        @media (max-width: 860px) {
            .cp-card { grid-template-columns: 1fr; }
            .cp-left { padding: 28px; }
            .cp-right { padding: 28px; }
        }
        @media (max-width: 768px) {
            .cp-container { width: min(1400px, calc(100vw - 28px)); }
            .cp-main { padding: 24px 0; }
            .cp-left { padding: 24px; }
            .cp-left h1 { font-size: 1.5rem; }
            .cp-right { padding: 24px; }
            .cp-right h2 { font-size: 1.25rem; }
        }
        @media (max-width: 576px) {
            .cp-container { width: min(1400px, calc(100vw - 22px)); }
            .secure-brand-text { font-size: 1.15rem; }
            .secure-label { display: none; }
            .cp-card { border-radius: 18px; }
            .cp-left { padding: 20px; }
            .cp-left h1 { font-size: 1.35rem; }
            .cp-right { padding: 20px; }
            .policy-item { font-size: 0.8rem; padding: 8px 10px; }
        }
        @media (max-width: 430px) {
            .cp-container { width: min(1400px, calc(100vw - 18px)); }
            .secure-header-inner { padding: 8px 0; }
            .secure-brand-text { font-size: 1.05rem; }
            .cp-left { padding: 18px; }
            .cp-left h1 { font-size: 1.2rem; }
            .cp-right { padding: 18px; }
            .cp-right h2 { font-size: 1.1rem; }
            .form-submit { width: 100%; }
        }
    </style>
</head>
<body>

<header class="secure-header">
    <div class="cp-container secure-header-inner">
        <div class="secure-brand">
            <div class="secure-brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 8.5 12 4l8 4.5v7c0 3.7-2.5 6.97-6 7.9-3.5-.93-6-4.2-6-7.9v-7Z"/><path d="M9.5 12.5 11.5 14.5 15.5 10.5"/></svg>
            </div>
            <div class="secure-brand-text"><span class="e">e-</span><span class="pani">Pani</span></div>
            <span class="secure-label">Account Security</span>
        </div>
        <form method="post" action="<?= e(url('/logout')) ?>" style="margin:0;">
            <?= \App\Core\Csrf::inputField() ?>
            <button type="submit" class="secure-logout">Logout</button>
        </form>
    </div>
</header>

<main class="cp-main">
    <div class="cp-container">
        <div class="cp-card">
            <section class="cp-left">
                <div class="eyebrow"><?= !empty($forcedChange) ? 'Security Update' : 'Account Security' ?></div>
                <h1><?= !empty($forcedChange) ? 'Secure Your Account' : 'Change Password' ?></h1>
                <p class="copy-text"><?= !empty($forcedChange) ? 'For your account protection, please create a new password before continuing to your workspace.' : 'Update your password whenever you need to strengthen or refresh your account security.' ?></p>

                <?php if ($userName !== ''): ?>
                    <div class="welcome">Welcome back, <strong><?= e($userName) ?></strong></div>
                <?php endif; ?>

                <div class="policy-list">
                    <div class="policy-item">
                        <span class="policy-check"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                        Minimum 8 characters
                    </div>
                    <div class="policy-item">
                        <span class="policy-check"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                        Include uppercase and lowercase letters
                    </div>
                    <div class="policy-item">
                        <span class="policy-check"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                        Include at least one number
                    </div>
                    <div class="policy-item">
                        <span class="policy-check"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                        Include at least one special character
                    </div>
                    <div class="policy-item">
                        <span class="policy-check"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                        Choose a password different from the current one
                    </div>
                </div>
            </section>

            <section class="cp-right">
                <div class="eyebrow"><?= !empty($forcedChange) ? 'PASSWORD RESET REQUIRED' : 'PASSWORD UPDATE' ?></div>
                <h2>Update Your Password</h2>
                <p class="hint"><?= !empty($forcedChange) ? 'Enter your current password and set a new secure password to continue.' : 'Enter your current password, then choose and confirm a new password.' ?></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('/change-password')) ?>">
                    <?= \App\Core\Csrf::inputField() ?>

                    <label>
                        Current Password
                        <input type="password" name="current_password" autocomplete="current-password" required>
                    </label>

                    <label>
                        New Password
                        <input type="password" name="new_password" autocomplete="new-password" required>
                        <span class="form-help">Must meet the password policy shown on the left</span>
                    </label>

                    <label>
                        Confirm New Password
                        <input type="password" name="confirm_password" autocomplete="new-password" required>
                    </label>

                    <button type="submit" class="form-submit">Update Password</button>
                </form>
            </section>
        </div>

        <div class="trust-strip">
            <span>Secure password update</span>
            <span class="trust-sep">&middot;</span>
            <span>Encrypted credentials</span>
            <span class="trust-sep">&middot;</span>
            <span>Audit-ready records</span>
        </div>
    </div>
</main>

<footer class="secure-footer">
    <div class="cp-container">&copy; E Tax Advisors Private Limited</div>
</footer>

</body>
</html>
