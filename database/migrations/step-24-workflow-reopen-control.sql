INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('workflow.reopen', 'WORKFLOW', 'REOPEN', 'Reopen Workflow', 'Reopen a completed workflow milestone', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'workflow.reopen'
WHERE r.code IN ('SUPER_ADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE
    is_granted = VALUES(is_granted),
    updated_at = CURRENT_TIMESTAMP;
