<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Collection Ageing</h3><div class="subtle">Ageing analysis of outstanding invoices by bucket.</div></div></div>

    <?php foreach ($buckets as $bucket => $rows): ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;"><?= e($bucket) ?></h4>
                <span class="chip"><?= count($rows) ?> invoices</span>
            </div>
            <?php if ($rows === []): ?>
                <p class="subtle">No invoices in this bucket.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead class="table-header"><tr><th>Invoice</th><th>Client</th><th>Due</th><th>Total</th><th>Ageing</th></tr></thead>
                        <tbody class="table-body">
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><strong><?= e($row['invoice_no']) ?></strong></td>
                                <td><?= queue_cell_html('client_name', $row['client_name'] ?? '') ?></td>
                                <td><?= e($row['due_date'] ?: '—') ?></td>
                                <td>INR <?= e(number_format((float) $row['net_payable'], 2)) ?></td>
                                <td><?= e((string) ($row['ageing_days'] ?? 0)) ?> days</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>
