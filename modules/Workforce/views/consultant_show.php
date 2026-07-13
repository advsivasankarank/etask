<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Dashboard</div><h3 style="margin:0 0 6px;"><?= e($consultant['name']) ?></h3><div class="subtle"><?= e($consultant['firm_name'] ?: 'Consultant') ?> | <?= e(label_case((string) $consultant['status'])) ?></div></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultants/edit?id=' . $consultant['id'])) ?>" class="button">Edit</a><?php endif; ?>
            <a href="<?= e(url('/workforce/consultants')) ?>" class="button button-secondary">Back</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric"><strong>Contact</strong><div style="margin-top:8px;"><?= e($consultant['mobile'] ?: '-') ?></div><div style="font-size:0.85rem;color:#64748b;"><?= e($consultant['email'] ?: '-') ?></div></div>
        <div class="metric"><strong>PAN / GSTIN</strong><div style="margin-top:8px;"><?= e($consultant['pan'] ?: '-') ?></div><div style="font-size:0.85rem;color:#64748b;"><?= e($consultant['gstin'] ?: '-') ?></div></div>
        <div class="metric"><strong>Expertise</strong><div style="margin-top:8px;"><?= e($consultant['expertise'] ?: '-') ?></div></div>
        <div class="metric"><strong>Status</strong><div style="margin-top:8px;"><?= e(label_case((string) $consultant['status'])) ?></div></div>
    </div>

    <?php if (!empty($consultant['address'])): ?><div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);"><h4 style="margin-top:0;">Address</h4><p><?= e($consultant['address']) ?></p></div><?php endif; ?>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Assignments</h4>
        <?php if ($assignments === []): ?><p class="subtle">No assignments yet.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Title</th><th>SO</th><th>Client</th><th>Status</th><th>Due</th></tr></thead><tbody>
            <?php foreach ($assignments as $a): ?><tr><td><strong><?= e($a['assignment_title']) ?></strong></td><td><?= e($a['so_no'] ?: '-') ?></td><td><?= e($a['client_name'] ?: '-') ?></td><td><span class="chip"><?= e(label_case((string) $a['status'])) ?></span></td><td><?= e($a['due_date'] ?: '-') ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Bills</h4>
        <?php if ($bills === []): ?><p class="subtle">No bills yet.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Bill No.</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($bills as $b): ?><tr><td><?= e($b['bill_no'] ?: '-') ?></td><td><?= e($b['bill_date'] ?: '-') ?></td><td><?= e(money_inr($b['total_amount'] ?? 0)) ?></td><td><span class="chip"><?= e(label_case((string) $b['status'])) ?></span></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>
