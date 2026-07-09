<section style="display:grid;gap:20px;">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="hero-card">
        <div class="eyebrow">Office Command Centre</div>
        <h3 style="margin:10px 0 8px;font-size:1.8rem;">Welcome, <?= e($user['full_name'] ?? 'User') ?></h3>
        <p class="subtle" style="margin:0 0 20px;">Active persona: <?= e($dashboard['persona'] ?? 'General') ?> | <?= e(date('l, d M Y')) ?></p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('clients.create')): ?>
                <a href="<?= e(url('/clients/create')) ?>" class="button" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);">+ Add Client</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('service_orders.create')): ?>
                <a href="<?= e(url('/service-orders/create')) ?>" class="button" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);">+ Create Service Order</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::canAny('attendance.view', 'attendance.report.review')): ?>
                <a href="<?= e(url('/attendance')) ?>" class="button button-secondary">Staff Monitor</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('billing.view')): ?>
                <a href="<?= e(url('/billing')) ?>" class="button button-secondary">Billing</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::canAny('reports.view', 'reports.financial')): ?>
                <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Reports</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));">
        <?php foreach (($dashboard['metrics'] ?? []) as $label => $value): ?>
            <div class="metric" style="min-height:100px;">
                <div class="eyebrow"><?= e(ucwords(str_replace('_', ' ', (string) $label))) ?></div>
                <div style="font-size:2rem;font-weight:800;margin-top:8px;"><?= e((string) $value) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (($dashboard['queues'] ?? []) !== []): ?>
        <div style="display:grid;gap:16px;">
            <div class="eyebrow">Priority Work</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(380px, 1fr));gap:16px;">
                <?php foreach (($dashboard['queues'] ?? []) as $queueName => $rows): ?>
                    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div>
                                <div class="eyebrow">Queue</div>
                                <h4 style="margin:2px 0 0;font-size:1rem;"><?= e(ucwords(str_replace('_', ' ', (string) $queueName))) ?></h4>
                            </div>
                            <span class="chip"><?= count($rows) ?> items</span>
                        </div>
                        <?php if ($rows === []): ?>
                            <p style="color:#64748b;font-size:0.9rem;">No records in this queue.</p>
                        <?php else: ?>
                            <div style="display:grid;gap:8px;">
                                <?php foreach ($rows as $row): ?>
                                    <div style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;">
                                        <?php foreach ($row as $key => $value): ?>
                                            <div class="stat-line" style="font-size:0.85rem;">
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
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(380px, 1fr));gap:16px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <div class="eyebrow">Alerts</div>
                    <h4 style="margin:2px 0 0;font-size:1rem;">Notifications</h4>
                </div>
                <?php if (\App\Core\Auth::can('reminders.view')): ?>
                    <a href="<?= e(url('/reminders')) ?>" class="button button-secondary" style="padding:6px 12px;font-size:0.82rem;">Reminders</a>
                <?php endif; ?>
            </div>
            <?php if (($dashboard['notifications'] ?? []) === []): ?>
                <p style="color:#64748b;font-size:0.9rem;">No active notifications.</p>
            <?php else: ?>
                <div style="display:grid;gap:8px;">
                    <?php foreach (array_slice($dashboard['notifications'] ?? [], 0, 5) as $notification): ?>
                        <div style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                <strong style="font-size:0.9rem;"><?= e($notification['subject'] ?: 'Notification') ?></strong>
                                <span class="chip" style="font-size:0.72rem;padding:3px 8px;"><?= e($notification['delivery_status']) ?></span>
                            </div>
                            <div class="subtle" style="margin-top:4px;font-size:0.82rem;"><?= e($notification['message']) ?></div>
                            <div style="margin-top:6px;font-size:0.75rem;color:#94a3b8;"><?= e($notification['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <div class="eyebrow">Quick Access</div>
                    <h4 style="margin:2px 0 0;font-size:1rem;">Workspaces</h4>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:8px;">
                <?php if (\App\Core\Auth::can('clients.view')): ?>
                    <a href="<?= e(url('/clients')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Clients</div>
                        <div style="font-size:0.78rem;color:#64748b;">Register</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::can('service_orders.view')): ?>
                    <a href="<?= e(url('/service-orders')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Service Orders</div>
                        <div style="font-size:0.78rem;color:#64748b;">Register</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('attendance.view', 'attendance.report.review')): ?>
                    <a href="<?= e(url('/attendance')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Staff Monitor</div>
                        <div style="font-size:0.78rem;color:#64748b;">Attendance</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::can('billing.view')): ?>
                    <a href="<?= e(url('/billing')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Billing</div>
                        <div style="font-size:0.78rem;color:#64748b;">Invoices</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('reports.view', 'reports.financial')): ?>
                    <a href="<?= e(url('/reports')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Reports</div>
                        <div style="font-size:0.78rem;color:#64748b;">Analytics</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('users.manage.portal', 'users.manage.internal')): ?>
                    <a href="<?= e(url('/users')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Users</div>
                        <div style="font-size:0.78rem;color:#64748b;">Accounts</div>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('consultants.view')): ?>
                    <a href="<?= e(url('/consultants')) ?>" style="padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;transition:all 0.15s;">
                        <div style="font-weight:700;font-size:0.9rem;">Consultants</div>
                        <div style="font-size:0.78rem;color:#64748b;">Workspace</div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div>
                <div class="eyebrow">Workspace</div>
                <h4 style="margin:2px 0 0;">Session Info</h4>
            </div>
        </div>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
            <div class="metric" style="min-height:70px;">
                <div class="eyebrow">User</div>
                <div style="font-weight:700;"><?= e($user['username'] ?? '-') ?></div>
            </div>
            <div class="metric" style="min-height:70px;">
                <div class="eyebrow">Roles</div>
                <div style="font-weight:700;"><?= e(implode(', ', $user['roles'] ?? [])) ?></div>
            </div>
            <div class="metric" style="min-height:70px;">
                <div class="eyebrow">Environment</div>
                <div style="font-weight:700;"><?= e((string) config('app.env', 'local')) ?></div>
            </div>
        </div>
    </div>
</section>
