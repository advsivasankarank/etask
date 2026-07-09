<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar">
        <div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Workforce Dashboard</h3><div class="subtle">Internal and external workforce management overview.</div></div>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Staff</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total_staff'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Present Today</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['present_today'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">On Work</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['on_work'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Reports Pending</div><div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['daily_reports_pending'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Active Consultants</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['consultants_active'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Assignments Pending</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['consultant_assignments_pending'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Bills Pending</div><div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['consultant_bills_pending'] ?? 0)) ?></div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(380px, 1fr));gap:16px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">Internal Workforce</div>
            <h4 style="margin:4px 0 12px;">Staff & Attendance</h4>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                <?php if (\App\Core\Auth::canAny('users.manage.portal', 'users.manage.internal')): ?><a href="<?= e(url('/users')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">User Accounts</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::canAny('attendance.view', 'attendance.report.review')): ?><a href="<?= e(url('/attendance')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Staff Monitor</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('attendance.report.submit')): ?><a href="<?= e(url('/attendance/report')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Daily Reports</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('attendance.productivity.view')): ?><a href="<?= e(url('/attendance/productivity')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Productivity</div></a><?php endif; ?>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin:0;">
            <div class="eyebrow">External Workforce</div>
            <h4 style="margin:4px 0 12px;">Consultants</h4>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                <?php if (\App\Core\Auth::can('workforce.consultants.view')): ?><a href="<?= e(url('/workforce/consultants')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Consultant Register</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('workforce.consultants.view')): ?><a href="<?= e(url('/workforce/consultant-assignments')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Assignments</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('workforce.consultants.view')): ?><a href="<?= e(url('/workforce/consultant-deliverables')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Deliverables</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('workforce.consultants.view')): ?><a href="<?= e(url('/workforce/consultant-bills')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Bills</div></a><?php endif; ?>
                <?php if (\App\Core\Auth::can('workforce.consultants.view')): ?><a href="<?= e(url('/workforce/consultant-payments')) ?>" style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:center;"><div style="font-weight:700;font-size:0.9rem;">Payments</div></a><?php endif; ?>
            </div>
        </div>
    </div>
</section>
