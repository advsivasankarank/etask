<?php $settings = $settings ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Notification Settings</h3><div class="subtle">Email, SMS, and portal notification configuration.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/settings/notifications')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Email Notifications</span><select name="email_enabled"><option value="1" <?= ($settings['email_enabled'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($settings['email_enabled'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option></select></label>
            <label style="display:grid;gap:8px;"><span>SMS Notifications</span><select name="sms_enabled"><option value="1" <?= ($settings['sms_enabled'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($settings['sms_enabled'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option></select></label>
            <label style="display:grid;gap:8px;"><span>Portal Notifications</span><select name="portal_enabled"><option value="1" <?= ($settings['portal_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option><option value="0" <?= ($settings['portal_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option></select></label>
        </div>
        <div style="display:flex;gap:12px;"><button type="submit" class="button">Update Notifications</button><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>
