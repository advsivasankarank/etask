<?php $audience = ($audience ?? 'portal') === 'portal' ? 'portal' : 'portal'; ?>
<div class="auth-wrap">
    <section class="auth-copy">
        <div class="eyebrow">e-pani</div>
        <h1>Recover your client portal access.</h1>
        <p class="copy-text">
            Enter your PAN, TAN, or Aadhaar-based portal username along with the registered email address or mobile number. Once the details match, you can set a new password immediately.
        </p>
        <div class="badge-grid">
            <div class="badge">Username must match your PAN, TAN, or Aadhaar portal ID</div>
            <div class="badge">Use the email or mobile already registered for your client account</div>
            <div class="badge">Your new password must satisfy the current security policy</div>
        </div>
    </section>
    <section class="auth-form">
        <div class="eyebrow" style="margin-bottom:8px;">Client Password Recovery</div>
        <h2 style="margin-top:0;">Forgot password</h2>
        <p class="hint" style="color:#5d6b82;">Verify your details to continue to a secure password reset.</p>

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
            <button type="submit" class="button">Verify and Continue</button>
        </form>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <a href="<?= e(url('/login?audience=portal')) ?>" class="button button-secondary">Back to Client Login</a>
            <a href="<?= e(url('/register-client')) ?>" class="button button-secondary">Register Client</a>
        </div>
    </section>
</div>
