<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Accounts Reports</h3><div class="subtle">Invoice, receipt, and outstanding summary.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Invoiced</div><div style="font-size:1.4rem;font-weight:800;"><?= e(money_inr($summary['total_invoiced'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Received</div><div style="font-size:1.4rem;font-weight:800;color:#047857;"><?= e(money_inr($summary['total_received'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Outstanding</div><div style="font-size:1.4rem;font-weight:800;color:#ea580c;"><?= e(money_inr($summary['outstanding'] ?? 0)) ?></div></div>
    </div>
</section>
