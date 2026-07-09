<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Client Reports</h3><div class="subtle">Client register summary and service order status.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <?php if (($report ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No client data found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Client</th><th>PAN</th><th>Mobile</th><th>Active SO</th><th>Unpaid</th></tr></thead><tbody>
        <?php foreach ($report as $c): ?>
            <tr><td><strong><?= e($c['legal_name']) ?></strong></td><td><?= e($c['pan'] ?: '-') ?></td><td><?= e($c['mobile'] ?: '-') ?></td><td><?= e((string) $c['active_so']) ?></td><td><?= e((string) $c['unpaid_invoices']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
