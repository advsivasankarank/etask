USE etaxadv_etask;

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('users.manage.rights', 'USERS', 'MANAGE_RIGHTS', 'Manage User Rights', 'Manage direct user permission grants from the rights control panel', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'users.manage.rights'
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE
    is_granted = VALUES(is_granted),
    updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.accounts',
    'billing.view','billing.disbursements.manage','billing.invoices.manage','billing.payments.manage',
    'consultants.view','consultants.bills.review','consultants.payments.record',
    'service_orders.view',
    'reports.view','reports.financial',
    'workflow.payment.record','workflow.close.accounting','workflow.close.final'
)
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE
    is_granted = VALUES(is_granted),
    updated_at = CURRENT_TIMESTAMP;
