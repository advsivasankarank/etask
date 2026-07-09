<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Milestone Settings</h3><div class="subtle">Workflow milestone definitions.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (($milestones ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No milestones configured.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Stage Code</th><th>Stage Name</th><th>Service Type</th><th>Order</th><th>Required</th><th>Terminal</th></tr></thead><tbody>
        <?php foreach ($milestones as $m): ?>
            <tr><td><strong><?= e($m['stage_code']) ?></strong></td><td><?= e($m['stage_name']) ?></td><td><?= e($m['service_type_name'] ?: '-') ?></td><td><?= e((string) $m['sort_order']) ?></td><td><?= $m['is_milestone_click_required'] ? 'Yes' : 'No' ?></td><td><?= $m['is_terminal'] ? 'Yes' : 'No' ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
