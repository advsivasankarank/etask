ALTER TABLE service_orders
    ADD COLUMN IF NOT EXISTS itr_case_nature VARCHAR(30) NULL AFTER assessment_year,
    ADD COLUMN IF NOT EXISTS itr_tax_audit_applicable TINYINT(1) NULL AFTER itr_case_nature,
    ADD COLUMN IF NOT EXISTS form_3cb_acknowledgement_no VARCHAR(120) NULL AFTER acknowledgement_no,
    ADD COLUMN IF NOT EXISTS form_3cb_acknowledgement_captured_at DATETIME NULL AFTER acknowledgement_captured_at;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'billing.disbursements.manage'
WHERE r.code IN ('CRM', 'ASSISTANT_CRM')
ON DUPLICATE KEY UPDATE
    is_granted = VALUES(is_granted),
    updated_at = CURRENT_TIMESTAMP;
