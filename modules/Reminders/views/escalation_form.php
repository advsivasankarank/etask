<?php
$oldData = $old ?? [];
$data = $rule ?? [];
$value = static fn (string $key, mixed $default = '') => $oldData[$key] ?? $data[$key] ?? $default;
?>
<section class="panel">
    <div class="toolbar">
        <div><div class="eyebrow">Rule</div><h3 style="margin:0 0 6px;"><?= $rule === null ? 'Create Escalation Rule' : 'Edit Escalation Rule' ?></h3></div>
        <a href="<?= e(url('/reminders/escalations')) ?>" class="button button-secondary">Back</a>
    </div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fff3f2;color:#b42318;border:1px solid #f3c7c3;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/reminders/escalations/save')) ?>" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="id" value="<?= e((string) $value('id')) ?>">
        <label><span>Reminder Type</span><select name="reminder_type"><?php foreach (($options['reminder_types'] ?? []) as $type): ?><option value="<?= e($type) ?>" <?= (string) $value('reminder_type') === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
        <label><span>Day Offset</span><input type="number" name="day_offset" min="0" value="<?= e((string) $value('day_offset', 1)) ?>" required></label>
        <label><span>Target Type</span><select name="target_type"><?php foreach (($options['target_types'] ?? []) as $targetType): ?><option value="<?= e($targetType) ?>" <?= (string) $value('target_type', 'ASSIGNED_USER') === $targetType ? 'selected' : '' ?>><?= e($targetType) ?></option><?php endforeach; ?></select></label>
        <label><span>Target Role</span><select name="target_role_code"><option value="">Not Required</option><?php foreach (($options['roles'] ?? []) as $role): ?><option value="<?= e($role['code']) ?>" <?= (string) $value('target_role_code') === (string) $role['code'] ? 'selected' : '' ?>><?= e($role['label']) ?></option><?php endforeach; ?></select></label>
        <label><span>Channel</span><select name="channel"><?php foreach (($options['channels'] ?? []) as $channel): ?><option value="<?= e($channel) ?>" <?= (string) $value('channel', 'IN_APP') === $channel ? 'selected' : '' ?>><?= e($channel) ?></option><?php endforeach; ?></select></label>
        <label><span>Active</span><select name="is_active"><option value="1" <?= (string) $value('is_active', '1') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= (string) $value('is_active') === '0' ? 'selected' : '' ?>>No</option></select></label>
        <div style="display:flex;align-items:end;"><button type="submit" class="button">Save Rule</button></div>
    </form>
</section>
