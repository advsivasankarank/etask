<section style="display:grid;gap:20px;">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="hero-card">
        <div class="hero-card-body">
            <div class="eyebrow">Office Command Centre</div>
            <h2 style="margin:10px 0 6px;font-family:var(--font-display);font-size:1.8rem;font-weight:700;color:#fff;">Welcome back, <?= e($user['full_name'] ?? 'User') ?></h2>
            <p class="subtle" style="margin:0;font-size:14.5px;"><?= e($dashboard['persona'] ?? 'General') ?> workspace &middot; <?= e(date('l, d M Y')) ?></p>
        </div>
        <div class="hero-card-actions" style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('clients.create')): ?>
                <a href="<?= e(url('/clients/create')) ?>" class="hero-btn hero-btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Client
                </a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('service_orders.create')): ?>
                <a href="<?= e(url('/service-orders/create')) ?>" class="hero-btn hero-btn-secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Service Order
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php
        $allMetrics = $dashboard['metrics'] ?? [];
        $heroMetrics = $dashboard['heroStats'] ?? array_slice($allMetrics, 0, 4, true);
        $restMetrics = isset($dashboard['heroStats']) ? $allMetrics : array_slice($allMetrics, 4, null, true);
    ?>

    <div class="stat-row">
        <?php foreach ($heroMetrics as $label => $value): ?>
            <?php $severity = metric_severity((string) $label); ?>
            <div class="stat-card severity-<?= e($severity) ?>">
                <div class="stat-card-icon"><?= metric_icon_svg($severity) ?></div>
                <div>
                    <div class="stat-card-value"><?= e((string) $value) ?></div>
                    <div class="stat-card-label"><?= e(label_case((string) $label)) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (($dashboard['persona'] ?? '') === 'Admin'): ?>
        <div style="display:grid;grid-template-columns:minmax(0, 1fr) minmax(0, 1.4fr);gap:16px;">
            <div class="panel" style="box-shadow:none;">
                <div class="eyebrow">Work In Progress</div>
                <h2 class="section-title" style="margin-top:2px;">Open Service Orders by Stage</h2>
                <?php if (($dashboard['stageBreakdown'] ?? []) === []): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-title">No open service orders</div>
                        <div class="empty-state-text">Stage distribution will appear here once work is in progress.</div>
                    </div>
                <?php else: ?>
                    <?php
                        $stageLabels = [];
                        $stageCounts = [];
                        $stageColors = [];
                        foreach ($dashboard['stageBreakdown'] as $stage) {
                            $stageLabels[] = label_case((string) $stage['current_stage_code']);
                            $stageCounts[] = (int) $stage['cnt'];
                            $stageColors[] = severity_hex(status_severity((string) $stage['current_stage_code']));
                        }
                    ?>
                    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                        <div style="position:relative;width:150px;height:150px;flex-shrink:0;">
                            <canvas id="stageDonutChart" role="img" aria-label="Open service orders grouped by workflow stage"></canvas>
                        </div>
                        <div style="display:grid;gap:8px;flex:1;min-width:160px;">
                            <?php foreach ($stageLabels as $i => $stageLabel): ?>
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:0.85rem;">
                                    <span style="display:flex;align-items:center;gap:8px;">
                                        <span style="width:10px;height:10px;border-radius:50%;background:<?= e($stageColors[$i]) ?>;display:inline-block;flex-shrink:0;"></span>
                                        <?= e($stageLabel) ?>
                                    </span>
                                    <strong><?= e((string) $stageCounts[$i]) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var el = document.getElementById('stageDonutChart');
                        if (!el || typeof Chart === 'undefined') return;
                        if (typeof Chart.defaults !== 'undefined') { Chart.defaults.font.family = "'Poppins', sans-serif"; }
                        new Chart(el, {
                            type: 'doughnut',
                            data: {
                                labels: <?= json_encode($stageLabels) ?>,
                                datasets: [{
                                    data: <?= json_encode($stageCounts) ?>,
                                    backgroundColor: <?= json_encode($stageColors) ?>,
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                cutout: '68%',
                                plugins: { legend: { display: false } }
                            }
                        });
                    })();
                    </script>
                <?php endif; ?>
            </div>

            <div class="panel" style="box-shadow:none;">
                <div class="eyebrow">Trend</div>
                <h2 class="section-title" style="margin-top:2px;">Service Orders Created — Last 14 Days</h2>
                <div style="height:190px;">
                    <canvas id="creationTrendChart" role="img" aria-label="Service orders created each day during the last 14 days"></canvas>
                </div>
                <script>
                (function() {
                    var el = document.getElementById('creationTrendChart');
                    if (!el || typeof Chart === 'undefined') return;
                    if (typeof Chart.defaults !== 'undefined') { Chart.defaults.font.family = "'Poppins', sans-serif"; }
                    var gradient = el.getContext('2d').createLinearGradient(0, 0, 0, 190);
                    gradient.addColorStop(0, 'rgba(20, 153, 168, 0.25)');
                    gradient.addColorStop(1, 'rgba(20, 153, 168, 0.02)');
                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels: <?= json_encode(array_map(static fn ($row) => date('d M', strtotime((string) $row['date'])), $dashboard['creationTrend'] ?? [])) ?>,
                            datasets: [{
                                label: 'Service Orders Created',
                                data: <?= json_encode(array_map(static fn ($row) => $row['count'], $dashboard['creationTrend'] ?? [])) ?>,
                                borderColor: '#1499a8',
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointBackgroundColor: '#1499a8'
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(16,24,40,0.05)' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                })();
                </script>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($restMetrics !== []): ?>
        <div class="kpi-grid">
            <?php foreach ($restMetrics as $label => $value): ?>
                <?php $severity = metric_severity((string) $label); ?>
                <div class="kpi-card severity-<?= e($severity) ?>">
                    <div class="kpi-icon"><?= metric_icon_svg($severity) ?></div>
                    <div class="kpi-body">
                        <div class="kpi-label"><?= e(label_case((string) $label)) ?></div>
                        <div class="kpi-value"><?= e((string) $value) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (($dashboard['persona'] ?? '') === 'Admin'): ?>
        <div style="display:grid;gap:14px;">
            <div class="eyebrow">Priority Work</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:16px;">
                <div class="panel" style="box-shadow:none;margin:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <div>
                            <div class="eyebrow">Queue</div>
                            <h2 style="margin:2px 0 0;font-size:15px;">Compliance Due This Week</h2>
                        </div>
                        <span class="chip"><?= count($dashboard['complianceDueThisWeek'] ?? []) ?> items</span>
                    </div>
                    <?php if (($dashboard['complianceDueThisWeek'] ?? []) === []): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✅</div>
                            <div class="empty-state-title">All clear</div>
                            <div class="empty-state-text">Nothing due in the next 7 days.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead class="table-header">
                                    <tr><th>Client</th><th>Compliance</th><th>Due</th></tr>
                                </thead>
                                <tbody class="table-body">
                                    <?php foreach ($dashboard['complianceDueThisWeek'] as $row): ?>
                                        <?php $due = due_badge((string) $row['sla_due_at']); ?>
                                        <tr>
                                            <td><?= queue_cell_html('client_name', $row['client_name']) ?></td>
                                            <td><?= e($row['service_type_name']) ?></td>
                                            <td><span class="badge badge-<?= e($due['severity']) ?>"><?= e($due['label']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="panel" style="box-shadow:none;margin:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <div>
                            <div class="eyebrow">Queue</div>
                            <h2 style="margin:2px 0 0;font-size:15px;">Documents Awaiting Review</h2>
                        </div>
                        <span class="chip"><?= count($dashboard['documentsAwaitingReview'] ?? []) ?> items</span>
                    </div>
                    <?php if (($dashboard['documentsAwaitingReview'] ?? []) === []): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✅</div>
                            <div class="empty-state-title">All clear</div>
                            <div class="empty-state-text">No documents pending review.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead class="table-header">
                                    <tr><th>Client</th><th>Document</th><th>Uploaded</th></tr>
                                </thead>
                                <tbody class="table-body">
                                    <?php foreach ($dashboard['documentsAwaitingReview'] as $row): ?>
                                        <tr>
                                            <td><?= queue_cell_html('client_name', $row['client_name']) ?></td>
                                            <td><?= e($row['document_name']) ?></td>
                                            <td class="subtle"><?= e(relative_time((string) $row['uploaded_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif (($dashboard['queues'] ?? []) !== []): ?>
        <div style="display:grid;gap:16px;">
            <div class="eyebrow">Priority Work</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:16px;">
                <?php foreach (($dashboard['queues'] ?? []) as $queueName => $rows): ?>
                    <div class="panel" style="box-shadow:none;margin:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div>
                                <div class="eyebrow">Queue</div>
                                <h2 style="margin:2px 0 0;font-size:1rem;"><?= e(label_case((string) $queueName)) ?></h2>
                            </div>
                            <span class="chip"><?= count($rows) ?> items</span>
                        </div>
                        <?php if ($rows === []): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">✅</div>
                                <div class="empty-state-title">All clear</div>
                                <div class="empty-state-text">No records in this queue.</div>
                            </div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table>
                                    <thead class="table-header">
                                        <tr>
                                            <?php foreach (array_keys($rows[0]) as $col): ?>
                                                <th><?= e(label_case((string) $col)) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="table-body">
                                        <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $key => $value): ?>
                                                    <td><?= queue_cell_html((string) $key, $value) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
                    <h2 style="margin:2px 0 0;font-size:1rem;">Notifications</h2>
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
                            <div style="margin-top:6px;font-size:0.75rem;color:#94a3b8;"><?= e(relative_time((string) $notification['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <div class="eyebrow">Quick Access</div>
                    <h2 style="margin:2px 0 0;font-size:1rem;">Workspaces</h2>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:8px;">
                <?php if (\App\Core\Auth::can('clients.view')): ?>
                    <a href="<?= e(url('/clients')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
                        <span><div class="quick-tile-title">Clients</div><div class="quick-tile-sub">Register</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::can('service_orders.view')): ?>
                    <a href="<?= e(url('/service-orders')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg></span>
                        <span><div class="quick-tile-title">Service Orders</div><div class="quick-tile-sub">Register</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('attendance.view', 'attendance.report.review')): ?>
                    <a href="<?= e(url('/attendance')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span>
                        <span><div class="quick-tile-title">Staff Monitor</div><div class="quick-tile-sub">Attendance</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::can('billing.view')): ?>
                    <a href="<?= e(url('/billing')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                        <span><div class="quick-tile-title">Billing</div><div class="quick-tile-sub">Invoices</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('reports.view', 'reports.financial')): ?>
                    <a href="<?= e(url('/reports')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                        <span><div class="quick-tile-title">Reports</div><div class="quick-tile-sub">Analytics</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('users.manage.portal', 'users.manage.internal')): ?>
                    <a href="<?= e(url('/users')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                        <span><div class="quick-tile-title">Users</div><div class="quick-tile-sub">Accounts</div></span>
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Auth::canAny('consultants.view')): ?>
                    <a href="<?= e(url('/consultants')) ?>" class="quick-tile">
                        <span class="quick-tile-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
                        <span><div class="quick-tile-title">Consultants</div><div class="quick-tile-sub">Workspace</div></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (($dashboard['upcomingDeadlines'] ?? []) !== []): ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Schedule</div>
            <h2 style="margin:2px 0 12px;font-size:15px;">Upcoming Deadlines</h2>
            <div style="display:grid;gap:10px;">
                <?php foreach ($dashboard['upcomingDeadlines'] as $deadline): ?>
                    <?php $dueTimestamp = strtotime((string) $deadline['due_date']); ?>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#e8f6f8;color:var(--primary-dark);display:grid;place-items:center;flex-shrink:0;font-family:var(--font-display);">
                            <span style="font-size:15px;font-weight:700;line-height:1;"><?= e($dueTimestamp !== false ? date('d', $dueTimestamp) : '-') ?></span>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:600;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($deadline['service_type_name']) ?></div>
                            <div class="subtle" style="font-size:11.5px;"><?= e($dueTimestamp !== false ? date('d M', $dueTimestamp) : '') ?> &middot; <?= e((string) $deadline['cnt']) ?> client<?= (int) $deadline['cnt'] === 1 ? '' : 's' ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div>
                <div class="eyebrow">Workspace</div>
                <h2 style="margin:2px 0 0;">Session Info</h2>
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
