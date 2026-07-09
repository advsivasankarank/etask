-- Step 27: Staff Monitor Module Permissions
-- Adds attendance/staff-monitor permissions and role assignments.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Create Permissions
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('attendance.view', 'ATTENDANCE', 'VIEW', 'View Staff Monitor', 'Access the staff monitor dashboard and attendance records', 1, NOW(), NOW()),
('attendance.report.submit', 'ATTENDANCE', 'REPORT_SUBMIT', 'Submit Daily Work Report', 'Create and submit daily work reports', 1, NOW(), NOW()),
('attendance.report.review', 'ATTENDANCE', 'REPORT_REVIEW', 'Review Staff Daily Reports', 'Review, remark, and approve staff daily work reports', 1, NOW(), NOW()),
('attendance.activity.manage', 'ATTENDANCE', 'ACTIVITY_MANAGE', 'Manage Own Work Activity', 'Start, stop, pause, and resume work activities', 1, NOW(), NOW()),
('attendance.productivity.view', 'ATTENDANCE', 'PRODUCTIVITY_VIEW', 'View Staff Productivity', 'View staff productivity summaries and SO-wise time tracking', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- =============================================================================
-- B. Role Assignments
-- =============================================================================

-- SUPER_ADMIN: all attendance permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.activity.manage', 'attendance.productivity.view')
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: all attendance permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.activity.manage', 'attendance.productivity.view')
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: all attendance permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.activity.manage', 'attendance.productivity.view')
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ASSISTANT_CRM: view, submit, activity manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.activity.manage')
WHERE r.code = 'ASSISTANT_CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- BACKEND_STAFF: view, submit, activity manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.activity.manage')
WHERE r.code = 'BACKEND_STAFF'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- DEO: view, submit, activity manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.activity.manage')
WHERE r.code = 'DEO'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ACCOUNTS: view, submit, review, activity manage, productivity view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.report.review', 'attendance.activity.manage', 'attendance.productivity.view')
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CONSULTANT: view, submit, activity manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('attendance.view', 'attendance.report.submit', 'attendance.activity.manage')
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CLIENT: no attendance permissions (intentionally omitted)
