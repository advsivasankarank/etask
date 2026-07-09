<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Backup / Maintenance</h3><div class="subtle">System maintenance logs and backup status.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">Record Maintenance Note</h4>
        <form method="post" action="<?= e(url('/settings/maintenance')) ?>" style="display:grid;gap:10px;">
            <?= \App\Core\Csrf::inputField() ?>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Action Type</span><input type="text" name="action_type" value="MANUAL_NOTE" placeholder="e.g., BACKUP_CHECK, VERSION_NOTE"></label>
            </div>
            <label style="display:grid;gap:8px;"><span>Note</span><textarea name="action_note" rows="3" placeholder="Maintenance note or observation"></textarea></label>
            <button type="submit" class="button">Record Note</button>
        </form>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Recent Maintenance Logs</h4>
        <?php if (($logs ?? []) === []): ?><p class="subtle">No maintenance logs recorded.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Action</th><th>Note</th><th>By</th><th>At</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($logs as $log): ?>
                <tr><td><strong><?= e($log['action_type']) ?></strong></td><td><?= e($log['action_note'] ?: '-') ?></td><td><?= e($log['performed_by_name'] ?: '-') ?></td><td><?= e($log['performed_at']) ?></td><td><span class="chip"><?= e($log['status'] ?: '-') ?></span></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>
