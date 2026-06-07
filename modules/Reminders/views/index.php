<section style="display:grid;gap:18px;">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fff3f2;color:#b42318;border:1px solid #f3c7c3;"><?= e($error) ?></div><?php endif; ?>

    <div class="hero-card">
        <div class="eyebrow">Reminder Engine</div>
        <h3 style="margin:10px 0 8px;font-size:2rem;">Reminder and notification operations</h3>
        <p class="subtle" style="margin:0;">Templates, escalation rules, scheduler delivery, dashboard alerts, and reminder reporting in one place.</p>
    </div>

    <div class="grid">
        <div class="metric"><div class="eyebrow">Open</div><strong>Active Reminders</strong><div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($overview['summary']['open_reminders'] ?? 0)) ?></div></div>
        <div class="metric"><div class="eyebrow">Today</div><strong>Due Today</strong><div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($overview['summary']['due_today'] ?? 0)) ?></div></div>
        <div class="metric"><div class="eyebrow">Overdue</div><strong>Escalation Queue</strong><div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($overview['summary']['overdue'] ?? 0)) ?></div></div>
        <div class="metric"><div class="eyebrow">Email</div><strong>Delivery Failures</strong><div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($overview['summary']['email_failures'] ?? 0)) ?></div></div>
    </div>

    <div class="card-grid">
        <article class="data-card"><div class="eyebrow">Manage</div><h4 style="margin:0;">Reminder Templates</h4><p class="subtle" style="margin:0;">Create and maintain dashboard and email reminder content.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/templates')) ?>" class="button button-secondary">Open</a><a href="<?= e(url('/reminders/templates/form')) ?>" class="button">Create</a></div></article>
        <article class="data-card"><div class="eyebrow">Rules</div><h4 style="margin:0;">Escalation Rules</h4><p class="subtle" style="margin:0;">Configure day-based escalation flow to assigned users, roles, or client contacts.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/escalations')) ?>" class="button button-secondary">Open</a><a href="<?= e(url('/reminders/escalations/form')) ?>" class="button">Create</a></div></article>
        <article class="data-card"><div class="eyebrow">Reports</div><h4 style="margin:0;">Reminder Reports</h4><p class="subtle" style="margin:0;">Review reminder register, pending queue, effectiveness, and escalation history.</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="<?= e(url('/reminders/register')) ?>" class="button button-secondary">Register</a><a href="<?= e(url('/reminders/pending')) ?>" class="button button-secondary">Pending</a><a href="<?= e(url('/reminders/effectiveness')) ?>" class="button button-secondary">Effectiveness</a><a href="<?= e(url('/reminders/escalation-report')) ?>" class="button button-secondary">Escalations</a></div></article>
        <article class="data-card"><div class="eyebrow">Scheduler</div><h4 style="margin:0;">Run Delivery Cycle</h4><p class="subtle" style="margin:0;">Generate due reminders, send dashboard and email notifications, and process escalations.</p><form method="post" action="<?= e(url('/reminders/run-scheduler')) ?>"><?= \App\Core\Csrf::inputField() ?><button type="submit" class="button">Run Scheduler Now</button></form></article>
    </div>
</section>
