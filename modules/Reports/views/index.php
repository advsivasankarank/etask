<?php use App\Core\Auth; ?>
<section style="display:grid;gap:18px;">
    <div class="hero-card">
        <div class="eyebrow">Reporting Workspace</div>
        <h3 style="margin:10px 0 8px;font-size:2rem;">Registers and financial visibility</h3>
        <p class="subtle" style="margin:0;">Open operational and finance reports without changing the service-order workflow.</p>
    </div>

    <div class="grid">
        <div class="metric">
            <div class="eyebrow">Clients</div>
            <strong>Active Register</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($cards['clients'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Service Orders</div>
            <strong>Open Register</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($cards['service_orders'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Invoices</div>
            <strong>Issued Count</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($cards['invoices'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Outstanding</div>
            <strong>Net Receivable</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($cards['outstanding_amount'] ?? 0), 2)) ?></div>
        </div>
    </div>

    <div class="card-grid">
        <?php
        $tiles = [
            ['title' => 'Client Register', 'path' => '/reports/clients', 'description' => 'Master client list with PAN, GSTIN, TAN, contact and CRM assignment.', 'permission' => 'reports.view'],
            ['title' => 'Service Order Register', 'path' => '/reports/service-orders', 'description' => 'SO lifecycle visibility with company, basis, stage, closure and SLA context.', 'permission' => 'reports.view'],
            ['title' => 'Invoice Register', 'path' => '/reports/invoices', 'description' => 'Invoice issue, payment, gross, net and outstanding status.', 'permission' => 'reports.financial'],
            ['title' => 'Receipt Register', 'path' => '/reports/receipts', 'description' => 'Receipt ledger with payment mode, references and allocation count.', 'permission' => 'reports.financial'],
            ['title' => 'Outstanding Report', 'path' => '/reports/outstanding', 'description' => 'Ageing and receivable tracking for unpaid and partially paid invoices.', 'permission' => 'reports.financial'],
            ['title' => 'GST Summary', 'path' => '/reports/gst-summary', 'description' => 'GST workload summary by company, service type, period and filing milestones.', 'permission' => 'reports.view'],
            ['title' => 'Revenue Report', 'path' => '/reports/revenue', 'description' => 'Month-wise billed, tax, net, collected and outstanding revenue view.', 'permission' => 'reports.financial'],
            ['title' => 'Document Access Report', 'path' => '/reports/document-access', 'description' => 'Audited log of every secure document download attempt and success.', 'permission' => 'documents.report'],
        ];
        ?>
        <?php foreach ($tiles as $tile): ?>
            <?php if (!Auth::can($tile['permission'])) { continue; } ?>
            <article class="data-card">
                <div class="eyebrow">Report</div>
                <h4 style="margin:0;"><?= e($tile['title']) ?></h4>
                <p class="subtle" style="margin:0;"><?= e($tile['description']) ?></p>
                <div style="display:flex;justify-content:flex-end;">
                    <a href="<?= e(url($tile['path'])) ?>" class="button button-secondary">Open Report</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
