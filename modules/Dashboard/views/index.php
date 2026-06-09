<section style="display:grid;gap:18px;">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="hero-card">
        <div class="eyebrow">Operations Hub</div>
        <h3 style="margin:10px 0 8px;font-size:2rem;">Welcome, <?= e($user['full_name'] ?? 'User') ?></h3>
        <p class="subtle" style="margin:0 0 22px;">Active dashboard persona: <?= e($dashboard['persona'] ?? 'General') ?></p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('clients.view')): ?>
                <a href="<?= e(url('/clients')) ?>" class="button button-secondary">Open Clients</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('service_orders.view')): ?>
                <a href="<?= e(url('/service-orders')) ?>" class="button">Open Service Orders</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::canAny('portal.self_access', 'portal.pso.create', 'portal.pso.review', 'portal.pso.approve', 'portal.pso.reject')): ?>
                <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Open PSOs</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('billing.view')): ?>
                <a href="<?= e(url('/billing')) ?>" class="button button-secondary">Open Billing</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('consultants.view')): ?>
                <a href="<?= e(url('/consultants')) ?>" class="button button-secondary">Open Consultants</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::canAny('reports.view', 'reports.financial')): ?>
                <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Open Reports</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::isPortalUser()): ?>
                <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Open Account</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <div class="eyebrow">Workspace</div>
            <strong>User</strong>
            <div style="margin-top:8px;font-size:1.15rem;"><?= e($user['username'] ?? '-') ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Access</div>
            <strong>Roles</strong>
            <div style="margin-top:8px;font-size:1.15rem;"><?= e(implode(', ', $user['roles'] ?? [])) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Runtime</div>
            <strong>Environment</strong>
            <div style="margin-top:8px;font-size:1.15rem;"><?= e((string) config('app.env', 'local')) ?></div>
        </div>
    </div>

    <div class="card-grid">
        <?php foreach (($dashboard['metrics'] ?? []) as $label => $value): ?>
            <div class="data-card">
                <div class="eyebrow">Live Metric</div>
                <strong><?= e(ucwords(str_replace('_', ' ', (string) $label))) ?></strong>
                <div style="font-size:1.85rem;font-weight:800;"><?= e((string) $value) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Alerts</div>
                <h4 class="section-title">Dashboard Notifications</h4>
            </div>
            <?php if (\App\Core\Auth::can('reminders.view')): ?>
                <a href="<?= e(url('/reminders')) ?>" class="button button-secondary">Open Reminders</a>
            <?php endif; ?>
        </div>
        <?php if (($dashboard['notifications'] ?? []) === []): ?>
            <p style="color:#64748b;">No active notifications.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach (($dashboard['notifications'] ?? []) as $notification): ?>
                    <div class="data-card" style="padding:14px;">
                        <div class="eyebrow"><?= e($notification['linked_module'] ?: 'GENERAL') ?></div>
                        <strong><?= e($notification['subject'] ?: 'Notification') ?></strong>
                        <div class="subtle" style="margin-top:6px;"><?= e($notification['message']) ?></div>
                        <div class="stat-line" style="margin-top:8px;">
                            <span>Status</span>
                            <strong><?= e($notification['delivery_status']) ?></strong>
                        </div>
                        <div class="stat-line">
                            <span>Created</span>
                            <strong><?= e($notification['created_at']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid">
        <?php foreach (($dashboard['queues'] ?? []) as $queueName => $rows): ?>
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                <div class="eyebrow">Queue</div>
                <h4 class="section-title"><?= e(ucwords(str_replace('_', ' ', (string) $queueName))) ?></h4>
                <?php if ($rows === []): ?>
                    <p style="color:#64748b;">No records in this queue.</p>
                <?php else: ?>
                    <div style="display:grid;gap:10px;">
                        <?php foreach ($rows as $row): ?>
                            <div class="data-card" style="padding:14px;">
                                <?php foreach ($row as $key => $value): ?>
                                    <div class="stat-line">
                                        <strong style="color:var(--text);"><?= e(ucwords(str_replace('_', ' ', (string) $key))) ?></strong>
                                        <span><?= e((string) $value) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
