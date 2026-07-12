<section style="display:grid;gap:18px;">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fff3f2;color:#b42318;border:1px solid #f3c7c3;"><?= e($error) ?></div><?php endif; ?>

    <div class="hero-card">
        <div class="eyebrow">Reminder Engine</div>
        <h3 style="margin:10px 0 8px;font-size:2rem;">Reminder and notification operations</h3>
        <p class="subtle" style="margin:0;">Templates, escalation rules, scheduler delivery, dashboard alerts, and reminder reporting in one place.</p>
    </div>

    <?php
        $reminderTiles = [
            'open_reminders' => ['label' => 'Active Reminders', 'severity' => 'neutral'],
            'due_today' => ['label' => 'Due Today', 'severity' => 'warning'],
            'overdue' => ['label' => 'Escalation Queue', 'severity' => 'danger'],
            'email_failures' => ['label' => 'Delivery Failures', 'severity' => 'danger'],
        ];
    ?>
    <div class="kpi-grid">
        <?php foreach ($reminderTiles as $key => $tile): ?>
            <div class="kpi-card severity-<?= e($tile['severity']) ?>">
                <div class="kpi-icon"><?= metric_icon_svg($tile['severity']) ?></div>
                <div class="kpi-body">
                    <div class="kpi-label"><?= e($tile['label']) ?></div>
                    <div class="kpi-value"><?= e((string) ($overview['summary'][$key] ?? 0)) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card-grid">
        <article class="data-card"><div class="eyebrow">Manage</div><h4 style="margin:0;">Reminder Templates</h4><p class="subtle" style="margin:0;">Create and maintain dashboard and email reminder content.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/templates')) ?>" class="button button-secondary">Open</a><a href="<?= e(url('/reminders/templates/form')) ?>" class="button">Create</a></div></article>
        <article class="data-card"><div class="eyebrow">Rules</div><h4 style="margin:0;">Escalation Rules</h4><p class="subtle" style="margin:0;">Configure day-based escalation flow to assigned users, roles, or client contacts.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/escalations')) ?>" class="button button-secondary">Open</a><a href="<?= e(url('/reminders/escalations/form')) ?>" class="button">Create</a></div></article>
        <article class="data-card"><div class="eyebrow">Reports</div><h4 style="margin:0;">Reminder Reports</h4><p class="subtle" style="margin:0;">Review reminder register, pending queue, effectiveness, and escalation history.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/register')) ?>" class="button button-secondary">Register</a><a href="<?= e(url('/reminders/pending')) ?>" class="button button-secondary">Pending</a><a href="<?= e(url('/reminders/effectiveness')) ?>" class="button button-secondary">Effectiveness</a><a href="<?= e(url('/reminders/escalation-report')) ?>" class="button button-secondary">Escalations</a></div></article>
        <article class="data-card"><div class="eyebrow">Scheduler</div><h4 style="margin:0;">Run Delivery Cycle</h4><p class="subtle" style="margin:0;">Generate due reminders, send dashboard and email notifications, and process escalations.</p><form method="post" action="<?= e(url('/reminders/run-scheduler')) ?>"><?= \App\Core\Csrf::inputField() ?><button type="submit" class="button">Run Scheduler Now</button></form></article>
    </div>
</section>
