<div class="auth-wrap">
    <section class="auth-copy">
        <div class="eyebrow">e-pani</div>
        <h1>Office management, compliance workflow, and client portal access.</h1>
        <p class="copy-text">
            Sign in to the e-pani workspace to manage service orders, workflow milestones, billing, reminders, attendance, consultant coordination, and client collaboration across companies.
        </p>
        <div class="badge-grid">
            <div class="badge">Teal-orange compliance operations workspace</div>
            <div class="badge">Role-segregated approvals, billing, and portal access</div>
            <div class="badge">Audit-friendly authentication with client and internal workflows</div>
        </div>
    </section>
    <section class="auth-form">
        <div class="eyebrow" style="margin-bottom:8px;">Portal Login</div>
        <h2 style="margin-top:0;">Login to e-pani</h2>
        <p class="hint" style="color:#5d6b82;">Enter your username and password to continue.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/login')) ?>" novalidate>
            <?= \App\Core\Csrf::inputField() ?>
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
    </section>
</div>
