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
        <div class="data-card"><span class="subtle">No consultant-linked service orders found.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($orders as $order): ?>
                <article class="data-card">
                    <div>
                        <div class="eyebrow"><?= e($order['so_no']) ?></div>
                        <h4 style="margin:4px 0 0;"><?= e($order['client_name']) ?></h4>
                    </div>
                    <div class="stat-line"><span>Service</span><strong><?= e($order['service_type_name']) ?></strong></div>
                    <div class="stat-line"><span>Company</span><strong><?= e($order['company_name']) ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <a class="button button-secondary" href="<?= e(url('/consultants/show?service_order_id=' . $order['id'])) ?>">Open Workspace</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
