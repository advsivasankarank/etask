<?php
/**
 * Daily Work Report Form
 * @var array|null $report
 * @var string $auto_draft
 * @var array $activities
 * @var bool $can_edit
 * @var bool $logout_pending
 * @var string|null $error
 * @var string|null $success
 */
$workDone = $report ? ($report['work_done_today'] ?? '') : $auto_draft;
$pendingWork = $report ? ($report['pending_work'] ?? '') : '';
$tmrwPlan = $report ? ($report['tomorrow_plan'] ?? '') : '';
$issuesFaced = $report ? ($report['issues_faced'] ?? '') : '';
$status = $report ? ($report['status'] ?? 'DRAFT') : null;
?>

<?php if ($success): ?>
    <div class="flash flash-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($logout_pending): ?>
    <div class="flash flash-warning" style="background:#fef3cd;border:1px solid #ffc107;color:#856404;padding:12px 18px;border-radius:12px;margin-bottom:18px;">
        <strong>Please submit your Daily Work Report before logout.</strong>
        <form method="POST" action="/attendance/emergency-logout" style="display:inline;margin-left:12px;">
            <?= \App\Core\Csrf::inputField() ?>
            <button type="submit" onclick="return confirm('Are you sure you want to emergency logout without submitting a report?');" style="background:none;border:1px solid #856404;color:#856404;padding:4px 12px;border-radius:6px;cursor:pointer;font-size:.85rem;">Emergency Logout</button>
        </form>
    </div>
<?php endif; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;">Daily Work Report</h1>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;"><?= date('l, d M Y') ?></p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/attendance" class="button" style="background:var(--surface);border:1px solid var(--border);padding:10px 20px;border-radius:12px;">Back to Monitor</a>
    </div>
</div>

<?php if ($status): ?>
<div style="margin-bottom:16px;">
    <span style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:.85rem;font-weight:600;
        background:<?= $status === 'SUBMITTED' ? 'rgba(4,120,87,0.1);color:var(--success)' : ($status === 'REVIEWED' ? 'rgba(20,153,168,0.1);color:var(--primary)' : ($status === 'REOPENED' ? 'rgba(239,139,44,0.15);color:var(--accent)' : 'rgba(96,123,134,0.1);color:var(--muted)')) ?>;">
        Status: <?= e($status) ?>
    </span>
</div>
<?php endif; ?>

<?php if ($activities !== []): ?>
<div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);margin-bottom:20px;">
    <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:700;">Activity Summary</h3>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="border-bottom:2px solid var(--border);">
                    <th style="text-align:left;padding:8px 10px;color:var(--muted);">Start</th>
                    <th style="text-align:left;padding:8px 10px;color:var(--muted);">End</th>
                    <th style="text-align:left;padding:8px 10px;color:var(--muted);">Type</th>
                    <th style="text-align:left;padding:8px 10px;color:var(--muted);">Duration</th>
                    <th style="text-align:left;padding:8px 10px;color:var(--muted);">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:8px 10px;"><?= date('h:i A', strtotime($act['started_at'])) ?></td>
                    <td style="padding:8px 10px;"><?= $act['ended_at'] ? date('h:i A', strtotime($act['ended_at'])) : '—' ?></td>
                    <td style="padding:8px 10px;"><?= e($act['activity_type']) ?></td>
                    <td style="padding:8px 10px;"><?= $act['duration_seconds'] ? ((int)($act['duration_seconds']/3600)).'h '.((int)(($act['duration_seconds']%3600)/60)).'m' : '—' ?></td>
                    <td style="padding:8px 10px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= !empty($act['remarks']) ? e($act['remarks']) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="panel" style="padding:28px;border-radius:16px;background:var(--surface);max-width:720px;">
    <form method="POST" action="/attendance/report">
        <?= \App\Core\Csrf::inputField() ?>

        <div style="margin-bottom:20px;">
            <label for="work_done_today" style="display:block;font-weight:600;margin-bottom:6px;">Work Done Today <span style="color:#dc3545;">*</span></label>
            <textarea name="work_done_today" id="work_done_today" rows="8" <?= $can_edit ? '' : 'readonly' ?>
                placeholder="Describe the work you completed today..."
                style="width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;<?= !$can_edit ? 'background:#f5f5f5;cursor:not-allowed;' : '' ?>"
            ><?= e($workDone) ?></textarea>
            <?php if ($auto_draft !== '' && $workDone === $auto_draft): ?>
                <div style="margin-top:6px;font-size:.8rem;color:var(--muted);">Auto-drafted from today's activity logs. Edit as needed.</div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom:20px;">
            <label for="pending_work" style="display:block;font-weight:600;margin-bottom:6px;">Pending Work</label>
            <textarea name="pending_work" id="pending_work" rows="3" <?= $can_edit ? '' : 'readonly' ?>
                placeholder="What work is still pending?"
                style="width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;<?= !$can_edit ? 'background:#f5f5f5;cursor:not-allowed;' : '' ?>"
            ><?= e($pendingWork) ?></textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label for="tomorrow_plan" style="display:block;font-weight:600;margin-bottom:6px;">Tomorrow's Plan</label>
            <textarea name="tomorrow_plan" id="tomorrow_plan" rows="3" <?= $can_edit ? '' : 'readonly' ?>
                placeholder="What do you plan to work on tomorrow?"
                style="width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;<?= !$can_edit ? 'background:#f5f5f5;cursor:not-allowed;' : '' ?>"
            ><?= e($tmrwPlan) ?></textarea>
        </div>

        <div style="margin-bottom:22px;">
            <label for="issues_faced" style="display:block;font-weight:600;margin-bottom:6px;">Issues Faced</label>
            <textarea name="issues_faced" id="issues_faced" rows="2" <?= $can_edit ? '' : 'readonly' ?>
                placeholder="Any issues or blockers encountered?"
                style="width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;<?= !$can_edit ? 'background:#f5f5f5;cursor:not-allowed;' : '' ?>"
            ><?= e($issuesFaced) ?></textarea>
        </div>

        <?php if ($can_edit): ?>
        <button type="submit" class="button button-primary" style="width:100%;padding:12px;font-size:1rem;">
            <?= $report ? 'Update Report' : 'Submit Report' ?>
        </button>
        <?php else: ?>
        <div style="padding:12px;background:rgba(96,123,134,0.05);border-radius:10px;text-align:center;color:var(--muted);">
            This report has been reviewed and cannot be edited.
        </div>
        <?php endif; ?>
    </form>
</div>
