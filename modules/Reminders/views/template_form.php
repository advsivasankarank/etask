<?php
$oldData = $old ?? [];
$data = $template ?? [];
$value = static fn (string $key, mixed $default = '') => $oldData[$key] ?? $data[$key] ?? $default;
?>
<section class="panel">
    <div class="toolbar">
        <div><div class="eyebrow">Template</div><h3 style="margin:0 0 6px;"><?= $template === null ? 'Create Reminder Template' : 'Edit Reminder Template' ?></h3></div>
        <a href="<?= e(url('/reminders/templates')) ?>" class="button button-secondary">Back</a>
    </div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fff3f2;color:#b42318;border:1px solid #f3c7c3;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/reminders/templates/save')) ?>" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="id" value="<?= e((string) $value('id')) ?>">
        <label><span>Code</span><input type="text" name="code" value="<?= e((string) $value('code')) ?>" required></label>
        <label><span>Reminder Type</span><select name="reminder_type"><?php foreach (($options['reminder_types'] ?? []) as $type): ?><option value="<?= e($type) ?>" <?= (string) $value('reminder_type') === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
        <label><span>Channel</span><select name="channel"><?php foreach (($options['channels'] ?? []) as $channel): ?><option value="<?= e($channel) ?>" <?= (string) $value('channel', 'IN_APP') === $channel ? 'selected' : '' ?>><?= e($channel) ?></option><?php endforeach; ?></select></label>
        <label style="grid-column:1 / -1;"><span>Subject</span><input type="text" name="subject" value="<?= e((string) $value('subject')) ?>"></label>
        <label style="grid-column:1 / -1;"><span>Message</span><textarea name="message" rows="8" required><?= e((string) $value('message')) ?></textarea></label>
        <label><span>Active</span><select name="is_active"><option value="1" <?= (string) $value('is_active', '1') === '1' ? 'selected' : '' ?>>Yes</option><option value="0" <?= (string) $value('is_active') === '0' ? 'selected' : '' ?>>No</option></select></label>
        <div style="display:flex;align-items:end;"><button type="submit" class="button">Save Template</button></div>
    </form>
</section>
