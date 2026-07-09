<?php $audience = ($audience ?? 'internal') === 'portal' ? 'portal' : 'internal'; ?>

<header class="pub-header">
    <div class="login-container pub-header-inner">
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
            <a href="<?= e(url('/login?audience=internal')) ?>" class="pub-btn pub-btn-ghost <?= $audience === 'internal' ? 'active' : '' ?>">Staff Login</a>
            <a href="<?= e(url('/login?audience=portal')) ?>" class="pub-btn pub-btn-ghost <?= $audience === 'portal' ? 'active' : '' ?>">Client Login</a>
        </div>
    </div>
</header>

<main class="login-main">
    <div class="login-container">
        <div class="auth-card">
            <?php if ($audience === 'portal'): ?>
                <section class="auth-left">
                    <div class="eyebrow">e-Pani Client Portal</div>
                    <h1>Client Portal</h1>
                    <p class="copy-text">Upload documents, track service status, view bills and download final records securely.</p>
                    <div class="feature-list">
                        <div class="feature-item"><span class="feature-dot"></span> Document Upload</div>
                        <div class="feature-item"><span class="feature-dot"></span> Service Status Tracking</div>
                        <div class="feature-item"><span class="feature-dot"></span> Bills and Payments</div>
                        <div class="feature-item"><span class="feature-dot"></span> Generated Records and Support</div>
                    </div>
                </section>
            <?php else: ?>
                <section class="auth-left">
                    <div class="eyebrow">e-Pani Staff Workspace</div>
                    <h1>Staff Workspace</h1>
                    <p class="copy-text">Control daily work, service orders, staff monitor, billing, reminders and reports from one secure workspace.</p>
                    <div class="feature-list">
                        <div class="feature-item"><span class="feature-dot"></span> Work Register and Service Orders</div>
                        <div class="feature-item"><span class="feature-dot"></span> Staff Monitor and Daily Reports</div>
                        <div class="feature-item"><span class="feature-dot"></span> Billing, Collections and Reminders</div>
                        <div class="feature-item"><span class="feature-dot"></span> Review, Reports and Audit Trail</div>
                    </div>
                </section>
            <?php endif; ?>

            <section class="auth-right">
                <div class="eyebrow"><?= $audience === 'portal' ? 'CLIENT LOGIN' : 'STAFF LOGIN' ?></div>
                <h2><?= $audience === 'portal' ? 'Client Portal Login' : 'Staff Workspace Login' ?></h2>
                <p class="hint"><?= $audience === 'portal'
                    ? 'Sign in to view service status, upload documents and access your records.'
                    : 'Sign in to manage office work, staff activity, service orders and client deliverables.' ?></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('/login')) ?>" novalidate>
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="audience" value="<?= e($audience) ?>">
                    <label>
                        Username
                        <input type="text" name="username" value="<?= e($old_username ?? '') ?>" autocomplete="username" required>
                    </label>
                    <label>
                        Password
                        <input type="password" name="password" autocomplete="current-password" required>
                    </label>
                    <button type="submit" class="form-submit">Sign In</button>
                </form>

                <div class="form-links">
                    <?php if ($audience === 'portal'): ?>
                        <div class="form-link"><a href="<?= e(url('/forgot-password?audience=portal')) ?>">Forgot Password?</a></div>
                        <div class="form-link"><a href="<?= e(url('/register-client')) ?>">New client? Register Client</a></div>
                    <?php endif; ?>
                    <div class="form-link">
                        <?= $audience === 'portal'
                            ? 'Staff user? <a href="' . e(url('/login?audience=internal')) . '">Go to Staff Login</a>'
                            : 'Client user? <a href="' . e(url('/login?audience=portal')) . '">Go to Client Login</a>' ?>
                    </div>
                    <div class="form-link"><a href="<?= e(url('/')) ?>">Back to Home</a></div>
                </div>
            </section>
        </div>

        <?php if ($audience === 'portal'): ?>
            <div class="trust-strip">
                <span>Secure portal access</span>
                <span class="trust-sep">&middot;</span>
                <span>Protected documents</span>
                <span class="trust-sep">&middot;</span>
                <span>Service tracking</span>
                <span class="trust-sep">&middot;</span>
                <span>Audit-ready records</span>
            </div>
        <?php else: ?>
            <div class="trust-strip">
                <span>Secure login</span>
                <span class="trust-sep">&middot;</span>
                <span>Role-based access</span>
                <span class="trust-sep">&middot;</span>
                <span>Staff activity tracking</span>
                <span class="trust-sep">&middot;</span>
                <span>Audit-ready records</span>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="pub-footer">
    <div class="login-container">
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
