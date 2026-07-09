<?php
/**
 * Show Single Daily Work Report
 * @var array $report
 * @var array $activities
 * @var bool $can_review
 * @var string|null $error
 * @var string|null $success
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
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;">Daily Work Report</h1>
        <p style="margin:4px 0 0;color:var(--muted);font-size:.95rem;">
            <?= e($report['staff_name'] ?? '') ?> &mdash; <?= e($report['report_date'] ?? '') ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="<?= $can_review ? '/attendance/admin' : '/attendance' ?>" class="button" style="background:var(--surface);border:1px solid var(--border);padding:10px 20px;border-radius:12px;">Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Status</div>
        <div>
            <span style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:.85rem;font-weight:600;
                background:<?= ($report['status'] ?? '') === 'SUBMITTED' ? 'rgba(4,120,87,0.1);color:var(--success)' : (($report['status'] ?? '') === 'REVIEWED' ? 'rgba(20,153,168,0.1);color:var(--primary)' : 'rgba(239,139,44,0.15);color:var(--accent)') ?>;">
                <?= e($report['status'] ?? 'N/A') ?>
            </span>
        </div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Login / Logout</div>
        <div style="font-weight:600;">
            <?= $report['login_at'] ? date('h:i A', strtotime($report['login_at'])) : '—' ?>
            <?= $report['logout_at'] ? ' to ' . date('h:i A', strtotime($report['logout_at'])) : '' ?>
        </div>
    </div>
    <div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);">
        <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Active / Idle Time</div>
        <div style="font-weight:600;">
            <span style="color:var(--success);"><?= $report['total_active_seconds'] ? ((int)($report['total_active_seconds']/3600)).'h '.((int)(($report['total_active_seconds']%3600)/60)).'m active' : '—' ?></span>
            <?php if (!empty($report['total_idle_seconds'])): ?>
                <span style="color:var(--accent);margin-left:12px;"><?= (int)($report['total_idle_seconds']/3600) ?>h <?= (int)(($report['total_idle_seconds']%3600)/60) ?>m idle</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($report['reviewed_by_name'])): ?>
<div class="panel" style="padding:20px;border-radius:16px;background:var(--surface);margin-bottom:20px;">
    <div style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Reviewed By</div>
    <div style="font-weight:600;"><?= e($report['reviewed_by_name']) ?></div>
    <?php if (!empty($report['reviewed_at'])): ?>
        <div style="font-size:.85rem;color:var(--muted);margin-top:4px;">at <?= date('d M Y, h:i A', strtotime($report['reviewed_at'])) ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 10px;font-size:.95rem;font-weight:700;">Work Done Today</h3>
        <div style="white-space:pre-wrap;line-height:1.6;color:var(--text);"><?= e($report['work_done_today'] ?? '') ?></div>
    </div>
    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 10px;font-size:.95rem;font-weight:700;">Pending Work</h3>
        <div style="white-space:pre-wrap;line-height:1.6;color:var(--text);"><?= e($report['pending_work'] ?? '—') ?></div>
    </div>
    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 10px;font-size:.95rem;font-weight:700;">Tomorrow's Plan</h3>
        <div style="white-space:pre-wrap;line-height:1.6;color:var(--text);"><?= e($report['tomorrow_plan'] ?? '—') ?></div>
    </div>
    <div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);">
        <h3 style="margin:0 0 10px;font-size:.95rem;font-weight:700;">Issues Faced</h3>
        <div style="white-space:pre-wrap;line-height:1.6;color:var(--text);"><?= e($report['issues_faced'] ?? '—') ?></div>
    </div>
</div>

<?php if (!empty($report['admin_remarks'])): ?>
<div class="panel" style="padding:24px;border-radius:16px;background:var(--surface);margin-bottom:24px;border-left:4px solid var(--primary);">
    <h3 style="margin:0 0 10px;font-size:.95rem;font-weight:700;">Admin Remarks</h3>
    <div style="white-space:pre-wrap;line-height:1.6;color:var(--text);"><?= e($report['admin_remarks']) ?></div>
</div>
<?php endif; ?>

<?php if ($can_review): ?>
<div class="panel" style="padding:28px;border-radius:16px;background:var(--surface);max-width:640px;">
    <h3 style="margin:0 0 16px;font-size:1rem;font-weight:700;">Review Action</h3>
    <form method="POST" action="/attendance/report/review">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">

        <div style="margin-bottom:18px;">
            <label for="admin_remarks" style="display:block;font-weight:600;margin-bottom:6px;">Remarks</label>
            <textarea name="admin_remarks" id="admin_remarks" rows="3" placeholder="Add your review comments..."
                style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.95rem;resize:vertical;font-family:inherit;"></textarea>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" name="action" value="review" class="button button-primary" style="flex:1;padding:12px;">Mark as Reviewed</button>
            <button type="submit" name="action" value="reopen" class="button" style="flex:1;padding:12px;background:#ffc107;color:#333;border:none;border-radius:12px;cursor:pointer;">Reopen Report</button>
        </div>
    </form>
</div>
<?php endif; ?>
