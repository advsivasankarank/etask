<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Client Portal</div>
            <h3 style="margin:0 0 6px;">Pre-Service Orders</h3>
            <div class="subtle"><?= $internalView ? 'CRM and Admin view across clients.' : 'Submit and track your PSOs from the client portal.' ?></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::isPortalUser()): ?>
                <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Open Account</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('portal.pso.create')): ?>
                <a href="<?= e(url('/client-portal/pso/create')) ?>" class="button">Create PSO</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($psos === []): ?>
        <div class="data-card"><span class="subtle">No PSOs found.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($psos as $pso): ?>
                <?php
                    $canConvertToSo = !\App\Core\Auth::isPortalUser()
                        && \App\Core\Auth::can('portal.pso.approve')
                        && !in_array((string) $pso['current_status'], ['REJECTED', 'CONVERTED_TO_SO'], true);
                ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($pso['pso_no']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($pso['client_name']) ?></h4>
                        </div>
                        <span class="chip chip-strong"><?= e($pso['current_status']) ?></span>
                    </div>
                    <div class="stat-line"><span>Service</span><strong><?= e($pso['service_type_name']) ?></strong></div>
                    <div class="stat-line"><span>Company</span><strong><?= e($pso['company_name']) ?></strong></div>
                    <div class="stat-line"><span>Converted SO</span><strong><?= e($pso['converted_so_no'] ?: '-') ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:10px;">
                        <?php if (!empty($pso['converted_so_id'])): ?>
                            <a href="<?= e(url('/service-orders/show?id=' . $pso['converted_so_id'])) ?>" class="button">Open SO</a>
                        <?php elseif ($canConvertToSo): ?>
                            <form method="post" action="<?= e(url('/client-portal/pso/approve')) ?>" style="margin:0;">
                                <?= \App\Core\Csrf::inputField() ?>
                                <input type="hidden" name="pso_id" value="<?= e($pso['id']) ?>">
                                <input type="hidden" name="remarks" value="Converted from PSO list">
                                <button type="submit" class="button">Convert to SO</button>
                            </form>
                        <?php endif; ?>
                        <a href="<?= e(url('/client-portal/pso/show?id=' . $pso['id'])) ?>" class="button button-secondary">View PSO</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
