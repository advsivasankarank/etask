<?php
$old = is_array($old ?? null) ? $old : [];
$error = $error ?? null;
$value = static function (string $key, array $primary): string {
    return (string) ($primary[$key] ?? '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration | e-Pani</title>
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

        .reg-container {
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
        .pub-btn-ghost {
            color: var(--text);
            background: #F8FAFC;
            border-color: #E2E8F0;
        }

        /* Main */
        .reg-main {
            flex: 1;
            padding: 32px 0 40px;
        }

        /* Intro */
        .reg-intro {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 28px;
        }
        .reg-intro-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            background: #EFF6FF;
            color: var(--primary-dark);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .reg-intro h1 {
            margin: 0 0 10px;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }
        .reg-intro p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* Alert */
        .alert {
            max-width: 1100px;
            margin: 0 auto 20px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.9rem;
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

        /* Form Card */
        .reg-card {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid rgba(226,232,240,0.8);
            border-radius: 20px;
            box-shadow: 0 16px 48px rgba(15,23,42,0.06);
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* Sections */
        .reg-section {
            padding: 24px 0;
            border-bottom: 1px solid var(--line);
        }
        .reg-section:last-of-type {
            border-bottom: none;
        }
        .reg-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .reg-section-title::before {
            content: "";
            width: 4px;
            height: 18px;
            border-radius: 2px;
            background: var(--primary);
        }

        /* Grid */
        .reg-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        /* Fields */
        .reg-field {
            display: grid;
            gap: 6px;
        }
        .reg-field > span {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
        }
        .req { color: var(--accent); }
        .reg-field input,
        .reg-field select {
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
        .reg-field input:focus,
        .reg-field select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.12);
        }
        .reg-field input[type="file"] {
            padding: 10px 14px;
            border-style: dashed;
        }
        .reg-help {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.5;
        }
        .reg-field-full {
            grid-column: 1 / -1;
        }

        /* Actions */
        .reg-actions {
            padding: 24px 0 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }
        .reg-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 32px;
            border-radius: 12px;
            border: 0;
            background: var(--primary);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .15s;
        }
        .reg-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .reg-actions-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .reg-link {
            font-size: 0.85rem;
            color: var(--muted);
        }
        .reg-link a {
            color: var(--primary);
            font-weight: 600;
        }
        .reg-link a:hover { text-decoration: underline; }

        /* Trust */
        .trust-strip {
            max-width: 1100px;
            margin: 28px auto 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 500;
        }
        .trust-sep { color: var(--line); margin: 0 4px; }
        .trust-note {
            max-width: 1100px;
            margin: 8px auto 0;
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
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
        @media (max-width: 768px) {
            .reg-container { width: min(1400px, calc(100vw - 28px)); }
            .reg-main { padding: 24px 0 32px; }
            .reg-card { padding: 24px; }
            .reg-intro h1 { font-size: 1.6rem; }
            .reg-grid { grid-template-columns: 1fr; }
            .pub-footer-inner { flex-direction: column; align-items: center; text-align: center; }
            .pub-footer-links { justify-content: center; }
        }
        @media (max-width: 576px) {
            .reg-container { width: min(1400px, calc(100vw - 22px)); }
            .pub-brand-text { font-size: 1.2rem; }
            .pub-nav { gap: 4px 10px; }
            .pub-nav a { font-size: 0.78rem; }
            .pub-btn { min-height: 32px; padding: 0 10px; font-size: 0.78rem; }
            .reg-card { padding: 18px; border-radius: 16px; }
            .reg-section { padding: 18px 0; }
            .reg-intro h1 { font-size: 1.4rem; }
            .reg-intro p { font-size: 0.88rem; }
        }
        @media (max-width: 430px) {
            .pub-header-inner { padding: 8px 0; }
            .pub-brand-text { font-size: 1.1rem; }
            .pub-nav { gap: 3px 8px; }
            .pub-nav a { font-size: 0.72rem; }
            .pub-actions { flex-direction: column; align-items: stretch; gap: 4px; }
            .pub-btn { width: 100%; justify-content: center; min-height: 34px; }
            .reg-card { padding: 14px; }
            .reg-intro h1 { font-size: 1.25rem; }
            .reg-submit { width: 100%; }
        }
    </style>
</head>
<body>

<header class="pub-header">
    <div class="reg-container pub-header-inner">
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

<main class="reg-main">
    <div class="reg-container">

        <div class="reg-intro">
            <div class="reg-intro-badge">CLIENT ONBOARDING</div>
            <h1>Register for Client Portal Access</h1>
            <p>Create your client profile and portal login to upload documents, track services, view bills and access final records securely.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/register-client')) ?>" enctype="multipart/form-data" class="reg-card">
            <?= \App\Core\Csrf::inputField() ?>

            <div class="reg-section">
                <div class="reg-section-title">Identity</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>Client Type <span class="req">*</span></span>
                        <select name="client_type" required>
                            <?php foreach (['INDIVIDUAL','PROPRIETORSHIP','PARTNERSHIP','LLP','PRIVATE_LIMITED','PUBLIC_LIMITED','TRUST','SOCIETY','OTHER'] as $type): ?>
                                <option value="<?= e($type) ?>" <?= $value('client_type', $old) === $type ? 'selected' : '' ?>><?= e(ucwords(strtolower(str_replace('_', ' ', $type)))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="reg-field">
                        <span>Legal Name <span class="req">*</span></span>
                        <input type="text" name="legal_name" value="<?= e($value('legal_name', $old)) ?>" required>
                    </label>
                    <label class="reg-field">
                        <span>Trade Name</span>
                        <input type="text" name="trade_name" value="<?= e($value('trade_name', $old)) ?>">
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Tax Identifiers</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>PAN <span class="req">*</span></span>
                        <input type="text" name="pan" value="<?= e($value('pan', $old)) ?>" required style="text-transform:uppercase;">
                        <small class="reg-help">Enter PAN exactly as per records</small>
                    </label>
                    <label class="reg-field">
                        <span>GSTIN</span>
                        <input type="text" name="gstin" value="<?= e($value('gstin', $old)) ?>" style="text-transform:uppercase;">
                    </label>
                    <label class="reg-field">
                        <span>Aadhaar Number</span>
                        <input type="text" name="aadhaar_no" value="<?= e((string) ($old['aadhaar_no'] ?? '')) ?>" maxlength="12" placeholder="12-digit Aadhaar">
                        <small class="reg-help">Stored securely, only last 4 digits retained</small>
                    </label>
                    <label class="reg-field">
                        <span>TAN</span>
                        <input type="text" name="tan" value="<?= e($value('tan', $old)) ?>" style="text-transform:uppercase;">
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Contact Details</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>Email</span>
                        <input type="email" name="email" value="<?= e($value('email', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Mobile</span>
                        <input type="text" name="mobile" value="<?= e($value('mobile', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Alternate Mobile</span>
                        <input type="text" name="alternate_mobile" value="<?= e($value('alternate_mobile', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Landline</span>
                        <input type="text" name="landline" value="<?= e($value('landline', $old)) ?>">
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Primary Contact</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>Contact Name</span>
                        <input type="text" name="contact_name" value="<?= e($value('contact_name', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Designation</span>
                        <input type="text" name="designation" value="<?= e($value('designation', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Contact Email</span>
                        <input type="email" name="contact_email" value="<?= e($value('contact_email', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Contact Mobile</span>
                        <input type="text" name="contact_mobile" value="<?= e($value('contact_mobile', $old)) ?>">
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Portal Login</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>Username Basis <span class="req">*</span></span>
                        <select name="username_basis" required>
                            <?php foreach (['PAN', 'TAN', 'AADHAAR'] as $basis): ?>
                                <option value="<?= e($basis) ?>" <?= ($value('username_basis', $old) === '' && $basis === 'PAN') || $value('username_basis', $old) === $basis ? 'selected' : '' ?>><?= e($basis) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="reg-help">Your portal username will be derived from the selected identifier</small>
                    </label>
                    <label class="reg-field">
                        <span>Password <span class="req">*</span></span>
                        <input type="password" name="password" required>
                        <small class="reg-help">Minimum 8 characters with mixed case, number and special character</small>
                    </label>
                    <label class="reg-field">
                        <span>Confirm Password <span class="req">*</span></span>
                        <input type="password" name="confirm_password" required>
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Documents</div>
                <div class="reg-grid">
                    <label class="reg-field">
                        <span>PAN Image</span>
                        <input type="file" name="pan_document" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="reg-help">JPG, PNG or PDF</small>
                    </label>
                    <label class="reg-field">
                        <span>Aadhaar Image</span>
                        <input type="file" name="aadhaar_document" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="reg-help">JPG, PNG or PDF</small>
                    </label>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-section-title">Address</div>
                <div class="reg-grid">
                    <label class="reg-field reg-field-full">
                        <span>Address Line 1</span>
                        <input type="text" name="address_line1" value="<?= e($value('address_line1', $old)) ?>">
                    </label>
                    <label class="reg-field reg-field-full">
                        <span>Address Line 2</span>
                        <input type="text" name="address_line2" value="<?= e($value('address_line2', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>City</span>
                        <input type="text" name="city" value="<?= e($value('city', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>State</span>
                        <input type="text" name="state_name" value="<?= e($value('state_name', $old)) ?>">
                    </label>
                    <label class="reg-field">
                        <span>Postal Code</span>
                        <input type="text" name="postal_code" value="<?= e($value('postal_code', $old)) ?>">
                    </label>
                </div>
            </div>

            <div class="reg-actions">
                <button type="submit" class="reg-submit">Register Client</button>
                <div class="reg-actions-links">
                    <span class="reg-link">Already registered? <a href="<?= e(url('/login?audience=portal')) ?>">Client Login</a></span>
                    <span class="reg-link"><a href="<?= e(url('/')) ?>">Back to Home</a></span>
                </div>
            </div>
        </form>

        <div class="trust-strip">
            <span>Secure portal access</span>
            <span class="trust-sep">&middot;</span>
            <span>Protected documents</span>
            <span class="trust-sep">&middot;</span>
            <span>Service tracking</span>
            <span class="trust-sep">&middot;</span>
            <span>Audit-ready records</span>
        </div>
        <div class="trust-note">Your details are used only for client onboarding and service management.</div>
    </div>
</main>

<footer class="pub-footer">
    <div class="reg-container">
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
