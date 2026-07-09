<?php $searchValue = $search ?? ''; ?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Service Order Module</div>
            <h3 style="margin:0 0 6px;">Service Order Register</h3>
            <div class="subtle">Search by SO number, client name, PAN, TAN, GSTIN, or mobile.</div>
        </div>
        <?php if (\App\Core\Auth::can('service_orders.create')): ?>
            <a href="<?= e(url('/service-orders/create')) ?>" class="button">+ Create Service Order</a>
        <?php endif; ?>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Total</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Active</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['active'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Due Today</div>
            <div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['due_today'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Overdue</div>
            <div style="font-size:1.6rem;font-weight:800;color:#b42318;"><?= e((string) ($summary['overdue'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Closed</div>
            <div style="font-size:1.6rem;font-weight:800;color:#047857;"><?= e((string) ($summary['closed'] ?? 0)) ?></div>
        </div>
    </div>

    <form method="get" action="<?= e(url('/service-orders')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($searchValue) ?>" placeholder="Search by SO No, Client Name, PAN, TAN, GSTIN, or Mobile...">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($orders === []): ?>
        <div class="data-card" style="text-align:center;padding:40px;">
            <div class="eyebrow">No Results</div>
            <p class="subtle" style="margin:8px 0 0;">No service orders found matching your search criteria.</p>
            <?php if (\App\Core\Auth::can('service_orders.create')): ?>
                <a href="<?= e(url('/service-orders/create')) ?>" class="button" style="margin-top:16px;">+ Create First Service Order</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($orders as $order): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($order['so_no']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($order['client_name']) ?></h4>
                        </div>
                        <span class="chip <?= (int) $order['is_locked'] === 1 ? 'chip-strong' : '' ?>"><?= (int) $order['is_locked'] === 1 ? 'Locked' : e($order['priority_level']) ?></span>
                    </div>
                    <div class="stat-line"><span>Service</span><strong><?= e($order['service_type_name']) ?></strong></div>
                    <div class="stat-line"><span>Company</span><strong><?= e($order['company_name']) ?></strong></div>
                    <div class="stat-line"><span>Stage</span><strong><?= e(str_replace('_', ' ', $order['current_stage_code'])) ?></strong></div>
                    <div class="stat-line"><span>Period</span><strong><?= e($order['period_label'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Created</span><strong><?= e($order['created_at']) ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;flex-wrap:wrap;">
                        <a href="<?= e(url('/service-orders/show?id=' . $order['id'])) ?>" class="button button-secondary">View Workspace</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/service-orders',
            'query' => ['search' => $searchValue],
        ], null) ?>
    <?php endif; ?>
</section>
