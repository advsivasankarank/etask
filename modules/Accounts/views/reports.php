<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Accounts Reports</h3><div class="subtle">Financial summary and collection reports.</div></div></div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Invoice Summary</div>
            <div class="stat-line"><span>Total Invoiced</span><strong>INR <?= e(number_format((float) ($summary['total_invoiced'] ?? 0), 2)) ?></strong></div>
            <div class="stat-line"><span>Total Received</span><strong>INR <?= e(number_format((float) ($summary['total_received'] ?? 0), 2)) ?></strong></div>
            <div class="stat-line"><span>Outstanding</span><strong>INR <?= e(number_format((float) ($summary['outstanding'] ?? 0), 2)) ?></strong></div>
            <div class="stat-line"><span>Overdue</span><strong>INR <?= e(number_format((float) ($summary['overdue_amount'] ?? 0), 2)) ?></strong></div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Collection Status</div>
            <div class="stat-line"><span>Due Today</span><strong><?= e((string) ($summary['due_today'] ?? 0)) ?></strong></div>
            <div class="stat-line"><span>Recent Receipts (30d)</span><strong><?= e((string) ($summary['recent_receipts'] ?? 0)) ?></strong></div>
            <div class="stat-line"><span>Unbilled Completed</span><strong><?= e((string) ($summary['unbilled_completed'] ?? 0)) ?></strong></div>
            <div class="stat-line"><span>Consultant Payables</span><strong>INR <?= e(number_format((float) ($summary['consultant_payables'] ?? 0), 2)) ?></strong></div>
        </div>
    </div>

    <div style="margin-top:20px;">
        <div class="eyebrow">Quick Reports</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-top:12px;">
            <a href="<?= e(url('/accounts/invoices')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Invoice Register</a>
            <a href="<?= e(url('/accounts/receipts')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Receipt Register</a>
            <a href="<?= e(url('/accounts/outstanding')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Outstanding</a>
            <a href="<?= e(url('/accounts/ageing')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Ageing</a>
            <a href="<?= e(url('/accounts/consultant-payables')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Consultant Payables</a>
            <a href="<?= e(url('/accounts/unbilled-work')) ?>" style="padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;font-weight:700;">Unbilled Work</a>
        </div>
    </div>
</section>
