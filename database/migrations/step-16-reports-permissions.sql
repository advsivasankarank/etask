USE etaxadv_etask;

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('reports.view', 'REPORTS', 'VIEW', 'View Operational Reports', 'View client, service order, and GST reports', 1, NOW(), NOW()),
('reports.financial', 'REPORTS', 'FINANCIAL', 'View Financial Reports', 'View invoice, receipt, outstanding, and revenue reports', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('reports.view', 'reports.financial')
WHERE r.code IN ('SUPER_ADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'reports.view'
WHERE r.code IN ('CRM', 'ASSISTANT_CRM', 'BACKEND_STAFF')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('reports.view', 'reports.financial')
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;
