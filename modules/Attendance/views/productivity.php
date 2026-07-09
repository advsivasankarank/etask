<?php
/**
 * Staff Productivity View
 * @var array $staff_summary
 * @var array $so_summary
 * @var array $staff_members
 * @var array $filters
 * @var string|null $success
 * @var string|null $error
 */
?>

<?php if ($success): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;">Staff Productivity</h1>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">Performance and time tracking overview</p>
    </div>
    <a href="/attendance/admin" class="button" style="background:var(--surface);border:1px solid var(--border);padding:10px 20px;border-radius:12px;">Daily Reports</a>
</div>

<div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);margin-bottom:20px;">
    <form method="GET" action="/attendance/productivity" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;">From</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? date('Y-m-d', strtotime('-7 days'))) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:.9rem;">
        </div>
        <div>
            <label style="display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;">To</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? date('Y-m-d')) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:.9rem;">
        </div>
        <div>
            <label style="display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;">Staff</label>
            <select name="staff_id" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:.9rem;">
                <option value="">All Staff</option>
                <?php foreach ($staff_members as $staff): ?>
                    <option value="<?= (int) $staff['id'] ?>" <?= (($filters['staff_id'] ?? null) == $staff['id']) ? 'selected' : '' ?>><?= e($staff['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="button button-primary" style="padding:8px 20px;">Filter</button>
    </form>
</div>

<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);margin-bottom:24px;">
    <h2 style="margin:0 0 16px;font-size:1.1rem;font-weight:700;">Staff Summary</h2>
    <?php if ($staff_summary === []): ?>
        <div style="text-align:center;padding:30px;color:var(--muted);">No data found for the selected period.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Staff</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Days</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Active</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Idle</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Avg/Day</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Reports</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Missing</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff_summary as $row): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 12px;font-weight:600;"><?= e($row['full_name']) ?></td>
                    <td style="padding:10px 12px;text-align:right;"><?= (int) $row['login_days'] ?></td>
                    <td style="padding:10px 12px;text-align:right;color:var(--success);font-weight:600;"><?= e($row['active_duration']) ?></td>
                    <td style="padding:10px 12px;text-align:right;color:var(--accent);"><?= e($row['idle_duration']) ?></td>
                    <td style="padding:10px 12px;text-align:right;"><?= (int) $row['avg_active_per_day'] ?>m</td>
                    <td style="padding:10px 12px;text-align:right;"><?= (int) $row['reports_submitted'] ?></td>
                    <td style="padding:10px 12px;text-align:right;color:<?= (int) $row['reports_missing'] > 0 ? '#dc3545' : 'var(--muted)' ?>;"><?= (int) $row['reports_missing'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
    <h2 style="margin:0 0 16px;font-size:1.1rem;font-weight:700;">Service Order-wise Time</h2>
    <?php if ($so_summary === []): ?>
        <div style="text-align:center;padding:30px;color:var(--muted);">No service order activity found for the selected period.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Staff</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Service Order</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Client</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Time Spent</th>
                    <th style="text-align:right;padding:10px 12px;color:var(--muted);">Activities</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Last Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($so_summary as $row): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 12px;font-weight:600;"><?= e($row['staff_name']) ?></td>
                    <td style="padding:10px 12px;"><?= e($row['so_no'] ?? 'General') ?></td>
                    <td style="padding:10px 12px;"><?= e($row['client_name'] ?? '—') ?></td>
                    <td style="padding:10px 12px;text-align:right;color:var(--success);font-weight:600;"><?= e($row['duration']) ?></td>
                    <td style="padding:10px 12px;text-align:right;"><?= (int) $row['activity_count'] ?></td>
                    <td style="padding:10px 12px;"><?= $row['last_activity_at'] ? date('d M, h:i A', strtotime($row['last_activity_at'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
