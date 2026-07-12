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
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No billing-ready service orders found.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr><th>SO No</th><th>Client</th><th>Service</th><th>Company</th><th>Stage</th><th></th></tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['so_no']) ?></td>
                            <td><?= queue_cell_html('client_name', $order['client_name']) ?></td>
                            <td><?= e($order['service_type_name']) ?></td>
                            <td><?= e($order['company_name']) ?></td>
                            <td><span class="badge badge-<?= e(status_severity((string) $order['current_stage_code'])) ?>"><?= e(label_case((string) $order['current_stage_code'])) ?></span></td>
                            <td><a class="btn btn-secondary btn-sm" href="<?= e(url('/billing/show?service_order_id=' . $order['id'])) ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/billing',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>
