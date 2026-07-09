<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Operational Reports</h3><div class="subtle">Pending work, overdue service orders, and operational status.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Active SO</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['active_service_orders'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Overdue SO</div><div style="font-size:1.6rem;font-weight:800;color:#b42318;"><?= e((string) ($summary['overdue_service_orders'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Pending Docs</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['pending_documents'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Staff Present</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['staff_present_today'] ?? 0)) ?></div></div>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">Overdue Service Orders</h4>
        <?php if ($overdueOrders === []): ?><p class="subtle">No overdue service orders.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>SO No.</th><th>Client</th><th>Service Type</th><th>Due</th><th>Stage</th></tr></thead><tbody>
            <?php foreach ($overdueOrders as $so): ?>
                <tr><td><strong><?= e($so['so_no']) ?></strong></td><td><?= e($so['client_name'] ?: '-') ?></td><td><?= e($so['service_type_name'] ?: '-') ?></td><td><?= e($so['sla_due_at'] ?: '-') ?></td><td><?= e(str_replace('_', ' ', $so['current_stage_code'])) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>
