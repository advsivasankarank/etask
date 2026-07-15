<?php
$old = is_array($old ?? null) ? $old : [];
$serviceTypeCompanyMap = [];
foreach ($serviceTypes as $serviceType) {
    $serviceTypeCompanyMap[(string) $serviceType['id']] = (string) ($serviceType['default_company_id'] ?? '');
}
$quarterLabels = [
    'Q1' => 'Q1 (April - June)',
    'Q2' => 'Q2 (July - September)',
    'Q3' => 'Q3 (October - December)',
    'Q4' => 'Q4 (January - March)',
];
?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Service Order Module</div>
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
            <div class="eyebrow">Client Selection</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Client *</span>
                <select name="client_id" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e($client['id']) ?>" <?= (string) ($old['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>>
                            <?= e($client['legal_name']) ?> (<?= e($client['pan'] ?: $client['client_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Service Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Service Type *</span>
                <select name="service_type_id" id="service_type_id" required>
                    <option value="">Select service</option>
                    <?php foreach ($serviceTypes as $serviceType): ?>
                        <option value="<?= e($serviceType['id']) ?>" <?= (string) ($old['service_type_id'] ?? '') === (string) $serviceType['id'] ? 'selected' : '' ?>>
                            <?= e($serviceType['name']) ?><?= !empty($serviceType['default_company_name']) ? ' - ' . e($serviceType['default_company_name']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Mapped Company *</span>
                <select name="company_id" id="company_id" required>
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
            <div class="eyebrow">Work Period</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
                <label style="display:grid;gap:8px;">
                    <span>Financial Year *</span>
                    <select name="financial_year_id" id="financial_year_id" required>
                        <option value="">Select financial year</option>
                        <?php foreach ($financialYears as $financialYear): ?>
                            <option value="<?= e((string) $financialYear['id']) ?>" <?= (string) ($old['financial_year_id'] ?? '') === (string) $financialYear['id'] ? 'selected' : '' ?>>
                                <?= e($financialYear['label']) ?><?= (int) ($financialYear['is_current'] ?? 0) === 1 ? ' (Current)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="work-basis-wrap" style="display:grid;gap:8px;">
                    <span>Filing Frequency *</span>
                    <select name="work_basis" id="work_basis" required>
                        <?php foreach ($workBasisOptions as $basis): ?>
                            <option value="<?= e($basis) ?>" <?= (string) ($old['work_basis'] ?? 'ANNUAL') === $basis ? 'selected' : '' ?>><?= e(ucfirst(strtolower($basis))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="gst-subtype-wrap" style="display:grid;gap:8px;">
                    <span>GST Return Type</span>
                    <select name="compliance_subtype" id="compliance_subtype">
                        <option value="">Select GST return type</option>
                        <?php foreach ($gstSubtypeOptions as $subtype): ?>
                            <option value="<?= e($subtype) ?>" <?= (string) ($old['compliance_subtype'] ?? '') === $subtype ? 'selected' : '' ?>><?= e($subtype) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="itr-case-wrap" style="display:grid;gap:8px;">
                    <span>ITR Case Type</span>
                    <select name="itr_case_nature" id="itr_case_nature">
                        <option value="">Select case type</option>
                        <?php foreach ($itrCaseOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= (string) ($old['itr_case_nature'] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="itr-tax-audit-wrap" style="display:grid;gap:8px;">
                    <span>Tax Audit Applicable</span>
                    <select name="itr_tax_audit_applicable" id="itr_tax_audit_applicable">
                        <option value="">Select option</option>
                        <?php foreach ($yesNoOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= (string) ($old['itr_tax_audit_applicable'] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="period-month-wrap" style="display:grid;gap:8px;">
                    <span>Month</span>
                    <select name="period_month" id="period_month">
                        <option value="">Select month</option>
                        <?php foreach ($monthOptions as $monthValue => $monthLabel): ?>
                            <option value="<?= e((string) $monthValue) ?>" <?= (string) ($old['period_month'] ?? '') === (string) $monthValue ? 'selected' : '' ?>><?= e($monthLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label id="period-quarter-wrap" style="display:grid;gap:8px;">
                    <span>Quarter</span>
                    <select name="period_quarter" id="period_quarter">
                        <option value="">Select quarter</option>
                        <?php foreach ($quarterOptions as $quarter): ?>
                            <option value="<?= e($quarter) ?>" <?= (string) ($old['period_quarter'] ?? '') === $quarter ? 'selected' : '' ?>><?= e($quarterLabels[$quarter] ?? $quarter) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Description</div>
            <label style="display:grid;gap:8px;">
                <span>Title *</span>
                <input type="text" name="title" value="<?= e($old['title'] ?? '') ?>" placeholder="Example: FY 2025-26 ITR Filing" required>
            </label>

            <label style="display:grid;gap:8px;margin-top:12px;">
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Operational notes, scope, or special instructions"><?= e($old['description'] ?? '') ?></textarea>
            </label>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Create Service Order</button>
            <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
    (function () {
        const serviceTypeSelect = document.getElementById('service_type_id');
        const companySelect = document.getElementById('company_id');
        const workBasisSelect = document.getElementById('work_basis');
        const gstSubtypeSelect = document.getElementById('compliance_subtype');
        const itrCaseWrap = document.getElementById('itr-case-wrap');
        const itrTaxAuditWrap = document.getElementById('itr-tax-audit-wrap');
        const itrCaseSelect = document.getElementById('itr_case_nature');
        const workBasisWrap = document.getElementById('work-basis-wrap');
        const gstSubtypeWrap = document.getElementById('gst-subtype-wrap');
        const monthWrap = document.getElementById('period-month-wrap');
        const quarterWrap = document.getElementById('period-quarter-wrap');
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
            const itrCase = itrCaseSelect.value;

            toggle(gstSubtypeWrap, serviceCode === 'GST');
            toggle(itrCaseWrap, serviceCode === 'ITR');
            toggle(itrTaxAuditWrap, serviceCode === 'ITR' && itrCase === 'BUSINESS');

            if (serviceCode === 'ITR') {
                workBasisSelect.value = 'ANNUAL';
                workBasisSelect.setAttribute('disabled', 'disabled');
                toggle(workBasisWrap, true);
                toggle(monthWrap, false);
                toggle(quarterWrap, false);
                return;
            }

            workBasisSelect.removeAttribute('disabled');
            toggle(workBasisWrap, true);

            if (serviceCode === 'GST' && (gstSubtype === 'GSTR9' || gstSubtype === 'GSTR9C')) {
                workBasisSelect.value = 'ANNUAL';
                workBasisSelect.setAttribute('disabled', 'disabled');
                toggle(monthWrap, false);
                toggle(quarterWrap, false);
                return;
            }

            if (serviceCode === 'GST' && (gstSubtype === 'GSTR1' || gstSubtype === 'GSTR3B') && workBasis === '') {
                workBasisSelect.value = 'MONTHLY';
            }

            workBasisSelect.removeAttribute('disabled');
            toggle(monthWrap, workBasisSelect.value === 'MONTHLY');
            toggle(quarterWrap, workBasisSelect.value === 'QUARTERLY');
        }

        serviceTypeSelect.addEventListener('change', applyDefaultCompany);
        serviceTypeSelect.addEventListener('change', applyPeriodRules);
        gstSubtypeSelect.addEventListener('change', applyPeriodRules);
        workBasisSelect.addEventListener('change', applyPeriodRules);
        itrCaseSelect.addEventListener('change', applyPeriodRules);
        applyDefaultCompany();
        applyPeriodRules();
    }());
</script>
