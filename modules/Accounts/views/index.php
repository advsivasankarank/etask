<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Accounts Dashboard</h3><div class="subtle">Financial overview and collection control centre.</div></div></div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Invoiced</div><div style="font-size:1.4rem;font-weight:800;">INR <?= e(number_format((float) ($summary['total_invoiced'] ?? 0), 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Received</div><div style="font-size:1.4rem;font-weight:800;color:#047857;">INR <?= e(number_format((float) ($summary['total_received'] ?? 0), 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Outstanding</div><div style="font-size:1.4rem;font-weight:800;color:#ea580c;">INR <?= e(number_format((float) ($summary['outstanding'] ?? 0), 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Overdue</div><div style="font-size:1.4rem;font-weight:800;color:#b42318;">INR <?= e(number_format((float) ($summary['overdue_amount'] ?? 0), 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Due Today</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['due_today'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Unbilled Work</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['unbilled_completed'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Consultant Payables</div><div style="font-size:1.4rem;font-weight:800;color:#ea580c;">INR <?= e(number_format((float) ($summary['consultant_payables'] ?? 0), 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Recent Receipts</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['recent_receipts'] ?? 0)) ?></div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Quick Links</div>
            <h4 style="margin:4px 0 12px;">Financial Workspaces</h4>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/invoices')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Invoices</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/receipts')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Receipts</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/payments')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Payments</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/outstanding')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Outstanding</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/ageing')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Ageing</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/consultant-payables')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Consultant Payables</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/unbilled-work')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Unbilled Work</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?><a href="<?= e(url('/accounts/reports')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Reports</div></a><?php endif; ?>
            </div>
        </div>
    </div>
</section>
