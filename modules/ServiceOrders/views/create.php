<?php
$old = is_array($old ?? null) ? $old : [];
$serviceTypeCompanyMap = [];
foreach ($serviceTypes as $serviceType) {
    $serviceTypeCompanyMap[(string) $serviceType['id']] = (string) ($serviceType['default_company_id'] ?? '');
}
?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Workflow Intake</div>
            <h3 style="margin:0 0 6px;">Create Service Order</h3>
            <div class="subtle">SO number will be auto-generated and immutable in the format `SO/&lt;Company&gt;/&lt;FY&gt;/&lt;Running No&gt;`.</div>
        </div>
        <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Back to Register</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/service-orders')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Order Setup</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Client</span>
                <select name="client_id" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e($client['id']) ?>" <?= (string) ($old['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>>
                            <?= e($client['legal_name']) ?> (<?= e($client['pan'] ?: $client['client_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Service Type</span>
                <select name="service_type_id" id="service_type_id" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <option value="">Select service</option>
                    <?php foreach ($serviceTypes as $serviceType): ?>
                        <option value="<?= e($serviceType['id']) ?>" <?= (string) ($old['service_type_id'] ?? '') === (string) $serviceType['id'] ? 'selected' : '' ?>>
                            <?= e($serviceType['name']) ?><?= !empty($serviceType['default_company_name']) ? ' - ' . e($serviceType['default_company_name']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Mapped Company</span>
                <select name="company_id" id="company_id" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <option value="">Select company</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= e($company['id']) ?>" <?= (string) ($old['company_id'] ?? '') === (string) $company['id'] ? 'selected' : '' ?>>
                            <?= e($company['display_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Priority</span>
                <select name="priority_level">
                    <?php foreach ($priorityOptions as $priority): ?>
                        <option value="<?= e($priority) ?>" <?= (string) ($old['priority_level'] ?? 'MEDIUM') === $priority ? 'selected' : '' ?>>
                            <?= e($priority) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Work Period</h4>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
                <label id="work-basis-wrap" style="display:grid;gap:8px;">
                    <span>Work Basis</span>
                    <select name="work_basis" id="work_basis" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                        <option value="">Select basis</option>
                        <?php foreach ($workBasisOptions as $basis): ?>
                            <option value="<?= e($basis) ?>" <?= (string) ($old['work_basis'] ?? '') === $basis ? 'selected' : '' ?>><?= e($basis) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="gst-subtype-wrap" style="display:grid;gap:8px;">
                    <span>GST Return Type</span>
                    <select name="compliance_subtype" id="compliance_subtype" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                        <option value="">Select GST return type</option>
                        <?php foreach ($gstSubtypeOptions as $subtype): ?>
                            <option value="<?= e($subtype) ?>" <?= (string) ($old['compliance_subtype'] ?? '') === $subtype ? 'selected' : '' ?>><?= e($subtype) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="assessment-year-wrap" style="display:grid;gap:8px;">
                    <span>Assessment Year</span>
                    <input type="text" name="assessment_year" id="assessment_year" value="<?= e($old['assessment_year'] ?? '') ?>" placeholder="Example: 2026-27" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                </label>

                <label id="period-month-wrap" style="display:grid;gap:8px;">
                    <span>Month</span>
                    <select name="period_month" id="period_month" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                        <option value="">Select month</option>
                        <?php foreach ($monthOptions as $monthValue => $monthLabel): ?>
                            <option value="<?= e((string) $monthValue) ?>" <?= (string) ($old['period_month'] ?? '') === (string) $monthValue ? 'selected' : '' ?>><?= e($monthLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="period-quarter-wrap" style="display:grid;gap:8px;">
                    <span>Quarter</span>
                    <select name="period_quarter" id="period_quarter" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                        <option value="">Select quarter</option>
                        <?php foreach ($quarterOptions as $quarter): ?>
                            <option value="<?= e($quarter) ?>" <?= (string) ($old['period_quarter'] ?? '') === $quarter ? 'selected' : '' ?>><?= e($quarter) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="period-year-wrap" style="display:grid;gap:8px;">
                    <span>Year</span>
                    <input type="number" name="period_year" id="period_year" value="<?= e((string) ($old['period_year'] ?? '')) ?>" min="2000" max="2100" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                </label>
            </div>
        </div>

        <label style="display:grid;gap:8px;">
            <span>Title</span>
            <input type="text" name="title" value="<?= e($old['title'] ?? '') ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" placeholder="Example: FY 2025-26 ITR Filing" required>
        </label>

        <label style="display:grid;gap:8px;">
            <span>Description</span>
            <textarea name="description" rows="5" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="Operational notes, scope, or special instructions"><?= e($old['description'] ?? '') ?></textarea>
        </label>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Create Immutable SO</button>
            <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Back to Register</a>
        </div>
    </form>
</section>

<script>
    (function () {
        const serviceTypeSelect = document.getElementById('service_type_id');
        const companySelect = document.getElementById('company_id');
        const workBasisSelect = document.getElementById('work_basis');
        const gstSubtypeSelect = document.getElementById('compliance_subtype');
        const assessmentYearWrap = document.getElementById('assessment-year-wrap');
        const workBasisWrap = document.getElementById('work-basis-wrap');
        const gstSubtypeWrap = document.getElementById('gst-subtype-wrap');
        const monthWrap = document.getElementById('period-month-wrap');
        const quarterWrap = document.getElementById('period-quarter-wrap');
        const yearWrap = document.getElementById('period-year-wrap');
        const map = <?= json_encode($serviceTypeCompanyMap, JSON_THROW_ON_ERROR) ?>;
        const serviceTypeMeta = <?= json_encode(array_map(static fn ($serviceType) => [
            'id' => (string) $serviceType['id'],
            'code' => (string) $serviceType['code'],
        ], $serviceTypes), JSON_THROW_ON_ERROR) ?>;
        const serviceCodeMap = Object.fromEntries(serviceTypeMeta.map(item => [item.id, item.code]));

        function toggle(el, visible) {
            el.style.display = visible ? 'grid' : 'none';
        }

        function applyDefaultCompany() {
            const selectedServiceType = serviceTypeSelect.value;
            const mappedCompany = map[selectedServiceType] || '';

            if (mappedCompany !== '') {
                companySelect.value = mappedCompany;
            }
        }

        function applyPeriodRules() {
            const serviceCode = serviceCodeMap[serviceTypeSelect.value] || '';
            const gstSubtype = gstSubtypeSelect.value;
            const workBasis = workBasisSelect.value;

            toggle(gstSubtypeWrap, serviceCode === 'GST');
            toggle(assessmentYearWrap, serviceCode === 'ITR');

            if (serviceCode === 'ITR') {
                workBasisSelect.value = 'ANNUAL';
                workBasisSelect.setAttribute('disabled', 'disabled');
                toggle(workBasisWrap, false);
                toggle(monthWrap, false);
                toggle(quarterWrap, false);
                toggle(yearWrap, false);
                return;
            }

            workBasisSelect.removeAttribute('disabled');
            toggle(workBasisWrap, true);

            if (serviceCode === 'GST' && (gstSubtype === 'GSTR9' || gstSubtype === 'GSTR9C')) {
                workBasisSelect.value = 'ANNUAL';
                workBasisSelect.setAttribute('disabled', 'disabled');
                toggle(monthWrap, false);
                toggle(quarterWrap, false);
                toggle(yearWrap, false);
                return;
            }

            if (serviceCode === 'GST' && (gstSubtype === 'GSTR1' || gstSubtype === 'GSTR3B') && workBasis === '') {
                workBasisSelect.value = 'MONTHLY';
            }

            workBasisSelect.removeAttribute('disabled');
            toggle(monthWrap, workBasisSelect.value === 'MONTHLY');
            toggle(quarterWrap, workBasisSelect.value === 'QUARTERLY');
            toggle(yearWrap, workBasisSelect.value === 'MONTHLY' || workBasisSelect.value === 'QUARTERLY');
        }

        serviceTypeSelect.addEventListener('change', applyDefaultCompany);
        serviceTypeSelect.addEventListener('change', applyPeriodRules);
        gstSubtypeSelect.addEventListener('change', applyPeriodRules);
        workBasisSelect.addEventListener('change', applyPeriodRules);
        applyDefaultCompany();
        applyPeriodRules();
    }());
</script>
