<?php $settings = $settings ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Security Settings</h3><div class="subtle">Password policy, session timeout, and audit configuration.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/settings/security')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Password Policy</span><input type="text" name="password_policy" value="<?= e($settings['password_policy'] ?? 'Minimum 8 characters') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Session Timeout (minutes)</span><input type="number" name="session_timeout" value="<?= e($settings['session_timeout'] ?? '120') ?>" min="15" max="480"></label>
            <label style="display:grid;gap:8px;"><span>Audit Logging</span><select name="audit_logging"><option value="1" <?= ($settings['audit_logging'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($settings['audit_logging'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option></select></label>
        </div>
        <div style="display:flex;gap:12px;"><button type="submit" class="button">Update Security</button><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>
