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
            <div class="eyebrow">Workflow Register</div>
            <h3 style="margin:0 0 6px;">SO Register</h3>
            <div class="subtle">Search by PAN, TAN, client name, mobile number, or SO number.</div>
        </div>
        <a href="<?= e(url('/service-orders/create')) ?>" class="button">Create SO</a>
    </div>

    <form method="get" action="<?= e(url('/service-orders')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($searchValue) ?>" placeholder="PAN / TAN / client / mobile / SO no" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($orders === []): ?>
        <div class="data-card"><span class="subtle">No service orders found.</span></div>
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
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <a href="<?= e(url('/service-orders/show?id=' . $order['id'])) ?>" class="button button-secondary">View SO</a>
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
