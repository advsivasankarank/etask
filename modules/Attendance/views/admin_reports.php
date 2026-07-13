<?php
/**
 * Admin Staff Daily Reports
 * @var array $reports
 * @var array $staff_members
 * @var array $filters
 * @var string|null $success
 * @var string|null $error
 */
$items = $reports['items'] ?? [];
$total = $reports['total'] ?? 0;
$page = $reports['page'] ?? 1;
$totalPages = $reports['total_pages'] ?? 1;
?>

<?php if ($success): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;">Staff Daily Reports</h1>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">Review and manage staff work reports</p>
    </div>
    <?php if (\App\Core\Auth::can('attendance.productivity.view')): ?>
    <a href="/attendance/productivity" class="button" style="background:var(--primary);color:#fff;padding:10px 20px;border-radius:12px;">Productivity View</a>
    <?php endif; ?>
</div>

<div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);margin-bottom:20px;">
    <form method="GET" action="/attendance/admin" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;">Date</label>
            <input type="date" name="date" value="<?= e($filters['date'] ?? date('Y-m-d')) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:.9rem;">
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
        <div>
            <label style="display:block;font-size:.85rem;color:var(--muted);margin-bottom:4px;">Status</label>
            <select name="status" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:.9rem;">
                <option value="">All</option>
                <option value="SUBMITTED" <?= (($filters['status'] ?? '') === 'SUBMITTED') ? 'selected' : '' ?>>Submitted</option>
                <option value="REVIEWED" <?= (($filters['status'] ?? '') === 'REVIEWED') ? 'selected' : '' ?>>Reviewed</option>
                <option value="REOPENED" <?= (($filters['status'] ?? '') === 'REOPENED') ? 'selected' : '' ?>>Reopened</option>
            </select>
        </div>
        <button type="submit" class="button button-primary" style="padding:8px 20px;">Filter</button>
    </form>
</div>

<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
    <?php if ($items === []): ?>
        <div style="text-align:center;padding:40px;color:var(--muted);">No reports found for the selected filters.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Staff</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Login</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Active</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Idle</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Work Done</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Status</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Reviewed By</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 12px;font-weight:600;"><?= e($row['full_name'] ?? '') ?></td>
                    <td style="padding:10px 12px;"><?= $row['login_at'] ? date('h:i A', strtotime($row['login_at'])) : '—' ?></td>
                    <td style="padding:10px 12px;color:var(--success);"><?= $row['total_active_seconds'] ? ((int)($row['total_active_seconds']/3600)).'h '.((int)(($row['total_active_seconds']%3600)/60)).'m' : '—' ?></td>
                    <td style="padding:10px 12px;color:var(--accent);"><?= $row['total_idle_seconds'] ? ((int)($row['total_idle_seconds']/3600)).'h '.((int)(($row['total_idle_seconds']%3600)/60)).'m' : '—' ?></td>
                    <td style="padding:10px 12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= !empty($row['work_done_today']) ? e(mb_strimwidth($row['work_done_today'], 0, 40, '...')) : '—' ?></td>
                    <td style="padding:10px 12px;">
                        <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.8rem;font-weight:600;
                            background:<?= ($row['status'] ?? '') === 'SUBMITTED' ? 'rgba(4,120,87,0.1);color:var(--success)' : (($row['status'] ?? '') === 'REVIEWED' ? 'rgba(20,153,168,0.1);color:var(--primary)' : 'rgba(239,139,44,0.15);color:var(--accent)') ?>;">
                            <?= e($row['status'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td style="padding:10px 12px;"><?= !empty($row['reviewed_by_name']) ? e($row['reviewed_by_name']) : '—' ?></td>
                    <td style="padding:10px 12px;">
                        <a href="/attendance/report/show?id=<?= (int) $row['id'] ?>" style="color:var(--primary);font-weight:600;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="/attendance/admin?date=<?= e($filters['date'] ?? '') ?>&staff_id=<?= e((string)($filters['staff_id'] ?? '')) ?>&status=<?= e($filters['status'] ?? '') ?>&page=<?= $p ?>"
               style="padding:6px 14px;border-radius:8px;font-size:.85rem;<?= $p === $page ? 'background:var(--primary);color:#fff;' : 'background:var(--surface-soft);' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
