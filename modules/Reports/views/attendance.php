<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Attendance Reports</h3><div class="subtle">Staff attendance and productivity overview.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Staff Attendance Today</h4>
        <?php if (($attendance ?? []) === []): ?><p class="subtle">No attendance data.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Staff</th><th>Present Today</th><th>On Work</th></tr></thead><tbody>
            <?php foreach ($attendance as $a): ?>
                <tr><td><?= e($a['full_name']) ?></td><td><?= $a['present_today'] ? 'Yes' : 'No' ?></td><td><?= $a['on_work'] ? 'Yes' : 'No' ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>
