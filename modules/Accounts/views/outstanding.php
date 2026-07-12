<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Outstanding Register</h3><div class="subtle">Invoices with pending payments and ageing analysis.</div></div></div>

    <?php if ($invoices === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">✅</div>
            <div class="empty-state-title">No outstanding</div>
            <div class="empty-state-text">No outstanding invoices.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr><th>Invoice</th><th>Client</th><th>SO</th><th>Due</th><th>Total</th><th>Outstanding</th><th>Ageing</th></tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($invoices as $inv): ?>
                        <?php
                        $ageingDays = (int) ($inv['ageing_days'] ?? 0);
                        $bucket = $ageingDays < 0 ? 'Not Due' : ($ageingDays <= 30 ? '0-30' : ($ageingDays <= 60 ? '31-60' : ($ageingDays <= 90 ? '61-90' : '90+')));
                        $bucketSeverity = $bucket === '90+' ? 'danger' : (($bucket === '61-90' || $bucket === '31-60') ? 'warning' : 'success');
                        ?>
                        <tr>
                            <td><strong><?= e($inv['invoice_no']) ?></strong></td>
                            <td><?= queue_cell_html('client_name', $inv['client_name'] ?? '') ?></td>
                            <td><?= e($inv['so_no'] ?: '—') ?></td>
                            <td><?= e($inv['due_date'] ?: '—') ?></td>
                            <td>INR <?= e(number_format((float) $inv['net_payable'], 2)) ?></td>
                            <td>INR <?= e(number_format((float) $inv['net_payable'], 2)) ?></td>
                            <td><span class="badge badge-<?= e($bucketSeverity) ?>"><?= e($bucket) ?> (<?= e((string) $ageingDays) ?>d)</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
