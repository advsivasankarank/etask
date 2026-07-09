<?php $settings = $settings ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Workflow Settings</h3><div class="subtle">Global workflow options and policies.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/settings/workflow')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Reopen Requires Reason</span><select name="reopen_requires_reason"><option value="1" <?= ($settings['reopen_requires_reason'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($settings['reopen_requires_reason'] ?? '1') === '0' ? 'selected' : '' ?>>No</option></select></label>
            <label style="display:grid;gap:8px;"><span>Reminder Warnings Enabled</span><select name="reminder_warnings_enabled"><option value="1" <?= ($settings['reminder_warnings_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= ($settings['reminder_warnings_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>No</option></select></label>
        </div>
        <div style="display:flex;gap:12px;"><button type="submit" class="button">Update Settings</button><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>
