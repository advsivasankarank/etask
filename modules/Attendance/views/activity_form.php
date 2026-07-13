<?php
/**
 * Start Work Activity Form
 * @var array $service_orders
 * @var array|null $open_session
 * @var string|null $error
 */
?>

<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="margin:0;font-size:1.6rem;font-weight:700;">Start Work</h2>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">Log your work activity</p>
    </div>
    <a href="/attendance" class="button" style="background:var(--surface);border:1px solid var(--border);padding:10px 20px;border-radius:12px;">Back to Monitor</a>
</div>

<?php if ($open_session === null): ?>
    <div class="panel" style="padding:32px;border-radius:16px;background:var(--surface);text-align:center;">
        <p style="color:var(--muted);font-size:1.1rem;">No active attendance session. Please log in to start tracking work.</p>
    </div>
<?php else: ?>
<div class="panel" style="padding:28px;border-radius:16px;background:var(--surface);max-width:640px;">
    <form method="POST" action="/attendance/activity/start">
        <?= \App\Core\Csrf::inputField() ?>

        <div style="margin-bottom:18px;">
            <label for="service_order_id" style="display:block;font-weight:600;margin-bottom:6px;">Service Order</label>
            <select name="service_order_id" id="service_order_id" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;background:var(--surface);">
                <option value="">— Select Service Order (optional) —</option>
                <?php foreach ($service_orders as $so): ?>
                    <option value="<?= (int) $so['id'] ?>"><?= e($so['so_no'] . ' - ' . $so['title'] . ' (' . $so['client_name'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:18px;">
            <label for="activity_type" style="display:block;font-weight:600;margin-bottom:6px;">Activity Type</label>
            <select name="activity_type" id="activity_type" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;background:var(--surface);">
                <option value="ACTIVE">Active Work</option>
                <option value="TASK_LINKED">Task-Linked Work</option>
            </select>
        </div>

        <div style="margin-bottom:22px;">
            <label for="remarks" style="display:block;font-weight:600;margin-bottom:6px;">Remarks / Work Description</label>
            <textarea name="remarks" id="remarks" rows="3" placeholder="Describe what you are working on..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;"></textarea>
        </div>

        <button type="submit" class="button button-primary" style="width:100%;padding:12px;font-size:1rem;">Start Work Activity</button>
    </form>
</div>
<?php endif; ?>
