<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Accounts Dashboard</h3><div class="subtle">Financial overview and collection control centre.</div></div></div>

    <?php
        $acctTiles = [
            'total_invoiced' => ['label' => 'Total Invoiced', 'severity' => 'neutral', 'money' => true],
            'total_received' => ['label' => 'Total Received', 'severity' => 'success', 'money' => true],
            'outstanding' => ['label' => 'Outstanding', 'severity' => 'warning', 'money' => true],
            'overdue_amount' => ['label' => 'Overdue', 'severity' => 'danger', 'money' => true],
            'due_today' => ['label' => 'Due Today', 'severity' => 'warning', 'money' => false],
            'unbilled_completed' => ['label' => 'Unbilled Work', 'severity' => 'neutral', 'money' => false],
            'consultant_payables' => ['label' => 'Consultant Payables', 'severity' => 'warning', 'money' => true],
            'recent_receipts' => ['label' => 'Recent Receipts', 'severity' => 'success', 'money' => false],
        ];
    ?>
    <div class="kpi-grid" style="margin-bottom:20px;">
        <?php foreach ($acctTiles as $key => $tile): ?>
            <div class="kpi-card severity-<?= e($tile['severity']) ?>">
                <div class="kpi-icon"><?= metric_icon_svg($tile['severity']) ?></div>
                <div class="kpi-body">
                    <div class="kpi-label"><?= e($tile['label']) ?></div>
                    <div class="kpi-value"><?= $tile['money'] ? 'INR ' . e(number_format((float) ($summary[$key] ?? 0), 0)) : e((string) ($summary[$key] ?? 0)) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Quick Links</div>
            <h4 style="margin:4px 0 12px;">Financial Workspaces</h4>
            <?php if (\App\Core\Auth::canAny('accounts.view', 'billing.view')): ?>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                <a href="<?= e(url('/accounts/invoices')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg></span>
                    <span><div class="quick-tile-title">Invoices</div></span>
                </a>
                <a href="<?= e(url('/accounts/receipts')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg></span>
                    <span><div class="quick-tile-title">Receipts</div></span>
                </a>
                <a href="<?= e(url('/accounts/payments')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                    <span><div class="quick-tile-title">Payments</div></span>
                </a>
                <a href="<?= e(url('/accounts/outstanding')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></span>
                    <span><div class="quick-tile-title">Outstanding</div></span>
                </a>
                <a href="<?= e(url('/accounts/ageing')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                    <span><div class="quick-tile-title">Ageing</div></span>
                </a>
                <a href="<?= e(url('/accounts/consultant-payables')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
                    <span><div class="quick-tile-title">Consultant Payables</div></span>
                </a>
                <a href="<?= e(url('/accounts/unbilled-work')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg></span>
                    <span><div class="quick-tile-title">Unbilled Work</div></span>
                </a>
                <a href="<?= e(url('/accounts/reports')) ?>" class="quick-tile">
                    <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                    <span><div class="quick-tile-title">Reports</div></span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
