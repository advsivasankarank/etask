<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Payments</h3><div class="subtle">Track payments made to consultants.</div></div></div>

    <?php if (($payments['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Payments</div><p class="subtle" style="margin:8px 0 0;">No payments recorded.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Bill No.</th><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Remarks</th></tr></thead><tbody>
        <?php foreach ($payments['items'] as $p): ?><tr><td><strong><?= e($p['consultant_name'] ?: '-') ?></strong></td><td><?= e($p['bill_no'] ?: '-') ?></td><td><?= e($p['payment_date'] ?: '-') ?></td><td>INR <?= e(number_format((float) ($p['amount'] ?? 0), 2)) ?></td><td><?= e($p['mode'] ?: '-') ?></td><td><?= e($p['reference_no'] ?: '-') ?></td><td><?= e($p['remarks'] ?: '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
