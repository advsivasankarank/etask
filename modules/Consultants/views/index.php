<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Expert Workflow</div>
            <h3 style="margin:0 0 6px;">Consultant Register</h3>
            <p class="subtle" style="margin:0;">Track consultant assignments, deliverables, bills, and payments linked to service orders.</p>
        </div>
    </div>

    <?php if ($orders === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No consultant-linked service orders found.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr><th>SO No</th><th>Client</th><th>Service</th><th>Company</th><th></th></tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['so_no']) ?></td>
                            <td><?= queue_cell_html('client_name', $order['client_name']) ?></td>
                            <td><?= e($order['service_type_name']) ?></td>
                            <td><?= e($order['company_name']) ?></td>
                            <td><a class="btn btn-secondary btn-sm" href="<?= e(url('/consultants/show?service_order_id=' . $order['id'])) ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
