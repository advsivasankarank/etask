<?php
/**
 * Staff Monitor Dashboard
 * @var array $dashboard
 * @var bool $has_missing_reports
 * @var string|null $success
 * @var string|null $error
 */
$loginTime = $dashboard['login_time'];
$isActive = $dashboard['is_active'];
$activeDuration = $dashboard['active_duration'];
$idleDuration = $dashboard['idle_duration'];
$reportStatus = $dashboard['report_status'];
$openActivity = $dashboard['open_activity'];
$currentActivityType = $dashboard['current_activity_type'];
$activities = $dashboard['activities'];
?>

<?php if ($success): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($has_missing_reports): ?>
    <div class="flash flash-warning" style="background:#fef3cd;border:1px solid #ffc107;color:#856404;padding:12px 18px;border-radius:12px;margin-bottom:18px;">
        <strong>Action Required:</strong> You have previous days with missing daily work reports. Please submit them as soon as possible.
    </div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="margin:0;font-size:1.6rem;font-weight:700;">Staff Monitor</h2>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">Your work day at a glance</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php if (!$isActive): ?>
            <a href="/login" class="button button-primary" style="opacity:0.6;pointer-events:none;">Not Logged In</a>
        <?php elseif ($currentActivityType === null || !in_array($currentActivityType, ['ACTIVE','TASK_LINKED'], true)): ?>
            <a href="/attendance/activity/start" class="button button-primary">Start Work</a>
        <?php else: ?>
            <form method="POST" action="/attendance/activity/stop" style="display:inline;">
                <?= \App\Core\Csrf::inputField() ?>
                <button type="submit" class="button" style="background:#dc3545;color:#fff;border:none;padding:10px 20px;border-radius:12px;cursor:pointer;">Stop Work</button>
            </form>
        <?php endif; ?>

        <?php if ($currentActivityType === 'ACTIVE' || $currentActivityType === 'TASK_LINKED'): ?>
            <form method="POST" action="/attendance/activity/pause" style="display:inline;">
                <?= \App\Core\Csrf::inputField() ?>
                <button type="submit" class="button" style="background:#ffc107;color:#333;border:none;padding:10px 20px;border-radius:12px;cursor:pointer;">Pause / Break</button>
            </form>
        <?php elseif ($currentActivityType === 'BREAK' || $currentActivityType === 'IDLE'): ?>
            <form method="POST" action="/attendance/activity/resume" style="display:inline;">
                <?= \App\Core\Csrf::inputField() ?>
                <button type="submit" class="button button-primary">Resume Work</button>
            </form>
        <?php endif; ?>

        <a href="/attendance/report" class="button" style="background:var(--primary);color:#fff;padding:10px 20px;border-radius:12px;">Daily Report</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Login Time</div>
        <div style="font-size:1.3rem;font-weight:700;"><?= $loginTime ? e($loginTime) : '—' ?></div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Work Status</div>
        <div style="font-size:1.3rem;font-weight:700;color:<?= $isActive ? 'var(--success)' : 'var(--muted)' ?>;">
            <?= $isActive ? 'Active' : 'Logged Out' ?>
        </div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Active Time Today</div>
        <div style="font-size:1.3rem;font-weight:700;color:var(--success);"><?= e($activeDuration) ?></div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Break / Idle Time</div>
        <div style="font-size:1.3rem;font-weight:700;color:var(--accent);"><?= e($idleDuration) ?></div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Daily Report</div>
        <div style="font-size:1.3rem;font-weight:700;<?= $reportStatus === 'SUBMITTED' || $reportStatus === 'REVIEWED' ? 'color:var(--success)' : 'color:#dc3545' ?>">
            <?= $reportStatus ? e($reportStatus) : 'Not Submitted' ?>
        </div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Current Activity</div>
        <div style="font-size:1.1rem;font-weight:600;">
            <?= $openActivity ? e($openActivity['activity_type']) : 'None' ?>
        </div>
        <?php if ($openActivity && !empty($openActivity['remarks'])): ?>
            <div style="font-size:.85rem;color:var(--muted);margin-top:4px;"><?= e(mb_strimwidth($openActivity['remarks'], 0, 60, '...')) ?></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($activities !== []): ?>
<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);margin-bottom:24px;">
    <h2 style="margin:0 0 16px;font-size:1.1rem;font-weight:700;">Today's Activity Log</h2>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Time</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Service Order</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Type</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Start</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">End</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Duration</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--muted);font-weight:600;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 12px;"><?= date('h:i A', strtotime($act['started_at'])) ?></td>
                    <td style="padding:10px 12px;"><?= !empty($act['so_no']) ? e($act['so_no']) : '—' ?></td>
                    <td style="padding:10px 12px;">
                        <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.8rem;font-weight:600;
                            background:<?= $act['activity_type'] === 'ACTIVE' || $act['activity_type'] === 'TASK_LINKED' ? 'rgba(4,120,87,0.1);color:var(--success)' : ($act['activity_type'] === 'BREAK' ? 'rgba(255,193,7,0.15);color:#856404' : 'rgba(96,123,134,0.1);color:var(--muted)') ?>;">
                            <?= e($act['activity_type']) ?>
                        </span>
                    </td>
                    <td style="padding:10px 12px;"><?= date('h:i A', strtotime($act['started_at'])) ?></td>
                    <td style="padding:10px 12px;"><?= $act['ended_at'] ? date('h:i A', strtotime($act['ended_at'])) : '<span style="color:var(--success);font-weight:600;">Running</span>' ?></td>
                    <td style="padding:10px 12px;"><?= $act['duration_seconds'] ? ((int)($act['duration_seconds']/3600)).'h '.((int)(($act['duration_seconds']%3600)/60)).'m' : '—' ?></td>
                    <td style="padding:10px 12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= !empty($act['remarks']) ? e(mb_strimwidth($act['remarks'], 0, 50, '...')) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
