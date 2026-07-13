<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Reference</div><h3 style="margin:0 0 6px;">Service Type Reference</h3><div class="subtle">Read-only service type definitions used across workflows.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (($serviceTypes ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No service type definitions</div><p class="subtle" style="margin:8px 0 0;">No service types are configured. Contact a system administrator to review the reference data.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Code</th><th>Name</th><th>Group</th><th>SLA Days</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($serviceTypes as $st): ?>
            <tr><td><strong><?= e($st['code']) ?></strong></td><td><?= e($st['name']) ?></td><td><?= e($st['service_group']) ?></td><td><?= e((string) $st['default_sla_days']) ?></td><td><span class="chip <?= $st['is_active'] ? '' : 'chip-strong' ?>"><?= $st['is_active'] ? 'Active' : 'Inactive' ?></span></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
