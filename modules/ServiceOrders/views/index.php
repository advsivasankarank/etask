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
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No service orders found matching your search criteria.</div>
            <?php if (\App\Core\Auth::can('service_orders.create')): ?>
                <a href="<?= e(url('/service-orders/create')) ?>" class="button" style="margin-top:16px;">+ Create First Service Order</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr>
                        <th>SO No</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Stage</th>
                        <th>Period</th>
                        <th>Priority</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['so_no']) ?></td>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-chip"><?= e(strtoupper(substr($order['client_name'], 0, 1))) ?></span>
                                    <span><?= e($order['client_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div><?= e($order['service_type_name']) ?></div>
                                <div class="subtle" style="font-size:0.78rem;"><?= e($order['company_name']) ?></div>
                            </td>
                            <td><span class="badge badge-<?= e(status_severity((string) $order['current_stage_code'])) ?>"><?= e(label_case((string) $order['current_stage_code'])) ?></span></td>
                            <td><?= e($order['period_label'] ?: '—') ?></td>
                            <td>
                                <?php if ((int) $order['is_locked'] === 1): ?>
                                    <span class="badge badge-neutral">Locked</span>
                                <?php else: ?>
                                    <span class="badge badge-<?= e(priority_severity((string) $order['priority_level'])) ?>"><?= e(label_case((string) $order['priority_level'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?= e(url('/service-orders/show?id=' . $order['id'])) ?>" class="btn btn-secondary btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/service-orders',
            'query' => ['search' => $searchValue],
        ], null) ?>
    <?php endif; ?>
</section>
