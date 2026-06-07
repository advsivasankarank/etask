<section class="panel">
    <div class="toolbar">
        <div><div class="eyebrow">Escalations</div><h3 style="margin:0 0 6px;">Escalation Rules</h3><p class="subtle" style="margin:0;">Configurable day-based escalation rules by reminder type.</p></div>
        <div style="display:flex;gap:10px;"><a href="<?= e(url('/reminders')) ?>" class="button button-secondary">Back</a><a href="<?= e(url('/reminders/escalations/form')) ?>" class="button">Create Rule</a></div>
    </div>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Type</th><th>Day</th><th>Target</th><th>Role</th><th>Channel</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach (($rules ?? []) as $rule): ?>
                <tr>
                    <td><?= e($rule['reminder_type']) ?></td>
                    <td><?= e((string) $rule['day_offset']) ?></td>
                    <td><?= e($rule['target_type']) ?></td>
                    <td><?= e($rule['target_role_code'] ?: '-') ?></td>
                    <td><?= e($rule['channel']) ?></td>
                    <td><span class="chip <?= (int) $rule['is_active'] === 1 ? 'chip-strong' : '' ?>"><?= (int) $rule['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                    <td><a href="<?= e(url('/reminders/escalations/form?id=' . $rule['id'])) ?>" class="chip">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
