<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Workforce Reports</h3><div class="subtle">Staff register summary and attendance overview.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total Staff</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total_staff'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Present Today</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['staff_present_today'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Active SO</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['active_service_orders'] ?? 0)) ?></div></div>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Staff Attendance Summary</h4>
        <?php if (($attendance ?? []) === []): ?><p class="subtle">No attendance data.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Staff</th><th>Present Today</th><th>On Work</th></tr></thead><tbody>
            <?php foreach ($attendance as $a): ?>
                <tr><td><?= e($a['full_name']) ?></td><td><?= $a['present_today'] ? 'Yes' : 'No' ?></td><td><?= $a['on_work'] ? 'Yes' : 'No' ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>
