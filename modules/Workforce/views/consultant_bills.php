<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Bills</h3><div class="subtle">Track consultant billing and payment status.</div></div></div>

    <form method="get" action="<?= e(url('/workforce/consultant-bills')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Status</option><?php foreach (['DRAFT','SUBMITTED','APPROVED','PAID','REJECTED'] as $s): ?><option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($bills['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Bills</div><p class="subtle" style="margin:8px 0 0;">No bills found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Bill No.</th><th>Date</th><th>Amount</th><th>Tax</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($bills['items'] as $b): ?><tr><td><strong><?= e($b['consultant_name'] ?: '-') ?></strong></td><td><?= e($b['bill_no'] ?: '-') ?></td><td><?= e($b['bill_date'] ?: '-') ?></td><td>INR <?= e(number_format((float) ($b['amount'] ?? 0), 2)) ?></td><td>INR <?= e(number_format((float) ($b['tax_amount'] ?? 0), 2)) ?></td><td>INR <?= e(number_format((float) ($b['total_amount'] ?? 0), 2)) ?></td><td><span class="chip <?= $b['status'] === 'PAID' ? '' : ($b['status'] === 'REJECTED' ? 'chip-strong' : '') ?>"><?= e($b['status']) ?></span></td><td><?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><form method="post" action="<?= e(url('/workforce/consultant-bills/status')) ?>" style="display:inline;"><?= \App\Core\Csrf::inputField() ?><input type="hidden" name="bill_id" value="<?= e((string) $b['id']) ?>"><select name="status" onchange="this.form.submit()" style="padding:4px 8px;font-size:0.78rem;border:1px solid #d8e1eb;border-radius:8px;"><option value="">Update</option><?php foreach (['DRAFT','SUBMITTED','APPROVED','PAID','REJECTED'] as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?></select></form><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
