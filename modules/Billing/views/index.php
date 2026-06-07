<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Finance</div>
            <h3 style="margin:0 0 6px;">Billing Register</h3>
            <p class="subtle" style="margin:0;">Manage invoices, payments, receipts, and disbursements per service order.</p>
        </div>
    </div>

    <form method="get" action="<?= e(url('/billing')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="SO no / Client / Service" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($orders === []): ?>
        <div class="data-card"><span class="subtle">No billing-ready service orders found.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($orders as $order): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($order['so_no']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($order['client_name']) ?></h4>
                        </div>
                        <span class="chip"><?= e($order['current_stage_code']) ?></span>
                    </div>
                    <div class="stat-line"><span>Service</span><strong><?= e($order['service_type_name']) ?></strong></div>
                    <div class="stat-line"><span>Company</span><strong><?= e($order['company_name']) ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <a class="button button-secondary" href="<?= e(url('/billing/show?service_order_id=' . $order['id'])) ?>">Open Billing</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/billing',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>
