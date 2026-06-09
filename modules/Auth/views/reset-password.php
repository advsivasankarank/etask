<div class="auth-wrap">
    <section class="auth-copy">
        <div class="eyebrow">e-pani</div>
        <h1>Set a new client portal password.</h1>
        <p class="copy-text">
            Choose a strong password for your portal account. Once saved, you can sign in again from the client login screen.
        </p>
        <div class="badge-grid">
            <div class="badge">Minimum 8 characters</div>
            <div class="badge">Include uppercase, lowercase, number, and special character</div>
            <div class="badge">Avoid reusing an old or shared office password</div>
        </div>
    </section>
    <section class="auth-form">
        <div class="eyebrow" style="margin-bottom:8px;">Client Password Reset</div>
        <h2 style="margin-top:0;">Reset password</h2>
        <p class="hint" style="color:#5d6b82;">Your verification is complete. Save the new password to reactivate access.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/reset-password')) ?>" novalidate>
            <?= \App\Core\Csrf::inputField() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
            <label>
                New Password
                <input type="password" name="new_password" autocomplete="new-password" required>
            </label>
            <label>
                Confirm New Password
                <input type="password" name="confirm_password" autocomplete="new-password" required>
            </label>
            <button type="submit" class="button">Update Password</button>
        </form>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <a href="<?= e(url('/login?audience=portal')) ?>" class="button button-secondary">Back to Client Login</a>
        </div>
    </section>
</div>
