<?php
/**
 * My Work Day page
 * @var array $dashboard
 */
$loginTime = $dashboard['login_time'];
$logoutTime = $dashboard['logout_time'];
$isActive = $dashboard['is_active'];
$activeDuration = $dashboard['active_duration'];
$idleDuration = $dashboard['idle_duration'];
$reportStatus = $dashboard['report_status'];
$openActivity = $dashboard['open_activity'];
$activities = $dashboard['activities'];
?>

<?php if (!empty($success)): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="margin:0;font-size:1.6rem;font-weight:700;">My Work Day</h2>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">Today's attendance and activity details</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/attendance" class="button" style="background:var(--surface);border:1px solid var(--border);padding:10px 20px;border-radius:12px;">Back to Monitor</a>
        <a href="/attendance/report" class="button" style="background:var(--primary);color:#fff;padding:10px 20px;border-radius:12px;">Daily Report</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 12px;font-size:.9rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Attendance Summary</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Login Time</span>
                <span style="font-weight:600;"><?= $loginTime ? e($loginTime) : '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Logout Time</span>
                <span style="font-weight:600;"><?= $logoutTime ? e($logoutTime) : ($isActive ? 'Still logged in' : '—') ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Status</span>
                <span style="font-weight:600;color:<?= $isActive ? 'var(--success)' : 'var(--muted)' ?>;"><?= $isActive ? 'Active' : 'Inactive' ?></span>
            </div>
        </div>
    </div>

    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 12px;font-size:.9rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Time Summary</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Active Time</span>
                <span style="font-weight:600;color:var(--success);"><?= e($activeDuration) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Break / Idle Time</span>
                <span style="font-weight:600;color:var(--accent);"><?= e($idleDuration) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--muted);">Activities Recorded</span>
                <span style="font-weight:600;"><?= count($activities) ?></span>
            </div>
        </div>
    </div>

    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 12px;font-size:.9rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Current Activity</h3>
        <?php if ($openActivity): ?>
            <div style="font-size:1.1rem;font-weight:700;margin-bottom:8px;"><?= e($openActivity['activity_type']) ?></div>
            <?php if (!empty($openActivity['remarks'])): ?>
                <div style="color:var(--muted);font-size:.9rem;"><?= e($openActivity['remarks']) ?></div>
            <?php endif; ?>
            <div style="margin-top:10px;font-size:.85rem;color:var(--muted);">
                Started at <?= date('h:i A', strtotime($openActivity['started_at'])) ?>
            </div>
        <?php else: ?>
            <div style="color:var(--muted);">No active work activity</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($activities !== []): ?>
<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
    <h2 style="margin:0 0 16px;font-size:1.1rem;font-weight:700;">Activity Timeline</h2>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Start</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">End</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Type</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Duration</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 12px;"><?= date('h:i A', strtotime($act['started_at'])) ?></td>
                    <td style="padding:10px 12px;"><?= $act['ended_at'] ? date('h:i A', strtotime($act['ended_at'])) : '<span style="color:var(--success);">Running</span>' ?></td>
                    <td style="padding:10px 12px;"><?= e($act['activity_type']) ?></td>
                    <td style="padding:10px 12px;"><?= $act['duration_seconds'] ? ((int)($act['duration_seconds']/3600)).'h '.((int)(($act['duration_seconds']%3600)/60)).'m' : '—' ?></td>
                    <td style="padding:10px 12px;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= !empty($act['remarks']) ? e($act['remarks']) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
