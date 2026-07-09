<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Reminder Templates</h3><div class="subtle">Manage reminder message templates.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (($templates ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No reminder templates configured.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Code</th><th>Type</th><th>Channel</th><th>Subject</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($templates as $t): ?>
            <tr><td><strong><?= e($t['code']) ?></strong></td><td><?= e($t['reminder_type']) ?></td><td><?= e($t['channel']) ?></td><td><?= e($t['subject'] ?: '-') ?></td><td><span class="chip <?= $t['is_active'] ? '' : 'chip-strong' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
