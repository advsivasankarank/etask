<?php $audience = ($audience ?? 'internal') === 'portal' ? 'portal' : 'internal'; ?>
<div class="auth-wrap">
    <section class="auth-copy">
        <div class="eyebrow">e-pani</div>
        <h1><?= $audience === 'portal' ? 'Client access for e-Pani.' : 'Staff access for e-Pani operations.' ?></h1>
        <p class="copy-text">
            <?= $audience === 'portal'
                ? 'Portal users can sign in using their PAN, TAN, or Aadhaar-based username. If you do not yet have portal access, register your client account first.'
                : 'Staff users can sign in to manage service orders, workflow milestones, billing, reminders, attendance, consultant coordination, and client collaboration across companies.' ?>
        </p>
        <div class="badge-grid">
            <?php if ($audience === 'portal'): ?>
                <div class="badge">Use PAN, TAN, or Aadhaar as your portal username</div>
                <div class="badge">Create client registration if access is not yet available</div>
                <div class="badge">Secure client collaboration and PSO tracking</div>
            <?php else: ?>
                <div class="badge">Role-segregated approvals, billing, and service execution</div>
                <div class="badge">Internal workspace for CRM, accounts, backend, and consultants</div>
                <div class="badge">Audit-friendly authentication with controlled operations access</div>
            <?php endif; ?>
        </div>
    </section>
    <section class="auth-form">
        <div class="eyebrow" style="margin-bottom:8px;"><?= $audience === 'portal' ? 'Client Login' : 'Staff Login' ?></div>
        <h2 style="margin-top:0;"><?= $audience === 'portal' ? 'Login to client portal' : 'Login to staff workspace' ?></h2>
        <p class="hint" style="color:#5d6b82;">Enter your username and password to continue.</p>

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
            <button type="submit" class="button">Sign In</button>
        </form>
        <?php if ($audience === 'portal'): ?>
            <div style="margin-top:12px;">
                <a href="<?= e(url('/forgot-password?audience=portal')) ?>" class="button button-secondary">Forgot Password</a>
            </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <a href="<?= e(url('/login?audience=internal')) ?>" class="button button-secondary">Staff Login</a>
            <a href="<?= e(url('/login?audience=portal')) ?>" class="button button-secondary">Client Login</a>
            <?php if ($audience === 'portal'): ?>
                <a href="<?= e(url('/register-client')) ?>" class="button button-secondary">Register Client</a>
            <?php endif; ?>
        </div>
    </section>
</div>
