<div class="auth-wrap">
    <section class="auth-copy">
        <div class="eyebrow">Security Update</div>
        <h1>Change your password</h1>
        <p class="copy-text">
            Your account is protected with a first-login password policy. Set a new password to continue into the workspace.
        </p>
        <div class="badge-grid">
            <div class="badge">Minimum 8 characters</div>
            <div class="badge">Include uppercase, lowercase, number, and special character</div>
            <div class="badge">Choose a password different from the current one</div>
        </div>
    </section>

    <section class="auth-form">
        <div class="eyebrow" style="color:#0f766e;">Account Security</div>
        <h2 style="margin:12px 0 18px;font-size:1.8rem;">Password reset required</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom:14px;"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom:14px;"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/change-password')) ?>">
            <?= \App\Core\Csrf::inputField() ?>

            <label>
                <span>Current Password</span>
                <input type="password" name="current_password" autocomplete="current-password" required>
            </label>

            <label>
                <span>New Password</span>
                <input type="password" name="new_password" autocomplete="new-password" required>
            </label>

            <label>
                <span>Confirm New Password</span>
                <input type="password" name="confirm_password" autocomplete="new-password" required>
            </label>

            <button type="submit" class="button">Update Password</button>
        </form>
    </section>
</div>
