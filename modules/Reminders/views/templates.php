<section class="panel">
    <div class="toolbar">
        <div><div class="eyebrow">Templates</div><h3 style="margin:0 0 6px;">Reminder Templates</h3><p class="subtle" style="margin:0;">Subject and message masters by reminder type and channel.</p></div>
        <div style="display:flex;gap:10px;"><a href="<?= e(url('/reminders')) ?>" class="button button-secondary">Back</a><a href="<?= e(url('/reminders/templates/form')) ?>" class="button">Create Template</a></div>
    </div>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Code</th><th>Type</th><th>Channel</th><th>Subject</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach (($templates ?? []) as $template): ?>
                <tr>
                    <td><?= e($template['code']) ?></td>
                    <td><?= e(label_case((string) $template['reminder_type'])) ?></td>
                    <td><?= e($template['channel']) ?></td>
                    <td><?= e($template['subject'] ?: '-') ?></td>
                    <td><span class="chip <?= (int) $template['is_active'] === 1 ? 'chip-strong' : '' ?>"><?= (int) $template['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                    <td><a href="<?= e(url('/reminders/templates/form?id=' . $template['id'])) ?>" class="chip">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
