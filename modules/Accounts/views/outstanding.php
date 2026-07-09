<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Outstanding Register</h3><div class="subtle">Invoices with pending payments and ageing analysis.</div></div></div>

    <?php if ($invoices === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Outstanding</div><p class="subtle" style="margin:8px 0 0;">No outstanding invoices.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Invoice</th><th>Client</th><th>SO</th><th>Due</th><th>Total</th><th>Outstanding</th><th>Ageing</th></tr></thead><tbody>
        <?php foreach ($invoices as $inv): ?>
            <?php
            $ageingDays = (int) ($inv['ageing_days'] ?? 0);
            $bucket = $ageingDays < 0 ? 'Not Due' : ($ageingDays <= 30 ? '0-30' : ($ageingDays <= 60 ? '31-60' : ($ageingDays <= 90 ? '61-90' : '90+')));
            $bucketColor = $bucket === '90+' ? '#b42318' : ($bucket === '61-90' ? '#ea580c' : ($bucket === '31-60' ? '#f59e0b' : '#047857'));
            ?>
            <tr>
                <td><strong><?= e($inv['invoice_no']) ?></strong></td>
                <td><?= e($inv['client_name'] ?: '-') ?></td>
                <td><?= e($inv['so_no'] ?: '-') ?></td>
                <td><?= e($inv['due_date'] ?: '-') ?></td>
                <td>INR <?= e(number_format((float) $inv['net_payable'], 2)) ?></td>
                <td>INR <?= e(number_format((float) $inv['net_payable'], 2)) ?></td>
                <td><span class="chip" style="color:<?= e($bucketColor) ?>;"><?= e($bucket) ?> (<?= e((string) $ageingDays) ?>d)</span></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
