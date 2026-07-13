<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Reports Dashboard</h3><div class="subtle">Consolidated reporting centre for all modules.</div></div></div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Clients</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total_clients'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Active SO</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['active_service_orders'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Overdue SO</div><div style="font-size:1.6rem;font-weight:800;color:#b42318;"><?= e((string) ($summary['overdue_service_orders'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Pending Docs</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['pending_documents'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">DSC Expiring</div><div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['dsc_expiring_soon'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Staff Present</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['staff_present_today'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Outstanding</div><div style="font-size:1.4rem;font-weight:800;color:#ea580c;"><?= e(money_inr($summary['outstanding_amount'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Consultant Payables</div><div style="font-size:1.4rem;font-weight:800;color:#ea580c;"><?= e(money_inr($summary['consultant_payables'] ?? 0)) ?></div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        <a href="<?= e(url('/reports/operational')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Operational</div>
            <h4 style="margin:4px 0;">Operational Reports</h4>
            <div class="subtle">Pending work, overdue, reviews, documents.</div>
        </a>
        <a href="<?= e(url('/reports/clients')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Clients</div>
            <h4 style="margin:4px 0;">Client Reports</h4>
            <div class="subtle">Client register, status, service orders.</div>
        </a>
        <a href="<?= e(url('/reports/service-orders')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Service Orders</div>
            <h4 style="margin:4px 0;">Service Order Reports</h4>
            <div class="subtle">SO status, ageing, workload.</div>
        </a>
        <a href="<?= e(url('/reports/workforce')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Workforce</div>
            <h4 style="margin:4px 0;">Workforce Reports</h4>
            <div class="subtle">Staff register, workload, reports pending.</div>
        </a>
        <a href="<?= e(url('/reports/attendance')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Attendance</div>
            <h4 style="margin:4px 0;">Attendance Reports</h4>
            <div class="subtle">Present today, productivity, activity.</div>
        </a>
        <a href="<?= e(url('/reports/documents')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Documents</div>
            <h4 style="margin:4px 0;">Document Reports</h4>
            <div class="subtle">Verification status, access logs.</div>
        </a>
        <a href="<?= e(url('/reports/dsc')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">DSC</div>
            <h4 style="margin:4px 0;">DSC Reports</h4>
            <div class="subtle">Expiry, custody, usage summary.</div>
        </a>
        <a href="<?= e(url('/reports/accounts')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Accounts</div>
            <h4 style="margin:4px 0;">Accounts Reports</h4>
            <div class="subtle">Invoices, receipts, outstanding.</div>
        </a>
        <a href="<?= e(url('/reports/consultants')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Consultants</div>
            <h4 style="margin:4px 0;">Consultant Reports</h4>
            <div class="subtle">Assignments, bills, payables.</div>
        </a>
        <a href="<?= e(url('/reports/audit')) ?>" class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;text-decoration:none;color:inherit;">
            <div class="eyebrow">Audit</div>
            <h4 style="margin:4px 0;">Audit Reports</h4>
            <div class="subtle">Activity logs, follow-ups.</div>
        </a>
    </div>
</section>
